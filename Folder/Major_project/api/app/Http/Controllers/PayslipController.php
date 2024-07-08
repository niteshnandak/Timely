<?php

namespace App\Http\Controllers;

use App\Mail\PayslipMail;
use App\Models\PayrollHistory;
use App\Services\PayslipService;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Laravel\Prompts\select;

class PayslipController extends Controller
{
    protected $paySlipService;

    public function __construct(PayslipService $paySlipService)
    {
        $this->paySlipService = $paySlipService;
    }

    public function showPayrollHistory(Request $request)
    {
        $skip = $request->query('skip', 0);
        $take = $request->query('take', 10);
        $companyId = $request->query('company_id');
        $organisationId = $request->query('organisation_id');

        $result = $this->paySlipService->showPayrollHistory($skip, $take, $companyId, $organisationId);

        return response()->json($result);
    }

    public function sendMail(Request $request, $id)
    {
        try {
            Log::info('sendMail called with ID: ' . $id);

            $data = $this->paySlipService->mailPdf($id);

            Log::info('Data received from paySlipService: ' . json_encode($data));

            if (isset($data['error'])) {
                Log::error('Error in mailPdf: ' . $data['error']);
                return response()->json(['message' => $data['error']], $data['status']);
            }

            $customer_mail = $data['customer_mail'];
            $email_data = $data['data'];

            Log::info('Attempting to send email to: ' . $customer_mail);
            Log::info('Email Data: ' . json_encode($email_data));

            Mail::to($customer_mail)->send(new PayslipMail($email_data));

            Log::info('Email sent successfully to: ' . $customer_mail);

            return response()->json([
                "message" => "Payslip sent successfully",
            ], 200);
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                "message" => 'Failed to send the mail',
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function searchPayrollHistory($companyId, Request $request)
    {
        Log::info('SearchPayrollHistory called with company ID: ' . $companyId);
        Log::info('Request data: ' . json_encode($request->all()));

        $validated = $request->validate([
            'people_name' => 'nullable|string',
            'payroll_batch_name' => 'nullable|string',
            'page' => 'required|integer',
            'perPage' => 'required|integer',
        ]);

        Log::info('Validated data: ' . json_encode($validated));

        try {
            $data = $this->paySlipService->searchPayrollHistory($companyId, $validated);
            Log::info('Search results: ' . json_encode($data));
            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('Error in searchPayrollHistory: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
