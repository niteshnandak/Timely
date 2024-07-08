<?php

namespace App\Http\Controllers;

use App\Exports\PayrollBatchExport;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PayrollBatch;
use App\Models\PayrollBatchDetail;
use App\Models\PayrollCustomerInvoice;
use App\Models\PayrollHistory;
use App\Models\TaxBrand;
use App\Services\PayrollService;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

use function Laravel\Prompts\select;
use function Psy\debug;

class PayrollController extends Controller
{
    private $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    // public function showPayrollBatches(Request $request)
    // {
    //     try {
    //         $skip = $request->query('skip', 0);
    //         $take = $request->query('take', 10);
    //         $companyId = $request->company_id;
    //         $organisationId = $request->organisation_id;

    //         $searchData=$request->searchData;
    //         Log::info($searchData);

    //         $result = $this->payrollService->showPayrollBatches($skip, $take, $companyId, $organisationId, $searchData);

    //         return response()->json($result);
    //     } catch (Exception $e) {
    //         Log::error('Error in showPayrollBatches: ' . $e->getMessage());
    //         return response()->json(['toaster_error' => Config::get('message.error.payroll_batch_retrieve_error')], 500);
    //     }
    // }

    public function showPayrollBatches(Request $request)
    {
        try {
            $validated = $request->validate([
                'skip' => 'integer|min:0',
                'take' => 'integer|min:1',
                'company_id' => 'required|integer',
                'searchData' => 'array',
                'searchData.payroll_batch_number' => 'string|nullable',
                'searchData.payroll_batch_name' => 'string|nullable',
                'searchData.payroll_batch_start_date' => 'date|nullable',
                'searchData.payroll_batch_end_date' => 'date|nullable',
                'searchData.payroll_batch_status' => 'string|nullable',
            ]);

            $skip = $validated['skip'] ?? 0;
            $take = $validated['take'] ?? 10;
            $companyId = $validated['company_id'];
            $searchData = $validated['searchData'] ?? null;

            $result = $this->payrollService->showPayrollBatches($skip, $take, $companyId, $searchData, $request->organisation_id);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::error('Error in showPayrollBatches: ' . $e->getMessage());
            return response()->json(['toaster_error' => Config::get('message.error.payroll_batch_retrieve_error')], 500);
        }
    }



