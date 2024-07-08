<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organisation;
use App\Models\Assignment;
use App\Models\User;
use App\Models\Company;
use App\Models\People;
use App\Models\Address;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\InvoiceFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SendInvoiceMail;
use App\Models\Customer;
use App\Models\MailTrack;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;



class InvoiceService
{

    // SERVICE TO FETCH THE INVOICES DATA
    public function fetchInvoicesData($company_id, $skip, $take, $request)
    {
        try {
            // Get the company name
            $company_name = Company::where('company_id', $company_id)->pluck('company_name')->first();

            // Initialize the query
            $query = DB::table('invoice')
                ->leftJoin('people', 'invoice.people_id', '=', 'people.people_id')
                ->leftJoin('assignment', 'invoice.assignment_id', '=', 'assignment.assignment_id')
                ->leftJoin('customer', 'invoice.customer_id', '=', 'customer.customer_id')
                ->select('invoice.*', 'people.people_name', 'assignment.assignment_num', 'customer.customer_name')
                ->where('invoice.company_id', $company_id)
                ->where('invoice.is_deleted', 0)
                ->orderBy('invoice.created_at', 'DESC');

            // Apply search filters if present
            if ($request->searchFormData) {
                $invoiceNumber = $request->searchFormData['invoiceNumber'];
                $payrollStatus = $request->searchFormData['payrollStatus'];
                $EmailStatus = $request->searchFormData['EmailStatus'];
                $peopleName = $request->searchFormData['peopleName'];
                $periodEndDate = $request->searchFormData['periodEndDate'];

                if ($invoiceNumber) {
                    $query->where('invoice.invoice_number', 'like', '%' . $invoiceNumber . '%');
                }

                if ($payrollStatus) {
                    $query->where('invoice.payroll_status', $payrollStatus);
                }
                if ($EmailStatus) {
                    $query->where('invoice.email_status', $EmailStatus);
                }

                if ($peopleName) {
                    $query->where('people.people_name', 'like', '%' . $peopleName . '%');
                }

                if ($periodEndDate) {
                    $query->where('invoice.period_end_date', $periodEndDate);
                }
            }

            // Get the total count of invoices matching the criteria
            $total_invoice_count = $query->count();

            // Fetch the invoices data with pagination
            $invoices_data = $query->skip($skip)
                ->take($take)
                ->get();

            Log::debug($invoices_data);

            // Return the result
            return [
                'invoices_data' => $invoices_data,
                'total_invoice_count' => $total_invoice_count,
                'company_name' => $company_name
            ];
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }



    // SERVICE TO UPDATE THE INVOICE
    public function updateInvoice($company_id, $invoice_id, $validated_data)
    {
        try {
            $total_amount = 0;
            $net_total = 0;
            DB::beginTransaction();

            // assignment details
            $assignment = Assignment::where('assignment_num', $validated_data['assignmentNumber'])->first();

            // check if the periodEndDate is between start_date and end_date
            if ($validated_data['periodEndDate'] < $assignment->start_date || $validated_data['periodEndDate'] > $assignment->end_date) {
                throw new Exception('The period end date is not within the Assignment range.');
            }

            // customer details
            $customer = Customer::where('customer_id', $assignment->customer_id)->first();
            $customer_vat_percentage = rtrim($customer->customer_vat_percentage, '%');
            $vat_percentage = floatval($customer_vat_percentage);

            $invoice = Invoice::where('invoice_id', $invoice_id)->first();

            // update invoice
            $invoice->update([
                'company_id' => $assignment->company_id,
                'assignment_id' => $assignment->assignment_id,
                'invoice_type' => "manual",
                'people_id' => $assignment->people_id,
                'customer_id' => $assignment->customer_id,
                'organisation_id' => $assignment->organisation_id,
                'assignment_number' => $validated_data['assignmentNumber'],
                'period_end_date' => $validated_data['periodEndDate'],
            ]);


            // create new line items
            $lineItems = [];
            foreach ($validated_data['lineItems'] as $lineItem) {
                $net_amount = $lineItem['quantity'] * $lineItem['unitPrice'];
                $net_total += $net_amount;

                $lineItems[] = [
                    'company_id' => $company_id,
                    'assignment_id' => $assignment->assignment_id,
                    'people_id' => $assignment->people_id,
                    'customer_id' => $assignment->customer_id,
                    'organisation_id' => $assignment->organisation_id,
                    'period_end_date' => $validated_data['periodEndDate'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unitPrice'],
                    'vat_percent' => $vat_percentage,
                    'gross_amount' => $net_amount,
                    'invoice_id' => $invoice->invoice_id,
                ];
            }

            // delete existing line items
            InvoiceDetails::where('invoice_id', $invoice_id)->delete();

            // insert the new line items into the database
            foreach ($lineItems as $item) {
                InvoiceDetails::create($item);
            }

            $total_amount = $net_total + (($vat_percentage / 100) * $net_total);

            // check if the total amount is less than= 0
            if ($total_amount <= 0) {
                throw new Exception('Total amount cannot be less than One.');
            }

            // update the total amount in the invoice
            $invoice->update([
                'total_amount' => $total_amount
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }


    // SERVICE TO CREATE AND ADD INTO THE DB
    public function putInvoice($company_id, $validated_data)
    {
        // try
        try {
            $total_amount = 0;
            $net_total = 0;

            // retrieve assignment details (if no record found, throws an exception)
            $assignment = Assignment::where('assignment_num', $validated_data['assignmentNumber'])->first();

            // check if the periodEndDate is between start_date and end_date
            if ($validated_data['periodEndDate'] < $assignment->start_date || $validated_data['periodEndDate'] > $assignment->end_date) {
                throw new Exception('The period end date is not within the Assignment range.');
            }



            // Get customer and company details
            $customer = Customer::where('customer_id', $assignment->customer_id)->first();
            $company = Company::where('company_id', $assignment->company_id)->first();

            // Check if customer VAT percentage is null
            if ($customer->customer_vat_percentage === null) {
                // If null, use company VAT percentage
                $vat_percentage = rtrim($company->company_vat_percentage, '%');
            } else {
                // Otherwise, use customer VAT percentage
                $vat_percentage = rtrim($customer->customer_vat_percentage, '%');
            }

            // Convert VAT percentage to float
            $vat_percentage = floatval($vat_percentage);


            // create the line items
            $lineItems = [];
            foreach ($validated_data['lineItems'] as $lineItem) {
                $net_amount = $lineItem['quantity'] * $lineItem['unitPrice'];
                $net_total += $net_amount;

                $lineItems[] = [
                    'company_id' => $assignment->company_id,
                    'assignment_id' => $assignment->assignment_id,
                    'people_id' => $assignment->people_id,
                    'customer_id' => $assignment->customer_id,
                    'organisation_id' => $assignment->organisation_id,
                    'period_end_date' => $validated_data['periodEndDate'],
                    'description' => $lineItem['description'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unitPrice'],
                    'vat_percent' => $vat_percentage,
                    'gross_amount' => $net_amount,
                ];
            }

            //  BODMAS

            $total_amount = $net_total + (($vat_percentage / 100) * $net_total);

            // check if the total amount is less than= 0
            if ($total_amount <= 0) {
                throw new Exception('Total amount cannot be less than One.');
            }

            // create the main invoice
            $invoice = Invoice::create([
                'company_id' => $company_id,
                'assignment_id' => $assignment->assignment_id,
                'invoice_type' => "manual",
                'people_id' => $assignment->people_id,
                'customer_id' => $assignment->customer_id,
                'organisation_id' => $assignment->organisation_id,
                'assignment_number' => $validated_data['assignmentNumber'],
                'period_end_date' => $validated_data['periodEndDate'],
                'total_amount' => $total_amount,
            ]);

            // insert the line items into the database using the create method
            foreach ($lineItems as $item) {
                $item['invoice_id'] = $invoice->invoice_id;
                InvoiceDetails::create($item);
            }
        }
        // catch
        catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }


    // SERVICE TO SELECTED ASSIGNMENTS WHILE CREATING
    public function getSelectedAssignmentsInv($company_id)
    {
        // try
        try {
            //all the assignment_num,people_id under the company are stored from assignment model.
            $assignments = Assignment::where('company_id', $company_id)
                ->where('is_deleted', 0)
                ->get(['assignment_num', 'people_id']);
            //people ids are plucked from the stored $assignments detail list
            $people_ids = $assignments->pluck('people_id');
            //people_id,peole_name are stored using peopleids.
            $people = People::whereIn('people_id', $people_ids)->get(['people_id', 'people_name']);
            //people names are mapped according to their ids in the form of associative array
            $people_map = $people->keyBy('people_id')->map(function ($person) {
                return $person->people_name;
            });

            //adding people names to the assignment creating a new people name.

            //return
            return $assignments->map(function ($assignment) use ($people_map) {
                $assignment->people_name = $people_map->get($assignment->people_id);
                return $assignment;
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    // SERVICE TO GET EDIT DETAILS TO PATCH WHILE EDITING
    public function fetchEditInvDetails($invoice_id)
    {
        $inv_record = Invoice::where('invoice_id', $invoice_id)->first();
        $assignment = Assignment::where('assignment_id', $inv_record->assignment_id)->first();
        $invoice_details = InvoiceDetails::where('invoice_id', $invoice_id)
            ->get();
        $invoice_details_count = sizeof($invoice_details);

        //return
        return ['assignment_num' => $assignment, 'inv_record' => $inv_record, 'invoice_details' => $invoice_details, 'invoice_details_count' => $invoice_details_count];
    }


    // SERVICE TO FETCH THE PARTICULAR INV DATA TO USE FOR SEND MAIL OR DOWNLOAD PDF.
    public function fetchInvoiceData($inv_id)
    {
        $invoice = Invoice::where('invoice_id', $inv_id)->first();
        $invoice_details = InvoiceDetails::where('invoice_id', $inv_id)->get();
        $organisation = Organisation::where('organisation_id', $invoice->organisation_id)->first();
        $assignment = Assignment::where('assignment_id', $invoice->assignment_id)->first();
        $customer = Customer::where('customer_id', $invoice->customer_id)->first();
        $company = Company::where('company_id', $invoice->company_id)->first();
        $people = People::where('people_id', $invoice->people_id)->first();
        $address = Address::where('address_id', $customer->address_id)->first();
        $logo_path = storage_path('app/public/images/' . $organisation->org_logo);

        /** Getting Image - 1 */
        //  $logo_path = Storage::disk('public')->path('images/' . $organisation->org_logo);

        /** Getting Image - 2 */
        // $logo_path = Storage::disk('storage')->path($organisation->org_logo);

        $invoice_data = ["invoice" => $invoice, "invoice_details" => $invoice_details, "organisation" => $organisation, "assignment" => $assignment, "customer" => $customer, "company" => $company, "people" => $people, "address" => $address, "logo_path" => $logo_path];

        //return
        return $invoice_data;
    }

    // SERVICE TO MAIL PDF ON THE FLY
    public function mailPdf($inv_id)
    {
        try {

            $invoice_data = $this->fetchInvoiceData($inv_id);
            $customer_name = $invoice_data['customer']->customer_name;
            $customer_mail = $invoice_data['customer']->email_address;
            $people_name = $invoice_data['people']->people_name;

            $pdf = Pdf::loadView('invoice-pdf.invoice-pdf', compact('invoice_data'));

            $message_id = Str::random(60);
            $invoice_data['message_id'] = $message_id;

            $email_data = [
                'template_data' => $invoice_data,
                'subject' => 'Invoice generated for ' . $people_name,
                'to_email' => $customer_mail,
                'attachment' => $pdf->output(),
            ];
            if (Mail::send(new SendInvoiceMail($email_data))) {
                MailTrack::create([
                    'sender_email'=>"timely@gmail.com",
                    'recipient_name'=>$customer_name,
                    'recipient_email'=>$customer_mail,
                    'subject'=>$email_data['subject'],
                    'message_id'=>$message_id,
                    'type'=>"invoice",
                    'type_id'=>$inv_id,
                    'status'=>"success",
                ]);
            }
            else{
                Log::debug("hmm i survived");
                MailTrack::create([
                    'sender_email'=>"timely@gmail.com",
                    'recipient_name'=>$customer_name,
                    'recipient_email'=>$customer_mail,
                    'subject'=>$email_data['subject'],
                    'message_id'=>$message_id,
                    'type'=>"invoice",
                    'type_id'=>$inv_id,
                    'status'=>"failed",
                ]);
                throw new Exception("Failed to send mail");

            }

            Invoice::where('invoice_id', $inv_id)
                ->update(['email_status' => "yes"]);

            // return
            return $invoice_data;
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            throw $e;
        }
    }


    // SERVICE TO DOWNLOAD THE PDF
    public function downloadPdf($inv_id)
    {
        // try
        try {
            $invoice_data = $this->fetchInvoiceData($inv_id);
            $pdf = Pdf::loadView('invoice-pdf.invoice-pdf', compact('invoice_data'));
            //stores the pdf in the form of binary string data
            $pdfBinary = $pdf->output();
            //  $invoice_number = $invoice_data['invoice']->invoice_number;
            $response = response()->make($pdfBinary, 200);
            //content type make that response contains the pdf
            $response->header('Content-Type', 'application/pdf');
            //browser treating as attachment
            $response->header('Content-Disposition', 'attachment; filename="invoice.pdf"');

            // return
            return $response;
        }
        // catch
        catch (\throwable $e) {
            //laravel log error
            Log::error($e);
        }
    }

    // SERVICE TO DELETE THE INVOICE
    public function deleteInvoice($inv_id)
    {
        //try
        try {
            $authUser = Auth::user();
            $invoice = Invoice::where('invoice_id', $inv_id)->first();
            if ($invoice) {
                $invoice->is_deleted = Invoice::STATUS_ACTIVE;
                $invoice->updated_by = $authUser->user_id;
                $invoice->updated_at = Carbon::now();
                $invoice->save();
            }
            // update all the invoicedetails is deleted status
            InvoiceDetails::where('invoice_id', $inv_id)->update([
                'is_deleted' => Invoice::STATUS_ACTIVE,
                'updated_by' => $authUser->user_id,
                'updated_at' => Carbon::now()
            ]);
        }

        //catch
        catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
