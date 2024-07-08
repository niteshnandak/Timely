<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PayrollBatch;
use App\Models\PayrollBatchDetail;
use Exception;
use App\Models\PayrollCustomer;
use App\Models\PayrollCustomerInvoice;
use App\Models\PayrollHistory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PayrollService
{
    // public function showPayrollBatches($skip, $take, $company_id, $organisation_id, $searchData)
    // {
    //     try {
    //         $query = PayrollBatch::where([
    //             'company_id' => $company_id,
    //             'organisation_id' => $organisation_id,
    //             'is_deleted' => 0

    //         ]);

    //         $payrollBatches = $query->select([
    //             'payroll_batch_id',
    //             'payroll_batch_number',
    //             'payroll_batch_name',
    //             'payroll_batch_date',
    //             'no_of_payroll',
    //             'payroll_batch_status'
    //         ])
    //         ->orderBy('created_at', 'desc');

    //         if($searchData){
    //             if($searchData['payroll_batch_number']){
    //                 $payrollBatches->where('payroll_batch_number', 'like', "%". $searchData['payroll_batch_number']."%");
    //             }
    //             else if($searchData['payroll_batch_name']){
    //                 $payrollBatches->where('payroll_batch_name', 'like', "%". $searchData['payroll_batch_name']."%");
    //             }
    //             else if($searchData['payroll_batch_date']){
    //                 $payrollBatches->where('payroll_batch_date',$searchData['payroll_batch_date']);
    //             }
    //             else if($searchData['payroll_batch_status']){
    //                 $payrollBatches->where('payroll_batch_status',$searchData['payroll_batch_status']);
    //             }
    //         }

    //         $total = $payrollBatches->count();


    //         $payrollBatches = $payrollBatches
    //         ->skip($skip)
    //         ->take($take)
    //         ->get();

    //         return [
    //             'data' => $payrollBatches,
    //             'total' => $total,
    //         ];
    //     } catch (Exception $e) {
    //         Log::error('Error fetching payroll batches: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    public function showPayrollBatches($skip, $take, $company_id, $searchData, $organisation_id)
{
    try {
        $query = PayrollBatch::where([
            'company_id' => $company_id,
            'organisation_id' => $organisation_id,
            'is_deleted' => 0
        ]);

        $payrollBatches = $query->select([
            'payroll_batch_id',
            'payroll_batch_number',
            'payroll_batch_name',
            'payroll_batch_date',
            'no_of_payroll',
            'payroll_batch_status'
        ])
        ->orderBy('created_at', 'desc');


        if ($searchData) {
            if (isset($searchData['payroll_batch_number'])) {
                $payrollBatches->where('payroll_batch_number', 'like', "%" . $searchData['payroll_batch_number'] . "%");
            }
            if (isset($searchData['payroll_batch_name'])) {
                $payrollBatches->where('payroll_batch_name', 'like', "%" . $searchData['payroll_batch_name'] . "%");
            }
            if (isset($searchData['payroll_batch_start_date']) && isset($searchData['payroll_batch_end_date'])) {
                $payrollBatches->whereBetween('payroll_batch_date',[ $searchData['payroll_batch_start_date'],$searchData['payroll_batch_end_date']]);
            }elseif(isset($searchData['payroll_batch_start_date']) ){
                $payrollBatches->where('payroll_batch_date','>=', $searchData['payroll_batch_start_date']);
            }elseif(isset($searchData['payroll_batch_end_date'])){
                $payrollBatches->where('payroll_batch_date','<=', $searchData['payroll_batch_end_date']);
            }
            if (isset($searchData['payroll_batch_status'])) {
                $payrollBatches->where('payroll_batch_status', $searchData['payroll_batch_status']);
            }
        }

        $total = $payrollBatches->count();

        $payrollBatches = $payrollBatches
            ->skip($skip)
            ->take($take)
            ->get();

        return [
            'data' => $payrollBatches,
            'total' => $total,
        ];
    } catch (Exception $e) {
        Log::error('Error fetching payroll batches: ' . $e->getMessage());
        throw $e;
    }
}



public function addPayrollBatch($data)
{
    return DB::transaction(function () use ($data) {
        try {

                $auth_Org_Id = auth()->user()->organisation_id;

            // Lock the table to prevent concurrent access
            DB::table('payroll_batch')
                ->where('organisation_id', $auth_Org_Id)
                ->where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->get();

                // Calculate the new payroll batch number
                if ($lastBatch) {
                    $lastNumber = (int) substr($lastBatch->payroll_batch_number, 2);
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }

                $payrollBatchNumber = 'PB' . $lastBatch->payroll_batch_id;

                // Create the new payroll batch
                $newBatch = PayrollBatch::create([
                    'payroll_batch_name' => $data['payroll_batch_name'],
                    'payroll_batch_date' => $data['payroll_batch_date'],
                    'payroll_batch_status' => 'created',
                    'company_id' => $data['company_id'],
                    'payroll_batch_number' => $payrollBatchNumber,
                    'organisation_id' => $auth_Org_Id
                ]);

                DB::commit();
                return $newBatch;

        } catch (Exception $e) {
            Log::error('Error adding payroll batch: ' . $e->getMessage());
            throw $e;
        }
    }, 5); // 5 is the number of retries for the transaction if it fails due to deadlock
}



    public function getPayrollBatchForReport($id)
{
    try {
        $payrollBatch = DB::table('payroll_batch')
            ->select(
                'payroll_batch.payroll_batch_number',
                'payroll_batch.payroll_batch_status',
                'payroll_batch_detail.*',
                'people.people_name'
            )
            ->join('payroll_batch_detail', 'payroll_batch.payroll_batch_id', '=', 'payroll_batch_detail.payroll_batch_id')
            ->join('people', 'payroll_batch_detail.people_id', '=', 'people.people_id')
            ->where('payroll_batch.payroll_batch_id', $id)
            ->where('payroll_batch.is_deleted', 0)
            ->where('payroll_batch_detail.is_deleted', 0)
            ->where('people.is_deleted', 0)
            ->get();

        if ($payrollBatch->isEmpty()) {
            throw new Exception(Config::get('message.error.payroll_batch_not_found'));
        }

        $batchStatus = $payrollBatch->first()->payroll_batch_status;

        if (!in_array($batchStatus, ['Verified', 'Payrolled'])) {
            Log::error("Invalid payroll batch status: {$batchStatus}");
            throw new Exception(Config::get('message.error.payroll_report_error'));
        }

        return $payrollBatch;
    } catch (Exception $e) {
        Log::error('Error getting payroll batch for report: ' . $e->getMessage());
        throw $e;
    }
}

public function deletePayrollBatch($id)
    {
        try {
            log::debug($id);
            $payrollBatch = PayrollBatch::where('payroll_batch_id', $id)->first();
            $payrollBatch->is_deleted=1;
            $payrollBatch->save();
            log::debug($payrollBatch);
            if ($payrollBatch) {
                return ['message' => Config::get('message.success.payroll_batch_delete_success')];
            }


            return ['message' => Config::get('message.success.payroll_batch_delete_success')];
        } catch (Exception $e) {
            return $e->getMessage();
            Log::error('Error deleting payroll batch: ' . $e->getMessage());
            throw $e;
        }
    }


    public function createPayrollCustomer($invoices, $payroll_batch_id)
    {

        try {
            $user = Auth::user();
            $customer_invoices = Invoice::whereIn('invoice_id', $invoices)
                                ->select([
                                        DB::raw('count(invoice_id) as no_of_invoice'),
                                        'customer_id',
                                        'company_id'
                                        ])
                                ->groupBy('customer_id', 'company_id')
                                ->get();

            DB::beginTransaction(); // Start transaction
            foreach ($customer_invoices as $customer_invoice) {
                        PayrollCustomer::create([
                            'payroll_batch_id' => $payroll_batch_id,
                            'customer_id' => $customer_invoice["customer_id"],
                            'no_of_invoice' => $customer_invoice["no_of_invoice"],
                            'company_id' => $customer_invoice["customer_id"],
                            'organisation_id' => $user['organisation_id'],
                            'created_at' => Carbon::now(),
                            // 'created_by'=>$user['user_id']
                        ]);
            }

            $payroll_customer_invoices = Invoice::join('payroll_customer', 'payroll_customer.customer_id', '=', 'invoice.customer_id')
                                ->whereIn('invoice_id', $invoices)
                                ->select([
                                    'invoice_id',
                                    'payroll_customer.payroll_customer_id',
                                    'invoice.company_id',
                                    'people_id'
                                ])
                                ->where([
                                    'payroll_customer.payroll_batch_id' => $payroll_batch_id,
                                    'payroll_customer.is_deleted' => PayrollCustomer::STATUS_UNDELETED
                                ])->get();

            foreach ($payroll_customer_invoices as $payroll_customer_invoice) {
                                PayrollCustomerInvoice::create([
                                    'payroll_customer_id' => $payroll_customer_invoice['payroll_customer_id'],
                                    'payroll_batch_id' => $payroll_batch_id,
                                    'invoice_id' => $payroll_customer_invoice['invoice_id'],
                                    'people_id' => $payroll_customer_invoice['people_id'],
                                    'invoice_selected_status' => PayrollCustomerInvoice::STATUS_UNSELECTED,
                                    'invoice_payrolled_status' => PayrollCustomerInvoice::STATUS_UNPAYROLLED,
                                    'company_id' => $payroll_customer_invoice['company_id'],
                                    'organisation_id' => $user['organisation_id'],
                                    'created_at' => Carbon::now(),
                                    // 'created_by'=>$user['user_id']
                                ]);
            }
            DB::commit(); // Commit transaction if successful
            return true;
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction if failed

            return $e->getMessage();
        }
    }

    // get customers for the payroll selection
    public function getCustomerDatasForPayroll($skip, $take, $company_id){

        // gettting all the cusrtomer datas of the company based on the company_id
        $customers = Customer::join('address', 'customer.address_id', '=', 'address.address_id')
        ->leftJoin('invoice', 'customer.customer_id', '=', 'invoice.customer_id')
        ->where('customer.company_id', $company_id)
        ->where('customer.is_deleted', 0)
        ->orderBy('customer.customer_id', 'desc')
        ->groupBy(
            'customer.customer_id',
            'customer.customer_name',
            'customer.email_address',
            'customer.phone_number',
            'customer.no_of_assignments',
            'address.city'
        )
        ->selectRaw(
            'customer.customer_id,
            customer.customer_name,
            customer.email_address,
            customer.phone_number,
            customer.no_of_assignments,
            address.city,
            COUNT(CASE WHEN invoice.payroll_status = "pending" and invoice.is_deleted = 0 THEN invoice.invoice_id ELSE NULL END) as invoice_count'
        )
        ->get()
        ->toArray();

        $filtered_data = [];

        foreach($customers as $customer){
            if($customer['invoice_count'] > 0){
                $filtered_data[] = $customer;
                Log::info($filtered_data);
            }
        }

        // counting the total number of customers
        $total = sizeof($filtered_data);

        $customers_data = array_slice($filtered_data, $skip, $take);

        return ['customers' => $customers_data, 'total' => $total];


    }

    // fetch the invoices related to the customers
    public function getInvoicesForCustomer($skip, $take, $customers)
    {

        //getting the selected customers invoices
        $invoices =  Invoice::join('customer as c', 'invoice.customer_id', '=', 'c.customer_id')
            ->join('people as p', 'invoice.people_id', '=', 'p.people_id')
            ->whereIn('invoice.customer_id', $customers)
            ->where(['payroll_status' => 'pending' ,'invoice.is_deleted'=>0]);


        $total = $invoices->count();
        $invoices = $invoices->limit($take)->offset($skip)->get()->toArray();

        return [
            'data' => $invoices,
            'total' => $total
        ];
    }

    //to change the verified status of a payroll batch
    public function postStatusChangeEvent($payroll_batch_id)
    {
        $payroll_update = PayrollBatch::where('payroll_batch_id', $payroll_batch_id)
                        ->update(['payroll_batch_status' => 'Verification']);

        return $payroll_update;
    }

    //get the payroll details for the payroll batch
    public function postPayrollBatchDetails($skip, $take, $payroll_batch_id)
    {
        $payrolls = DB::table('payroll_batch_detail as payroll')
                    ->join('people as people', 'payroll.people_id', '=', 'people.people_id')
                    ->where('payroll.payroll_batch_id', $payroll_batch_id);
        $total = $payrolls->count();
        $payrolls = $payrolls->get()->toArray();

        $formatted_payrolls = [];

        $total_netpay = 0;

        foreach($payrolls as $payroll){
            $invoice_numbers = Invoice::whereIn("invoice_id", json_decode($payroll->invoices))->pluck('invoice_number');
            $payroll->invoice_numbers = json_decode($invoice_numbers);
            $total_netpay += $payroll->net_pay;
            $formatted_payrolls[] = $payroll;
            Log::info($formatted_payrolls);
        }



        $payroll_status = PayrollBatch::where("payroll_batch_id", $payroll_batch_id)
        ->first()
        ->payroll_batch_status;

        return [
            'payrolls' => $formatted_payrolls,
            'total' => $total,
            'status' => $payroll_status,
            'net_pay' => $total_netpay
        ];
    }


    public function updateSelectedInvoices($invoices, $payroll_batch_id)
    {
        $unselected = PayrollCustomerInvoice::whereIn('invoice_id', $invoices)
            ->where([
                'invoice_selected_status' => PayrollCustomerInvoice::STATUS_SELECTED,
                "payroll_customer_invoice.is_deleted" => PayrollCustomer::STATUS_UNDELETED,
            ])
            ->select('invoice_id')
            ->get();

        if (PayrollCustomerInvoice::whereNotIn('invoice_id', $unselected)
            ->where(['payroll_batch_id' => $payroll_batch_id, 'is_deleted' => 0])
            ->update(['invoice_selected_status' => PayrollCustomerInvoice::STATUS_SELECTED])
        ) {
            PayrollBatch::where('payroll_batch_id', $payroll_batch_id)->update(["payroll_batch_status" => "Selected"]);
            return true;
        }


        return false;
    }


    // Selected Invoices
    public function getSelectedInvoices($payroll_batch_id, $skip, $take)
    {
        $PayrollBatchInvoices =  PayrollCustomerInvoice::join('invoice', 'payroll_customer_invoice.invoice_id', '=', 'invoice.invoice_id')
            ->join('people', 'people.people_id', '=', 'invoice.people_id')
            ->join('customer', 'customer.customer_id', '=', 'invoice.customer_id')
            ->join('payroll_batch', 'payroll_batch.payroll_batch_id', '=', 'payroll_customer_invoice.payroll_batch_id')
            ->where([
                'payroll_customer_invoice.payroll_batch_id' => $payroll_batch_id,
                "invoice.is_deleted" => PayrollCustomerInvoice::STATUS_UNDELETED,
                "payroll_customer_invoice.is_deleted" => PayrollCustomer::STATUS_UNDELETED
            ])
            ->select([
                "invoice.invoice_number",
                "invoice.period_end_date",
                "invoice.total_amount",
                "people.people_name",
                "customer.customer_name",
                "payroll_batch.payroll_batch_number",
                "payroll_batch.payroll_batch_name"
            ]);


        $selectedPayrollBatchInvoices = $PayrollBatchInvoices
            ->where(['invoice_selected_status' => PayrollCustomerInvoice::STATUS_SELECTED]);


        $selectedPayrollBatchInvoices = $selectedPayrollBatchInvoices->paginate($take, ['*'], 'page', $skip);
        return $selectedPayrollBatchInvoices;
    }


    // Unselected Invoices
    public function getUnselectedInvoices($payroll_batch_id, $skip, $take)
    {
        $PayrollBatchInvoices =  PayrollCustomerInvoice::join('invoice', 'payroll_customer_invoice.invoice_id', '=', 'invoice.invoice_id')
            ->join('people', 'people.people_id', '=', 'invoice.people_id')
            ->join('payroll_batch', 'payroll_batch.payroll_batch_id', '=', 'payroll_customer_invoice.payroll_batch_id')
            ->join('customer', 'customer.customer_id', '=', 'invoice.customer_id')
            ->where([
                'payroll_customer_invoice.payroll_batch_id' => $payroll_batch_id,
                "invoice.is_deleted" => PayrollCustomerInvoice::STATUS_UNDELETED,
                "payroll_customer_invoice.is_deleted" => PayrollCustomerInvoice::STATUS_UNDELETED
            ])
            ->select([
                "invoice.invoice_number",
                "invoice.period_end_date",
                "invoice.total_amount",
                "people.people_name",
                "customer.customer_name",
                "payroll_batch.payroll_batch_number",
                "payroll_batch.payroll_batch_name"
            ]);

        $batchInvoices = clone $PayrollBatchInvoices;
        $unselectedPayrollBatchInvoices = $PayrollBatchInvoices
            ->where('invoice_selected_status', PayrollCustomerInvoice::STATUS_UNSELECTED)
            ->whereOr('invoice_payrolled_status', PayrollCustomerInvoice::STATUS_UNPAYROLLED);
        $invoices = clone $unselectedPayrollBatchInvoices;
        $unselectedPayrollBatchInvoices = $unselectedPayrollBatchInvoices->paginate($take, ['*'], 'page', $skip);

        if ($batchInvoices->get()->count() == $invoices->count()) {
            PayrollCustomerInvoice::where('payroll_batch_id', $payroll_batch_id)->update(["is_deleted" => PayrollCustomerInvoice::STATUS_DELETED]);
            PayrollCustomer::where('payroll_batch_id', $payroll_batch_id)->update(["is_deleted" => PayrollCustomer::STATUS_DELETED]);
        }

        return $unselectedPayrollBatchInvoices;
    }

    public function updateVerifiedToProcessing($payroll_batch_id)
    {

        try {
            PayrollBatch::where([
                "payroll_batch_id" => $payroll_batch_id
                ])->update(
                    ["payroll_batch_status" => 'Processing']
                );
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateRollback($payroll_batch_id, $people_id)
    {

        try {
            $latestPayrollBatch = PayrollHistory::where(["people_id"=>$people_id,'is_rollback'=> PayrollHistory::STATUS_UNROLLBACK])->orderBy('payroll_history_id','desc')->select(['payroll_batch_id'])->first()->toArray();

        if($latestPayrollBatch['payroll_batch_id'] == $payroll_batch_id ){
        $data =  PayrollHistory::select(["invoices", 'expenses', 'payroll_batch_detail_id'])->where(["people_id" => $people_id, 'payroll_batch_id' => $payroll_batch_id])->first();
        Invoice::whereIn('invoice_id', json_decode($data->invoices))->update(['payroll_status' => 'pending']);
        Expense::whereIn('expense_id', json_decode($data->expenses))->where(['status' => 'processed'])->update(['status' => 'approved']);
        PayrollCustomerInvoice::whereIn('invoice_id', json_decode($data->invoices))
            ->where([
                'payroll_batch_id' => $payroll_batch_id,
                'invoice_selected_status' => PayrollCustomerInvoice::STATUS_SELECTED,
                'invoice_payrolled_status' => PayrollCustomerInvoice::STATUS_PAYROLLED,
            ])->update([
                'invoice_payrolled_status' => PayrollCustomerInvoice::STATUS_UNPAYROLLED,
                'invoice_selected_status' => PayrollCustomerInvoice::STATUS_UNSELECTED,
            ]);
        PayrollBatchDetail::where(['payroll_batch_detail_id' => $data->payroll_batch_detail_id])
            ->update([
                'is_rollback' => PayrollHistory::STATUS_ROLLBACK,
            ]);
        PayrollHistory::where(["payroll_batch_id" => $payroll_batch_id, "people_id" => $people_id])
            ->update([
                'is_rollback' => PayrollHistory::STATUS_ROLLBACK
            ]);
            return true;
        }else{
            return false;
        }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