    public function addPayrollBatch(Request $request)
    {
        try {

            $request->validate([
                'payroll_batch_name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-\/., ]+$/' // This regex matches any character that is not allowed
                ],
                'payroll_batch_date' => 'required|date',
                'company_id' => 'required|integer|exists:company,company_id',
            ]);

            $payrollBatch = $this->payrollService->addPayrollBatch($request->all());
            return response()->json([
                // $payrollBatch,
                'payroll_batch' => $payrollBatch,
                'toaster_success' => Config::get('message.success.payroll_batch_add_success')
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating payroll batch: ' . $e->getMessage());
            return response()->json(['toaster_error' => Config::get('message.error.payroll_batch_add_error')], 500);
        }
    }

    public function generateReport($id)
    {
        try {
            $payrollBatch = $this->payrollService->getPayrollBatchForReport($id);

            $export = new PayrollBatchExport($payrollBatch);
            return Excel::download($export, 'payroll_batch_report.xlsx');
        } catch (Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            return response()->json(['toaster_error' => Config::get('message.error.payroll_report_error')], 500);
        }
    }

    public function deletePayrollBatch(Request $request)
    {
        try {
            $id = $request->id;
            $result = $this->payrollService->deletePayrollBatch($id);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Error in deletePayrollBatch: ' . $e->getMessage());
            return response()->json(['toaster_error' => Config::get('message.error.payroll_batch_delete_error')], 500);
        }
    }


    // fetch Customers for the payroll selection

    public function getCustomerDatasForPayroll(Request $request){

        try{
            // Updating the skip and take to get the number of datas for the front end and the customer_id
            $skip = $request->query('skip',0);
            $take = $request->query('take',10);
            $company_id = $request->query('company_id');
            $order = 'desc';


            //sending the datas to the service of the customer to get the datas to send to the front end
            $customers_data = $this->payrollService->getCustomerDatasForPayroll($skip, $take, $company_id);

            // getting the active customer datas and count of the active customers
            $customers_active = $customers_data['customers'];
            $total = $customers_data['total'];


            // sending the required datas to the front end
            return response()->json([
                'customerActive' => $customers_active,
                'total' => $total
            ], 200);

        } catch(\Exception $e){


            // checking for the error and sending error message to the front end
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    // fetch the invoices related to the customers
    public function getInvoicesForCustomer(Request $request)
    {

        try {
            $skip = $request->query('skip');
            $take = $request->query('take');
            $customers = $request->all()['customers'];
            $invoices_for_customer = $this->payrollService->getInvoicesForCustomer($skip, $take, $customers);

            if ($invoices_for_customer['total'] == 0) {
                return response()->json([
                    "message" => Config::get('message.error.payroll_invoice_nodata')
                ], 400);
            }

            return response()->json($invoices_for_customer, 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                "message" => Config::get('message.error.payroll_invoice_retrieve_error')
            ], 400);
        }
    }

    // fetch the payroll details for the

    public function postPayrollBatchDetails(Request $request)
    {
        $payroll_batch_id = $request->all()['payroll_batch_id'];

        $skip = $request->query('skip');
        $take = $request->query('take');

        $payroll_details = $this->payrollService->postPayrollBatchDetails($skip, $take, $payroll_batch_id);

        return response()->json($payroll_details);
    }


    // Create the payroll customer and their invoices
    public function create_payroll_customer(Request $request)
    {
        $invoices = $request->invoices;
        $payroll_batch_id = (int)  $request->payroll_batch_id;

        $isCreated =   $this->payrollService->createPayrollCustomer($invoices, $payroll_batch_id);
        if ($isCreated) {
            $batchInvoice = $this->payrollService->updateSelectedInvoices($invoices, $payroll_batch_id);
            if ($batchInvoice) {
                return response()->json([
                    "toaster_success" => Config::get('message.success.payroll_customer_created')
                ], 200);
            } else {
                return response()->json([
                    "toaster_unselected_error" => Config::get('message.error.payroll_unselected_invoices')
                ], 200);
            }
        } else {
            return response()->json([
                "toaster_error" => Config::get('message.error.payroll_customer_error')
            ], 400);
        }
    }

    public function payroll_selected_invoices(Request $request)
    {
        $skip = $request->query('skip', 0);
        $take = $request->query('take', 10);
        $payroll_batch_id = (int)  $request->payroll_batch_id;
        $selectedPayrollBatchInvoices = $this->payrollService->getSelectedInvoices($payroll_batch_id, $skip, $take);
        return response()->json([
            "selected" => $selectedPayrollBatchInvoices
        ]);
    }

    public function postStatusChangeEvent(Request $request)
    {
        $payroll_batch_id = $request->all()['payroll_batch_id'];

        $payroll_update = $this->payrollService->postStatusChangeEvent($payroll_batch_id);

        return response()->json([
            'message' => $payroll_update
        ]);
    }

    public function payroll_unselected_invoices(Request $request)
    {
        $skip = $request->query('skip', 0);
        $take = $request->query('take', 10);
        $payroll_batch_id = (int)  $request->payroll_batch_id;
        $unselectedPayrollBatchInvoices = $this->payrollService->getUnselectedInvoices($payroll_batch_id, $skip, $take);
        return response()->json([
            "unselected" => $unselectedPayrollBatchInvoices,
        ]);
    }


    function updatePayrollProcess(Request $request)
    {
        $payroll_batch_id = (int)  $request->payroll_batch_id;
        $payroll_update = $this->payrollService->updateVerifiedToProcessing($payroll_batch_id);
        if ($payroll_update) {
            return response()->json([
                "toaster_success" => Config::get('message.success.update_payroll_Process_success')
            ], 201);
        } else {
            return response()->json([
                "toaster_error" => Config::get('message.error.update_payroll_Process_error')
            ], 400);
        }
    }

    function rollbackPayrollBatch(Request $request)
    {
        $payroll_batch_id = (int)  $request->payroll_batch_id;
        $people_id = (int)  $request->people_id;
        $payroll_update = $this->payrollService->updateRollback($payroll_batch_id, $people_id);
        if ($payroll_update) {
            return response()->json([
                "toaster_success" => Config::get('message.success.payroll_rollback_success')
            ]);
        } else {
            return response()->json([
                "toaster_error" => Config::get('message.error.payroll_rollback_error')
            ], 400);
        }
    }


    function payroll_test()
    {
        $people_id = 16;
        $payroll_batch_id = 110;




        //    $data=  PayrollHistory::join(
        //     "payroll_customer_invoice",
        //     "payroll_history.payroll_batch_id",
        //     "=",
        //     "payroll_customer_invoice.payroll_batch_id")
        // //     ->join(
        // //     'payroll_batch_detail',
        // //     'payroll_batch_detail.payroll_batch_id',
        // //     '=',
        // //     'payroll_history.payroll_batch_id'
        // // )
        //     // ->join(
        //     //     'invoice',
        //     //     'payroll_customer_invoice.invoice_id',
        //     //     '=',
        //     //     'invoice.invoice_id'
        //     // )
        //     // ->leftjoin(
        //     //     'expenses',
        //     //     'expenses.people_id',
        //     //     '=',
        //     //     'payroll_history.people_id'
        //     // )
        //     ->where([
        //         'payroll_history.people_id' => $people_id,
        //         'payroll_history.payroll_batch_id' => $payroll_batch_id,
        //         'payroll_customer_invoice.is_deleted' => PayrollCustomerInvoice::STATUS_UNDELETED,
        //         'payroll_customer_invoice.invoice_selected_status' => PayrollCustomerInvoice::STATUS_DELETED,
        //         'payroll_customer_invoice.invoice_payrolled_status' => PayrollCustomerInvoice::STATUS_DELETED,
        //     ])
        //     ->when('expenses.status' == 'processed',
        //         function($query) {
        //           return $query
        //                  ->where(["expenses.status"=>'processed']);
        //          })
        //     ->get()->toArray();
        //    dd($data);


    }
}
