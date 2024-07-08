<?php

namespace App\Http\Controllers;

use App\Models\TaskSchedular;
use App\Models\Timesheet;
use App\Models\TimesheetDetail;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimesheetDetailController extends Controller
{
    public function show(String $timesheet_id, Request $request){

        $skip = $request->query("skip", 0);
        $take = $request->query("take", 10);
        $timesheet = Timesheet::where('id', $timesheet_id);

        $csv_flag = $timesheet->first()->upload_type_csv;
        $count = $timesheet->first()->timesheet_detail_count;
        $invoice_status = $timesheet->first()->invoice_status;
        $timesheet_name = $timesheet->first()->timesheet_name;
        $timesheet_id = $timesheet->first()->id;
        $timesheetDetails = TimesheetDetail::where('timesheet_id', $timesheet_id)->where('is_deleted', 0)->orderBy('id', 'DESC');
        $total = count($timesheetDetails->get()->toArray());
        $timesheetDetails = $timesheetDetails->limit($take)->offset($skip)->get()->toArray();
        $check_empty = empty($timesheetDetails);
        $user =Auth::user();
        // return view('timesheets.timesheetdetail',['timesheet_name' => $timesheet_name, 'timesheet_id' => $timesheet_id, 'csv_flag' => $csv_flag, 'checkEmpty' => $check_empty], compact('timesheetDetails'));
        return response()->json([
            'timesheets_name' => $timesheet_name,
            'timesheet_detail' => $timesheetDetails,
            'csv_flag' => $csv_flag,
            'invoice_status' => $invoice_status,
            'check_empty' => $check_empty,
            'timesheet_count' => $count,
            'user' => $user,
            'total' => $total,
        ]);

        if(!$check_empty){
            return response()->json([
                'timesheets_name' => $timesheet_name,
                'timesheet_detail' => $timesheetDetails,
                'csv_flag' => $csv_flag,
                'check_empty' => $check_empty,
                'user' => "user",
                'total' => $total,
            ],200);
            // return [
            //     'timesheets_name' => $timesheet_name,
            //     'timesheet_detail' => $timesheetDetails,
            //     'csv_flag' => $csv_flag,
            //     'check_empty' => $check_empty,
            //     'user' => "user",
            //     'total' => $total,
            // ];

        }else{
            return response()->json([
                "error" => "No Such records found"
            ],500);
        }


    }

    public function addRow(Request $request, $timesheet_id){
        $validatedData = $request->validate([
            'worker_name' => 'required|string|max:255',
            'worker_id' => 'required|numeric',
            'timesheet_detail_date' => 'required|date',
            'organisation' => 'required|string|max:255',
            'hourly_pay' => 'required|numeric',
            'hours_worked' => 'required|numeric',
        ]);

        try{
            DB::beginTransaction();
            $timesheetDetail = new TimesheetDetail();
            $timesheetDetail->timesheet_id = $timesheet_id;
            $timesheetDetail->worker_name = $request->worker_name;
            $timesheetDetail->worker_id = $request->worker_id;
            $timesheetDetail->timesheet_detail_date = $request->timesheet_detail_date;
            $timesheetDetail->organisation = $request->organisation;
            $timesheetDetail->hourly_pay = $request->hourly_pay;
            $timesheetDetail->hours_worked = $request->hours_worked;
            $timesheetDetail->draft_status = 1; // Set draft status
            $timesheetDetail->save();

            $count = TimesheetDetail::where('timesheet_id', $timesheet_id)->count();
            Timesheet::where("id", $timesheet_id)->update(['timesheet_detail_count' => $count]);

            $jsonData = [
                'timesheet_id' => $timesheetDetail->timesheet_id,
                'timesheet_detail_id' => $timesheetDetail->id,
                'upload_type' => 'manual'
            ];

            $data_schedular['timesheet_detail_id'] = $timesheetDetail->id;
            $data_schedular['task_id'] = 1;
            $data_schedular['status'] = "Pending";
            $user =Auth::user();

            $data_schedular['created_by'] = $user->id;
            $data_schedular['updated_by'] = $user->id;
            $data_schedular['param'] = json_encode($jsonData);
            $data_schedular['timesheet_id'] = $timesheetDetail->timesheet_id;
            TaskSchedular::create($data_schedular);
            DB::commit();
        } catch(\Exception $e){
            DB::rollback();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            dd($e->getMessage());
        } finally{
            return response()->json([
                "message"=>"success",
            ]);
        }
    }

    public function edit($timesheet_id, $id){
        $timesheet_details = TimesheetDetail::where('id', $id)->first();
        // return view('timesheets.edittimesheet',['timesheet_details'=>$timesheet_details, 'timesheet_id'=>$timesheet_id, 'id'=>$id]);
        return response()->json([
            'response'=> "timesheet detail edited".$id
        ]);
    }

    public function update(Request $request, $timesheet_id, $id){

        $data = $request->validate([
            'worker_name' => 'required',
            'worker_id' => 'required',
            'organisation' => 'required',
            'hourly_pay' => 'required',
            'hours_worked' => 'required',
        ]);

        try{
            DB::beginTransaction();
            $timesheetDetail = TimesheetDetail::findOrFail($id);
            $timesheetDetail->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } finally{
            // return redirect(route('timesheetdetail.index',['timesheet_id'=>$timesheet_id]));
            return response()->json([
                "data"=>$request->all(),
            ]);
        }
    }

    public function updateDraft($timesheet_id){
        try{
            DB::beginTransaction();
            TimesheetDetail::where("timesheet_id", $timesheet_id)->update(['draft_status'=>0]);
            Timesheet::where("id",$timesheet_id)->update(['invoice_status' => 1]);
            // $timesheet_detail->update(['draft_status'=>0]);
            // $timesheetDetail = $timesheet_detail->id;
            TaskSchedular::where('timesheet_id', $timesheet_id)->update(['status'=>'Running', 'message' => 'The task is being executed']);

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } finally{
            return redirect(route('timesheetdetail.index',['timesheet_id'=>$timesheet_id]));
        }
    }

    public function destroy(Request $request ){
        // return response()->json(["data"=>$request->timesheet_detail_id]);
        if(TimesheetDetail::where('id', $request->timesheet_detail_id)->update(['is_deleted'=> 1])){
            return response()->json( [
                "message" => "Timesheet Detail of ".$request->timesheet_detail_id. " is Deleted"
            ]);
        }else{
            return response()->json( [
                "message" => "Cannot Be Deleted"
            ]);
        }

        }
}
