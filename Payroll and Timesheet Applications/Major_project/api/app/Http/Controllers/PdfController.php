<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ReflectionFunctionAbstract;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\GeneratedFile;
use App\Models\PayrollHistory;
use App\Models\TimesheetDetail;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LDAP\Result;

class PdfController extends Controller
{
    protected $payslipService;

    public function __construct(PayslipService $payslipService)
    {
        $this->payslipService = $payslipService;
    }


    public function viewPdf(Request $request, $id)
    {
        // Validate that the payroll history ID is numeric
        $payrollHistoryId = is_numeric($id) ? (int) $id : null;

        if (is_null($payrollHistoryId)) {
            return response()->json([
                'toaster_error' => 'Invalid payroll history ID provided.',
            ], 400);
        }

        // Generate the PDF using the PayslipService
        $result = $this->payslipService->generatePdf($payrollHistoryId);
        Log::info('PDF generation result:', $result);

        // Check for errors in the PDF generation result
        if (isset($result['error'])) {
            return response()->json([
                'toaster_error' => $result['error'],
            ], $result['status']);
        }

        // Retrieve the generated PDF
        $pdf = $result['pdf'];

        // Stream the PDF download
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'payslip.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="payslip.pdf"',
        ]);
    }
}
