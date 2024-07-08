<?php

namespace App\Services;

use App\Mail\PayslipMail;
use App\Models\Invoice;
use App\Models\Payroll;
use Exception;
use App\Models\PayrollHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayslipService
{

    public function showPayrollHistory($skip, $take, $companyId, $organisationId)
    {
        try {
            $query = DB::table('payroll_history as ph')
                ->join('people as p', 'ph.people_id', '=', 'p.people_id')
                ->leftJoin('people_employment_details as ped', 'p.people_id', '=', 'ped.people_id')
                ->leftJoin('people_bank_details as pbd', 'p.people_id', '=', 'pbd.people_id')
                ->leftJoin('payroll_batch as pb', 'pb.payroll_batch_id', '=', 'ph.payroll_batch_id')
                ->where('ph.is_deleted', 0)
                ->where('ph.is_rollback', 0)
                ->where('ph.company_id', $companyId)
                ->where('ph.organisation_id', $organisationId)
                ->select(
                    'ph.*',
                    'p.people_name',
                    'p.job_title',
                    'ped.joining_date',
                    'ped.nino_number',
                    'pbd.bank_name',
                    'pbd.bank_branch',
                    'pbd.account_number',
                    'pb.payroll_batch_name'
                )
                ->orderBy('ph.payroll_history_id', 'desc');

            $total = $query->count();

            $payrollHistory = $query->skip($skip)->take($take)->get();

            return [
                'data' => $payrollHistory,
                'total' => $total,
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error fetching payroll history: ' . $e->getMessage());
            return ['error' => 'Database error fetching payroll history'];
        } catch (\Exception $e) {
            Log::error('General error fetching payroll history: ' . $e->getMessage());
            return ['error' => 'Error fetching payroll history'];
        }
    }

    private function fetchPayrollHistoryDetails($payrollHistoryId)
    {
        $result = DB::table('payroll_history as ph')
            ->join('people as p', 'ph.people_id', '=', 'p.people_id')
            ->leftJoin('people_employment_details as ped', 'p.people_id', '=', 'ped.people_id')
            ->leftJoin('people_bank_details as pbd', 'p.people_id', '=', 'pbd.people_id')
            ->join('company as c', 'ph.company_id', '=', 'c.company_id')
            ->leftJoin('address as a', 'c.address_id', '=', 'a.address_id')
            ->join('organisation as o', 'o.organisation_id', '=', 'ph.organisation_id')
            ->where('ph.payroll_history_id', $payrollHistoryId)
            ->where('ph.is_deleted', 0)
            ->select(
                'ph.gross_salary',
                'ph.taxable_amount',
                'ph.total_payment_amount',
                'ph.er_tax',
                'ph.ee_tax',
                'ph.total_tax_deduction',
                'ph.net_pay',
                'ph.expenses',
                'ph.expense_amount',
                'ph.created_at as payslip_date',
                'p.people_name',
                'p.email_address',
                'p.job_title',
                'o.org_logo',
                'pbd.bank_name',
                'pbd.bank_branch',
                'pbd.account_number',
                'ped.nino_number',
                'ped.joining_date',
                'c.company_name',
                'c.company_logo_path',
                'a.address_line_1',
                'a.address_line_2',
                'a.city',
                'a.state',
                'a.country',
                'a.pincode'
            )
            ->first();

        if (!$result) {
            Log::error("No payroll history found for ID: $payrollHistoryId");
        } else {
            Log::info("Fetched payroll history: " . json_encode($result));
        }

        return $result;
    }

    public function generatePdf($payrollHistoryId)
    {
        try {
            $payrollHistory = $this->fetchPayrollHistoryDetails($payrollHistoryId);

            if (!$payrollHistory) {
                Log::error("Payroll history not found for ID: $payrollHistoryId");
                return ['error' => 'Payroll history record not found or is marked as deleted.', 'status' => 404];
            }

            $data = $this->preparePdfData($payrollHistory);

            if (!$data) {
                Log::error("Failed to prepare PDF data for payroll history ID: $payrollHistoryId");
                return ['error' => 'Failed to prepare PDF data.', 'status' => 500];
            }

            $pdf = Pdf::loadView('pdf.payslip', $data);

            return ['pdf' => $pdf, 'data' => $data, 'status' => 200];
            
        } catch (Exception $e) {
            Log::error("Error generating PDF for PayrollHistory ID: $payrollHistoryId - " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return ['error' => 'An error occurred while generating the PDF.', 'status' => 500];
        }
    }

    private function preparePdfData($payrollHistory)
    {
        if (!$payrollHistory) {
            Log::error("Attempting to prepare PDF data with null payroll history");
            return null;
        }

        $data =  [
            'people_name' => $payrollHistory->people_name ?? 'N/A',
            'people_email' => $payrollHistory->email_address ?? 'N/A',
            'bank' => $payrollHistory->bank_name ?? 'N/A',
            'job_title' => $payrollHistory->job_title ?? 'N/A',
            'branch' => $payrollHistory->bank_branch ?? 'N/A',
            'date_of_joining' => $payrollHistory->joining_date ?? 'N/A',
            'account_no' => $payrollHistory->account_number ?? 'N/A',
            'payslip_date' => \Carbon\Carbon::parse($payrollHistory->payslip_date)->format('d-m-Y'),
            'nino' => $payrollHistory->nino_number ?? 'N/A',
            'gross_salary' => $payrollHistory->gross_salary,
            'taxable_amount' => $payrollHistory->taxable_amount,
            'total_payment_amount' => $payrollHistory->total_payment_amount,
            'expenses' => $payrollHistory->expense_amount,
            'er_tax' => $payrollHistory->er_tax,
            'ee_tax' => $payrollHistory->ee_tax,
            'org_logo' => $payrollHistory->org_logo,
            'total_tax_deduction' => $payrollHistory->total_tax_deduction,
            'total_earnings' => $payrollHistory->gross_salary + $payrollHistory->taxable_amount + $payrollHistory->total_payment_amount + $payrollHistory->expense_amount,
            'total_deductions' => $payrollHistory->er_tax + $payrollHistory->ee_tax + $payrollHistory->total_tax_deduction,
            'net_salary' => $payrollHistory->net_pay,
            'company_name' => $payrollHistory->company_name ?? 'N/A',
            'company_address' => trim("{$payrollHistory->address_line_1}, {$payrollHistory->address_line_2}, {$payrollHistory->city}, {$payrollHistory->state} - {$payrollHistory->pincode}, {$payrollHistory->country}", '.')
        ];

        Log::info("Prepared PDF data: " . json_encode($data));

        return $data;
    }

    public function mailPdf($payrollHistoryId)
    {
        try {
            $result = $this->generatePdf($payrollHistoryId);
            Log::info('PDF generation result: ' . json_encode($result));

            if (isset($result['error'])) {
                Log::error("PDF generation failed: " . $result['error']);
                return $result;
            }

            if (!isset($result['pdf']) || !isset($result['data'])) {
                Log::error("Invalid result structure from generatePdf: " . json_encode($result));
                return ['error' => 'Invalid PDF generation result', 'status' => 500];
            }

            $pdf = $result['pdf'];
            $payslipData = $result['data'];

            $emailData = [
                'subject' => 'Payslip generated for ' . ($payslipData['people_name'] ?? 'Employee'),
                'to_email' => $payslipData['people_email'] ?? null,
                'attachment' => $pdf->output(),
                'payslip_data' => $payslipData
            ];

            Log::info('Email data prepared: ' . json_encode($emailData));

            if (!$emailData['to_email']) {
                Log::error("No recipient email address found in payslip data");
                return ['error' => 'No recipient email address found', 'status' => 500];
            }

            // Mail::send(new PayslipMail($emailData));

            return [
                'customer_mail' => $emailData['to_email'],
                'data' => $emailData,
                'status' => 200,
            ];
        } catch (Exception $e) {
            Log::error('Email generation failed: ' . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return [
                'error' => 'Failed to generate the PDF and send email',
                'status' => 500
            ];
        }
    }

    public function searchPayrollHistory($companyId, $filters)
    {
        Log::info('SearchPayrollHistory service method called with company ID: ' . $companyId);
        Log::info('Filters: ' . json_encode($filters));

        $query = DB::table('payroll_history')
            ->join('people', 'payroll_history.people_id', '=', 'people.people_id')
            ->leftJoin('payroll_batch', 'payroll_history.payroll_batch_id', '=', 'payroll_batch.payroll_batch_id')
            ->where('payroll_history.company_id', $companyId)
            ->where('payroll_history.is_rollback', 0);

        if (!empty($filters['people_name'])) {
            $query->where('people.people_name', 'like', '%' . $filters['people_name'] . '%');
            Log::info('Applying people_name filter: ' . $filters['people_name']);
        }

        if (!empty($filters['payroll_batch_name'])) {
            $query->where('payroll_batch.payroll_batch_name', 'like', '%' . $filters['payroll_batch_name'] . '%');
            Log::info('Applying payroll_batch_name filter: ' . $filters['payroll_batch_name']);
        }

        $total = $query->count();
        Log::info('Total count before pagination: ' . $total);

        $data = $query->select(
            'payroll_history.*',
            'people.people_name',
            'payroll_batch.payroll_batch_name'
        )
            ->skip(($filters['page'] - 1) * $filters['perPage'])
            ->take($filters['perPage'])
            ->get();

        Log::info('Query result count: ' . $data->count());

        $result = [
            'data' => $data,
            'total' => $total,
        ];

        Log::info('Search result: ' . json_encode($result));

        return $result;
    }
}
