<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use App\Models\Timesheet;
use App\Models\TimesheetDetail;
use App\Models\Assignment;
use App\Models\InvoiceDetails;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class TimesheetToInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:timesheet-to-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating invoices for assignments under timesheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        //Select timesheets with invoice_status as 'In-Progress'
        $timesheets = Timesheet::where('invoice_status', 'In Progress')
                    ->where('is_deleted', 0)
                    ->select(['timesheet_id'])
                    ->get();

        // Get timesheet details for the selected timesheets above
        $timesheet_details = TimesheetDetail::whereIn('timesheet_id', $timesheets)
                            ->where('is_deleted',0)
                            ->get();

        /**
         * Timesheet Detail to Invoice Detail
         */
        foreach($timesheet_details as $timesheet_detail){

            $assignment_info = Assignment::where('assignment_num', $timesheet_detail['assignment_num'])->get()->first();
            $timesheetFortd = Timesheet::where('timesheet_id', $timesheet_detail['timesheet_id'])->get()->first();

            $customer = Customer::where('customer_id', $assignment_info['customer_id'])->first();
            $customer_vat_percentage = rtrim($customer->customer_vat_percentage, '%');
            $vat_percentage = floatval($customer_vat_percentage);

            $amount = $timesheet_detail['quantity'] * $timesheet_detail['unit_price'];
            $gross_amount = $amount + $amount*($vat_percentage/100);

            $data = InvoiceDetails::create([
                'timesheet_id' => $timesheet_detail['timesheet_id'],
                'timesheet_detail_id' => $timesheet_detail['timesheet_detail_id'],
                'assignment_id' => $assignment_info['assignment_id'],
                'people_id' => $assignment_info['people_id'],
                'customer_id' => $assignment_info['customer_id'],
                'company_id' => $assignment_info['company_id'],
                'organisation_id' => $assignment_info['organisation_id'],
                'quantity' => $timesheet_detail['quantity'],
                'unit_price' => $timesheet_detail['unit_price'],
                'description' => $timesheet_detail['description'],
                'period_end_date' => $timesheetFortd['period_end_date'],
                'vat_percent' => $vat_percentage,
                'gross_amount' => $gross_amount,
                'invoice_status' => 'false'
            ]);
        }

        $invoice_details = DB::table('invoice_details as i')
        ->join('timesheet as t', 'i.timesheet_id', '=', 't.timesheet_id')
        ->where('i.is_deleted',0)
        ->where('i.invoice_status', 'false')
        ->selectRaw('i.timesheet_id, i.period_end_date, invoice_date, assignment_id, i.company_id, i.organisation_id, i.people_id, i.customer_id, SUM(gross_amount) as total_amount')
        ->groupBy('i.timesheet_id', 'assignment_id', 'i.period_end_date', 'invoice_date', 'i.company_id', 'i.organisation_id', 'i.people_id', 'i.customer_id')
        ->get()->toArray();

        foreach($invoice_details as $invoice){

            $invoiceArray = (array)$invoice;
            $invoiceArray['invoice_type'] = 'cron';

            $invoices = Invoice::create($invoiceArray);

            InvoiceDetails::where('people_id', $invoice->people_id)
            ->where('timesheet_id', $invoice->timesheet_id)
            ->where('assignment_id', $invoice->assignment_id)
            ->update([
                'invoice_status' => 'true',
                'invoice_id' => $invoices->invoice_id
            ]);
        }

        Timesheet::where('invoice_status', 'In Progress')
        ->update([
            'invoice_status' => 'Completed'
        ]);
    }
}
