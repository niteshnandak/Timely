<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\PayrollBatch;
use App\Models\PayrollBatchDetail;
use App\Models\PayrollCustomerInvoice;
use App\Models\TaskScheduler;
use App\Models\TaxBrand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyInvoiceToPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to tally the calculations of the invoices to create the payroll';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get the invoices which are selected but not verified yet from the payroll batch

        try{

        DB::beginTransaction();


        // Getting all the payroll batched which is in verification process
        $payroll_batches_verified = PayrollBatch::where('payroll_batch_status', 'Verification');

        $payroll_batches_verified_id = $payroll_batches_verified->pluck('payroll_batch_id')
        ->toArray();

        $payroll_batches_verified = $payroll_batches_verified->get()
        ->toArray();


        // getting all the invoices related to the payroll batches
        $invoices = DB::table('invoice as i')
        ->join('payroll_customer_invoice as p', 'p.invoice_id', '=', 'i.invoice_id')
        ->whereIn('payroll_batch_id', $payroll_batches_verified_id)
        ->where('invoice_selected_status', '1')
        ->where('p.is_deleted', 0)
        ->where('p.is_deleted', '0');

        $invoices_selected = $invoices->join('invoice_details as id', 'i.invoice_id', '=', 'id.invoice_id')
        ->selectRaw('i.people_id, i.company_id, i.organisation_id, payroll_batch_id, SUM(quantity) as total_hours, AVG(unit_price) as hourly_pay')
        ->where('id.is_deleted', 0)
        ->groupBy('i.people_id', 'i.company_id', 'i.organisation_id', 'payroll_batch_id')
        ->get()
        ->toArray();

        foreach($invoices_selected as $invoice_selected){
            // task scheduler audits are being created
            $task_data = [
                "task_id" => "2",
                "status" => "Starting",
                "message" => "Task scheduler is running for verifying the datas",
                "created_by" => "0",
                "updated_by" => "0"
            ];
            $task = TaskScheduler::create($task_data);

            // declaring all the variables needed for the calculations
            $hours_worked=$invoice_selected->total_hours;
            $hourly_rate=$invoice_selected->hourly_pay;
            $company_margin=10;
            $expense = Expense::where('people_id', $invoice_selected->people_id)
            ->where('is_deleted', 0)
            ->where('status', 'approved');

            // checking if there is any expense available for the selcted person and adding the values
            $expense_ids = $expense->pluck('expense_id')
            ->toArray();

            // getting inoice ids of the person to store in the table
            $invoice_ids = PayrollCustomerInvoice::join('invoice as i', 'payroll_customer_invoice.invoice_id', '=', 'i.invoice_id')
            ->where('payroll_batch_id', $invoice_selected->payroll_batch_id)
            ->where('i.people_id', $invoice_selected->people_id)
            ->where('invoice_selected_status', '1')
            ->where('i.is_deleted', 0)
            ->where('payroll_customer_invoice.is_deleted', 0)
            ->pluck('payroll_customer_invoice.invoice_id')
            ->toArray();

            // getting the sum of the expenses
            $expenses = $expense
            ->selectRaw('SUM(amount) as expense')
            ->get()
            ->toArray();

            $expenses = $expenses['0']['expense'];

            // calculation starts here
            $gross = $hours_worked * $hourly_rate;
            $taxable_amount = $gross - $company_margin;
            $total_payment = $taxable_amount +$expenses;

            $count = 0;
            $max_er=0;
            $max_ee=0;

            $tax_brand = TaxBrand::select(['tax_from','tax_to','er_percent','ee_percent','max_er','max_ee'])->get()->toArray();
            $tax_to =  array_column($tax_brand ,'tax_to');


            for($i=0; $i<sizeOf($tax_to)-1;$i++){
                if( !$tax_to[$i]){
                    return;
                }
                if($taxable_amount > $tax_to[$i] ){
                    $count++;
                }
            }

            for($j=0 ; $j<$count ;$j++ ){

                $max_ee +=$tax_brand[$j]['max_ee'];
                $max_er +=$tax_brand[$j]['max_er'];
            }
            $remaing_amount = $taxable_amount - $tax_brand[$count]["tax_from"];
            $max_ee += $remaing_amount * ($tax_brand[$count]["ee_percent"])/100;
            $max_er += $remaing_amount * ($tax_brand[$count]["er_percent"])/100;

            $total_er = $max_er;
            $total_ee = $max_ee;
            $total_tax_deduction = $total_er + $total_ee;
            $net_pay = $total_payment - $total_tax_deduction;

            // calculations ends here

            // making the structure of the data to be inserted into the table
            if($gross != 0){
                $data =[
                    "payroll_batch_id" => $invoice_selected->payroll_batch_id,
                    "company_id" => $invoice_selected->company_id,
                    "organisation_id" => $invoice_selected->organisation_id,
                    "people_id" => $invoice_selected->people_id,
                    "expenses" => json_encode($expense_ids),
                    "invoices" => json_encode($invoice_ids),
                    "total_hours" => $hours_worked,
                    "hourly_pay" => $hourly_rate,
                    "company_margin" => $company_margin,
                    "expense_amount" => $expenses,
                    "gross_salary"=>$gross,
                    "taxable_amount"=>$taxable_amount,
                    "total_payment_amount"=>$total_payment,
                    "er_tax" =>$total_er,
                    "ee_tax" =>$total_ee,
                    "total_tax_deduction"=>$total_tax_deduction,
                    "net_pay"=>$net_pay,
                    "margin" => $company_margin,
                    "created_by" => 0,
                    "updated_by" => 0,
                ];
            }

            // inserting the values into the payroll batch detail table
            $payroll_batch = PayrollBatchDetail::create($data);

            // updating status in payroll batch and expenses
            $payroll_batch_status = PayrollBatch::where("payroll_batch_id", $invoice_selected->payroll_batch_id)
            ->update(['payroll_batch_status' => 'Verified']);

            $expense->update(['status' => 'processed']);

            // updating the tasks in the task scheduler table
            $task_data = [
                "task_id" => "2",
                "status" => Config::get('message.success.task_scheduler_success'),
                "param" => json_encode(["invoices" => $invoice_ids, "expenses" => $expense_ids]),
                "message" => Config::get('message.success.task_scheduler_success_message'),
                "created_by" => "0",
                "updated_by" => "0"
            ];
            $task->update($task_data);

            Log::info($payroll_batch);

        }

        foreach($payroll_batches_verified_id as $payroll_batch_id){
            $no_of_payrolls = PayrollBatchDetail::where('payroll_batch_id', $payroll_batch_id)->count();
            if($no_of_payrolls != 0){
                PayrollBatch::where('payroll_batch_id', $payroll_batch_id)
                ->update([
                    'no_of_payroll' => $no_of_payrolls
                ]);
            }
            else{
                PayrollBatch::where('payroll_batch_id', $payroll_batch_id)
                ->update([
                    'is_deleted' => 1
                ]);
            }
        }

        // updating status in invoice
        $invoices = DB::table('invoice as i')
        ->join('payroll_customer_invoice as p', 'p.invoice_id', '=', 'i.invoice_id')
        ->whereIn('payroll_batch_id', $payroll_batches_verified_id)
        ->where('invoice_selected_status', '1')
        ->update(['payroll_status' => "processed"]);


        DB::commit();
        }catch(\Exception $e){

            // Logged and updated in the task scheduler table if there is a error
            DB::rollBack();

            $task_data = [
                "task_id" => "2",
                "status" => Config::get('message.error.task_scheduler_error'),
                "message" => Config::get('message.error.task_scheduler_error_message'),
                "exception" => $e->getMessage(),
                "created_by" => "0",
                "updated_by" => "0"
            ];
            $task->update($task_data);
            Log::error($e->getMessage());
        }
    }
}
