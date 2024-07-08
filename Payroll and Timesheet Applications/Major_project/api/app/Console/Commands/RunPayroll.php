<?php

namespace App\Console\Commands;

use App\Models\PayrollBatch;
use App\Models\PayrollBatchDetail;
use App\Models\PayrollCustomerInvoice;
use App\Models\PayrollHistory;
use App\Models\TaskScheduler;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:payroll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete Payroll for the people';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = PayrollBatch::where(['payroll_batch_status' => 'Processing'])->select(['payroll_batch_id'])->get();

        $verifiedPayrolls = PayrollBatchDetail::whereIn('payroll_batch_id', $data)->get();



        try {
            DB::beginTransaction(); // Start transaction
            foreach ($verifiedPayrolls as $verifiedPayroll) {
                Log::channel('payroll_run_log')->error('Payroll run  Started for payroll detail '.$verifiedPayroll['payroll_batch_detail_id']);
    
                $task = TaskScheduler::create( [
                    "task_id" => "3",
                    "status" => Config::get('message.success.payroll_started_status'),
                    "param" => json_encode([
                        'payroll_batch_id' => $verifiedPayroll['payroll_batch_id'],
                        'payroll_batch_detail_id' => $verifiedPayroll['payroll_batch_detail_id'],
                        'people_id' => $verifiedPayroll['people_id'],
                        'company_id' => $verifiedPayroll['company_id'],
                        'organisation_id' => $verifiedPayroll['organisation_id'],
                    ]),
                    "message" => Config::get('message.success.payroll_started_message'),
                ]);

                $payrollHistory =   PayrollHistory::create([
                    'task_schedular_id' => $task['task_schedular_id'],
                    'payroll_batch_id' => $verifiedPayroll['payroll_batch_id'],
                    'payroll_batch_detail_id' => $verifiedPayroll['payroll_batch_detail_id'],
                    'people_id' => $verifiedPayroll['people_id'],
                    'company_id' => $verifiedPayroll['company_id'],
                    'organisation_id' => $verifiedPayroll['organisation_id'],
                    'gross_salary' => $verifiedPayroll['gross_salary'],
                    'taxable_amount' => $verifiedPayroll['taxable_amount'],
                    'total_payment_amount' => $verifiedPayroll['total_payment_amount'],
                    'er_tax' => $verifiedPayroll['er_tax'],
                    'ee_tax' => $verifiedPayroll['ee_tax'],
                    'total_tax_deduction' => $verifiedPayroll['total_tax_deduction'],
                    'net_pay' => $verifiedPayroll['net_pay'],
                    'total_hours' => $verifiedPayroll['total_hours'],
                    'hourly_pay' => $verifiedPayroll['hourly_pay'],
                    'expense_amount' => $verifiedPayroll['expense_amount'],
                    'expenses' => $verifiedPayroll['expenses'],
                    'invoices'=>$verifiedPayroll['invoices'],
                    'margin' => $verifiedPayroll['margin'],
                    'in_hand_salary' => $verifiedPayroll['in_hand_salary'],
                    'is_rollback'=>PayrollHistory::STATUS_UNROLLBACK,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                PayrollBatchDetail::join('payroll_customer_invoice', 'payroll_customer_invoice.payroll_batch_id', '=', 'payroll_batch_detail.payroll_batch_id')
                    ->join('payroll_batch', 'payroll_batch.payroll_batch_id', '=', 'payroll_batch_detail.payroll_batch_id')
                    ->join('invoice', 'payroll_customer_invoice.invoice_id', '=', 'invoice.invoice_id')
                    ->where(['payroll_customer_invoice.is_deleted' => PayrollCustomerInvoice::STATUS_UNDELETED, 'payroll_batch_detail.payroll_batch_id' => $verifiedPayroll['payroll_batch_id']])
                    ->update([
                        "payroll_batch_detail.payrolled_status" =>  PayrollCustomerInvoice::STATUS_PAYROLLED,
                        'payroll_customer_invoice.invoice_payrolled_status' => PayrollCustomerInvoice::STATUS_PAYROLLED,
                        'payroll_batch.payroll_batch_status' => 'Payrolled',
                    ]);

                TaskScheduler::where(["task_schedular_id" => $task["task_schedular_id"]])
                    ->update([
                        "status" => Config::get('message.success.payroll_ended_status'),
                        "message" => Config::get('message.success.payroll_ended_message')
                    ]);
                
                Log::debug($task);

                PayrollHistory::where(["payroll_history_id" => $payrollHistory["payroll_history_id"]])
                    ->update([
                        "task_schedular_id" => $task["task_schedular_id"],
                    ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
