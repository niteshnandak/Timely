<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoicesController extends Controller
{

    public function index(Request $request){

        try{

            // $skip = $request->skip;
            // $take = $request->take;

            // $skip = is_numeric($skip) ? (int) $skip : 0;
            // $take = is_numeric($take) ? (int) $take : 10;

            /*
            ->limit($take)
            ->offset($skip)
            */

            $lastIndex = Invoice::latest('id')->first()->toArray();
            $allInvoices = Invoice::where('is_deleted',0)->get()->toArray();
            $invoices = Invoice::orderBy('id','DESC')
                                ->where('is_deleted',0)
                                ->get()
                                ->toArray();

            $responseData = [
                'data' => $invoices,
                'total' => count($allInvoices),
                'lastIndex' => $lastIndex
            ];

            return response()->json($responseData);
        }
        catch(exception $e){
            return response()->json([
                "toaster_error" => Config::get('message.error.invoice_fetch_error')
            ], 404);
        }

    }

    public function store(Request $request){

        $request->validate([
            'data.worker_id'=>'required',
            'data.worker_name'=>'required',
            'data.invoice_date'=>'required',
            'data.hourly_pay'=>'required',
            'data.hours_worked'=>'required',
            'data.organisation'=>'required',
        ]);

        try{
            $data=$request->data;

            $data['timesheet_id'] = 0;
            $data["total_amount"] = $data['hourly_pay'] * $data['hours_worked'];
            $data["taxed_amount"] = 1.18 * $data['total_amount'];

            $invoice = Invoice::create($data);

            $data['invoice_id'] = $invoice['id'];

            $invoice_detail = InvoiceDetail::create($data);

            return response()->json([
                "Success"=>Config::get('message.success.invoice_new_success'),
            ],200);
        }
        catch(Exception $e){
            return response()->json([
                "Error"=>Config::get('message.error.invoice_new_error'),
                "data" => $data
            ],400);
        }
    }

    public function editInvoice(Request $request){
        $id = $request->id;
        $data = $request->data;

        // Invoice::where('id',$id)->update($data);

        return response()->json([
            'response'=>'Invoice Edited'.$id
        ]);
    }

    public function deleteInvoice(Request $request){

        $invoiceId = $request->invoiceId;
        Invoice::where('id',$invoiceId)->update(['is_deleted' => 1]);
        $response = [
            'toaster_success' => Config::get('message.success.invoice_delete_success'), // . $invoiceId
        ];

        return response()->json($response);
    }
}
