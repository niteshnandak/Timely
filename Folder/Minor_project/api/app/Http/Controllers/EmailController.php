<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use App\Models\GeneratedFile;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;


class EmailController extends Controller
{
    protected $emailService;
    public function mailInvoice(Request $request)
    {
        $invoiceId = $request->id;
        try {
            // Fetching the generatedfile record associated with the given invoice id
            $generatedFile = GeneratedFile::where('invoice_id', $invoiceId)->first();

            //check for whether file exists or not
            if (!$generatedFile) {
                return response()->json([
                    'message' => Config::get('message.error.mail_failed_invoice_generated_file_error'),
                ], 404);
            }

            // fetching the associated invoice record
            $invoice = Invoice::where('id', $invoiceId)->first();

            //the file path from the generated file
            $filePath = $generatedFile->file_path;

            //check whether the file exists in the path or not
            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => Config::get('message.error.mail_failed_pdf_not_exist_error'),
                ], 404);
            }


            // data to carry forward to the customer mail
            $data = [
                'subject' => 'Invoice generated for ' . $invoice->worker_name,
                'attachment' => $filePath,
                'to_email' => 'hello@gmail.com',
                'invoice' => $invoice
            ];

            // Send email including the data
            $this->getEmailService()->sendEmail($data);

            // updating mail_status in invoice table
            $invoice->mail_status = true;
            $invoice->save();

            // If email sending is successful, display the success message
            return response()->json([
                'message' => Config::get('message.success.mail_sent_success'),
            ], 200);
        } 
        
        catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage()); // Log the exception
            return response()->json([
                'message' => Config::get('message.error.mail_failed_error'),
            ], 404);
        }
    }


    // Lazy creation
    private function getEmailService()
    {
        if ($this->emailService == null) {
            $this->emailService = new EmailService();
        }
        return $this->emailService;
    }
}
