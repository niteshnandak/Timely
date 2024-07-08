<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ReflectionFunctionAbstract;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\GeneratedFile;
use App\Models\TimesheetDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    //
    public function generatePdf(Request $request) {

        $invoiceId = $request->id;

        // $invoiceId = is_numeric($invoiceId) ? (int) $invoiceId : 77;

        // dd($invoice);

        DB::beginTransaction();
        try{
            //Fetch Invoice with Id => $request->id
            $invoice = Invoice::find($invoiceId);

            if($invoice->timesheet_id == 0){
                // fetch invoiceDetails using invoice_id
                $invoiceDetails = InvoiceDetail::where('invoice_id', $invoiceId)->get();
            }
            else {
                // $invoiceDetails = InvoiceDetail::where('invoice_id', $invoiceId)->get();

                $invoiceDetails = InvoiceDetail::where('timesheet_id', $invoice->timesheet_id)
                ->where('worker_id', $invoice->worker_id)
                ->where('worker_name', $invoice->worker_name)
                ->where('organisation', $invoice->organisation)
                ->get();
            }

            // // Date when pdf was generated/invoiced
            $invoicedDate = $invoice->created_at; //now();

            // data to be sent to PDF view
            $data = [
                'title' => $invoice->worker_name,
                'date' => $invoicedDate->format('d-m-Y'),
                'invoice' => $invoice,
                'invoiceDetails' => $invoiceDetails
            ];

            // load the view of the pdf
            $pdf = Pdf::loadView('pdf.view', $data);

            // timestamp for unique filename
            $timestamp = now()->timestamp;


            // // UPLOAD PDF GENERATED IN DIRECTORY

            // Generate unique filename for each worker's invoice PDF
            $filename = $invoice->timesheet_id . '_' . $invoice->worker_id . '_' . $invoice->organisation . '_' . $timestamp;

            // Construct the file path
            $filePath = storage_path("app/public/generated_files/{$filename}.pdf");

            // Check if similar file already exists
            if (file_exists($filePath)) {
                // If file exists, delete the old file
                unlink($filePath);
            } // if its getting unliked and then saved, uploaded at should change in table

            // Save the PDF to the file path
            $pdf->save($filePath);


            // Store new generated invoice file details in the generated_files table
            $generatedFile = GeneratedFile::Create(
                [
                    'invoice_id' => $invoice->id,
                    'filename' => $filename,
                    'file_path' => $filePath,
                ]
            );

            // update Invoice Date column
            // $invoice->invoiced_date = $invoicedDate->format('Y-m-d');
            // $invoice->invoiced_date = $invoice->created_at->format('Y-m-d');
            // $invoice->save();
            // set generated pdf status as true in invoice table
            $invoice->generated_status = 1;
            $invoice->save();

            DB::commit();

            return response()->json([
                'toaster_success'=> Config::get('message.success.pdf_generated_success')
            ]);

        }
        catch(Exception $e){
            DB::rollBack();

            // dd($e);
            return response()->json([
                'toaster_error'=>Config::get('message.error.pdf_generated_error'),
            ], 404);
        }

    }





    public function viewPdf(Request $request) {
        // dd($invoice);

        $invoiceId = $request->id;

        $invoiceId = is_numeric($invoiceId) ? (int) $invoiceId : 73;

        $invoice = Invoice::find($invoiceId);

        $generatedFile = GeneratedFile::where('invoice_id', $invoice->id)->where('is_deleted', 0)->first();

        if(!$generatedFile) {
            return response()->json([
                "toaster_error" => Config::get('message.error.pdf_view_error'),
            ]);
        }

        $pdfPath = $generatedFile->file_path;

        if(file_exists($pdfPath)) {

            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice.pdf"',
            ];

            // Stream the file to the user
            return response()->file($pdfPath, $headers);
        }
        else {
            return response()->json([
                'toaster_error' => Config::get('message.error.pdf_view_error'), // $invoice->worker_name
            ]);
        }

    }



    public function downloadPdf(Request $request) {

        $invoiceId = $request->id;

        $invoiceId = is_numeric($invoiceId) ? (int) $invoiceId : 73;
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'toaster_error' => Config::get('message.error.pdf_download_error'),
            ]);
        }

        // fetch the pdf data from generated_file table
        $generatedFile = GeneratedFile::where('invoice_id', $invoice->id)->where('is_deleted', 0)->first();

        // check if that file exists
        if (!$generatedFile) {
            return response()->json([
                'toaster_error' => Config::get('message.error.pdf_download_error'),
            ]);
        }

        // find the path of the invoice pdf
        $pdfPath = $generatedFile->file_path;

        if(file_exists($pdfPath)) {

            // generated filename for pdf to be downloaded
            $filename = $invoice->timesheet_id . '_' . $invoice->worker_id . '_' . $invoice->organisation . '_invoice.pdf';

            // Define headers for the response indicating PDF content type
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];

            // response to download the PDF invoice
            return response()->download($pdfPath);
        }
        else {
            return response()->json([
                'toaster_error' => Config::get('message.error.pdf_download_error'),
            ]);
        }
    }



    public function deletePdf(Request $request){

        $invoiceId = $request->id;

        $invoiceId = is_numeric($invoiceId) ? (int) $invoiceId : 77;

        DB::beginTransaction();
        try{

            //Fetch Invoice with Id => $request->id
            $invoice = Invoice::find($invoiceId);

            // fetch the generated_file row to be deleted
            $generatedFile = GeneratedFile::where('invoice_id', $invoice->id)->where('is_deleted', 0)->first();

            // check if that file exists
            if(!$generatedFile) {
                return response()->json([
                    'toaster_error' => Config::get('message.error.pdf_delete_error'),
                ], 404);
            }

            // find the path in the local folder where pdf is stored
            $pdfPath = $generatedFile->file_path;

            // Check if pdf exists in local folder
            if (file_exists($pdfPath)) {
                // If file exists, delete the invoice pdf
                unlink($pdfPath);
            }
            else {
                return response()->json([
                    'toaster_error' => Config::get('message.error.pdf_delete_error'),
                ], 404);
            }


            // delete pdf data row from generated_files table
            $generatedFile->is_deleted = true;
            $generatedFile->save();

            // set invoiced_date as null
            // $invoice->invoiced_date = null;
            // $invoice->save();

            DB::commit();
            return response()->json([
                'toaster_success' => Config::get('message.success.pdf_delete_success')
            ]);
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'toaster_error' => Config::get('message.error.pdf_delete_error'),
            ], 404);
        }
    }

}
