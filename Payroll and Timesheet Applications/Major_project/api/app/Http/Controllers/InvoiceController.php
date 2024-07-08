<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetails;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

// CLASS:INVOICECONTROLLER
class InvoiceController extends Controller
{

    protected $InvoiceService = null;

    // FUNCTION TO GET THE INVOICE SERVICE
    private function getInvoiceService()
    {
        if ($this->InvoiceService == null) {
            $this->InvoiceService = new InvoiceService();
        }
        return $this->InvoiceService;
    }

    // FUNCTION TO GET INVOICES DATA FROM DB
    public function getInvoices($company_id, Request $request)
    {
        try {
            Log::debug($request);
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);

            $data = $this->getInvoiceService()->fetchInvoicesData($company_id, $skip, $take, $request);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" => Config::get('message.error.invoices_fetch')
            ], 404);
        }
    }

    // FUNCTION TO CREATE THE INVOICE AND PUT INTO DB
    public function createInvoice($company_id, Request $request)
    {
        try {
            $validated_data = $request->validate([
                'assignmentNumber' => 'required|string|max:255',
                'periodEndDate' => 'required|date',
                'lineItems' => 'required|array',
                'lineItems.*.description' => 'required',
                'lineItems.*.quantity' => 'required',
                'lineItems.*.unitPrice' => 'required',
            ]);

            $this->getInvoiceService()->putInvoice($company_id, $validated_data);

            // return success message
            return response()->json([
                "message" => Config::get('message.success.invoice_create'),
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            //return error message

            return response()->json([
                "error" => $e->getMessage(),
                "message" => Config::get('message.error.invoice_create')
            ], 400);
        }
    }


    // FUNCTION TO UPDATE THE INVOICE
    public function updateInvoice($company_id, $invoice_id, Request $request)
    {
        try {


            $data = $this->getInvoiceService()->updateInvoice($company_id, $invoice_id, $request);

            // return success message
            return response()->json([
                "message" => Config::get('message.success.invoice_update'),
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" =>  Config::get('message.error.invoice_update')
            ], 404);
        }
    }

    // FUNCTION TO GET THE SELECTED ASSIGNMENTS BASED ON THE COMPANY
    public function getSelectedAssignments($company_id)
    {
        try {
            $data = $this->getInvoiceService()->getSelectedAssignmentsInv($company_id);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" =>  Config::get('message.error.invoice_selected_assignments')
            ], 404);
        }
    }

    // FUNCTION TO GET EDIT DETAILS TO PATCH
    public function getEditDetails($invoice_id)
    {
        try {
            $data = $this->getInvoiceService()->fetchEditInvDetails($invoice_id);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" =>  Config::get('message.error.invoice_patch_details')
            ], 404);
        }
    }

    // FUNCTION TO MAIL INVOICE ON THE FLY
    public function mailInvoice(Request $request)
    {
        try {
            $inv_id = $request->id;
            $data = $this->getInvoiceService()->mailPdf($inv_id);

            //return success message
            return response()->json([
                "message" => Config::get('message.success.invoice_mail')
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" =>  Config::get('message.error.invoice_mail')
            ], 404);
        }
    }

    // FUNCTION TO DOWNLOAD THE INVOICE ON THE FLY
    public function downloadInvoice($inv_id)
    {
        try {

            $data = $this->getInvoiceService()->downloadPdf($inv_id);
            return response($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            ////return error message
            return response()->json([
                "message" => Config::get('message.error.invoice_download')
            ], 500);
        }
    }

    // FUNCTION TO DELETE THE INVOICE USING THE INVID
    public function deleteInvoice($inv_id)
    {
        try {
            $data = $this->getInvoiceService()->deleteInvoice($inv_id);
            return response()->json([
                'message' => Config::get('message.success.invoice_delete')
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            ////return error message
            return response()->json([
                "message" => Config::get('message.error.invoice_delete')
            ], 500);
        }
    }
}
