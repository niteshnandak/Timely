<?php

namespace App\Http\Controllers;

use App\Models\FileAudit;
use App\Models\FileData;
use App\Models\TaskSchedular;
use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\TimesheetDetail;
use Dotenv\Store\File\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader as CsvReader;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    public function showHome(Request $request)
    {

        $skip = $request->query("skip", 0);
        $take = $request->query("take", 10);
        $order = "desc";

        $user =Auth::user();
        // this function is to route to the base dashboard of the project where all the timesheets will be
        $timesheets = Timesheet::where('is_deleted', 0)->orderBy('id', $order);
        $total = count($timesheets->get()->toArray());
        $timesheets = $timesheets->limit($take)->offset($skip)->get()->toArray();
        // return view('timesheets.home',['timesheets' => $timesheets,'user' => "user->id" ]);

        $formattedTimesheets = [];
        foreach ($timesheets as $timesheet) {
        $formattedTimesheet = $timesheet;
        $formattedTimesheet['timesheet_date'] = date('d-m-Y', strtotime($timesheet['timesheet_date']));
        $formattedTimesheets[] = $formattedTimesheet;
        }

        return response()->json([
        'timesheets' => $formattedTimesheets,
        'user' => $user,
        'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        //this function is used to for storing the new master timesheet entry every created via manual

        try{
            DB::beginTransaction();
            $data = $request->validate([
                'timesheet_name' => 'required|string|max:255',
                'timesheet_date' => 'required|date',
                'created_by' => 'required',
            ]);

            $timesheet = new Timesheet();
            $timesheet->timesheet_name = $request->timesheet_name;
            $timesheet->timesheet_date = $request->timesheet_date;
            $timesheet->created_by = "0";

            $timesheet -> save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => "Validation Failed"
            ],200);
        }finally{
            // return redirect(route('timesheetdetail.index',['timesheet_id'=>$timesheet->id]));
            return response()->json([
                'timesheet_id' => $timesheet->id
            ]);
        }
    }

    public function uploadCsv(Request $request){
        //this function is used to for storing the new master timesheet entry every created via manual
        try{
            DB::beginTransaction();

            $request->validate([
                'file_upload' => 'required',
            ]);

            $data = $request->validate([
                'timesheet_name' => 'required',
                'timesheet_date' => 'required'
            ]);

            $user =Auth::user();

            $data['created_by'] = "0";
            $data['upload_type_csv'] = 1;


            $uploadedFile = $request->file('file_upload');


            $timestamp = now()->timestamp;
            $filename = $timestamp . '.' . $uploadedFile->getClientOriginalExtension();
            $fileOriginalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getClientMimeType();
            $path = $uploadedFile->storeAs('csv_files', $filename);
            $file_size = Storage::size($path);
            $file_size_kb = round($file_size / 1024,2);


            $csv = fopen(storage_path('app/'.$path), 'r');


            $header = fgetcsv($csv);


            $requiredFields = ['worker_id', 'worker_name', 'timesheet_detail_date', 'organisation', 'hourly_pay', 'hours_worked'];
            $missingFields = array_diff($requiredFields, $header);
            if (!empty($missingFields)) {
                return redirect()->back()->withErrors('The CSV file is missing required fields: ' . implode(', ', $missingFields));
            }

            $count = 0;
            while (($row = fgetcsv($csv)) !== false) {
                $csvDatas[] = array_combine($header, $row);
                $count += 1;
            }

            $currentTimesheet = Timesheet::create($data);
            $currentTimesheet->update(['is_deleted'=> 1]);


            $timesheet_id = $currentTimesheet->id;

            $file_data['timesheet_id'] = $timesheet_id;
            $file_data['file_name'] = $fileOriginalName;
            $file_data['reference_name'] = $filename;
            $file_data['file_mime_type'] = $mimeType;
            $file_data['no_of_records'] = $count;
            $file_data['file_size'] = $file_size_kb;

            $currentFile = FileAudit::create($file_data);
            $file_id =$currentFile->id;

            foreach($csvDatas as $csvData){
                $csvData['file_id'] = $file_id;
                FileData::create($csvData);
            }

            DB::commit();

        } catch(\Exception $e){

            DB::rollBack();

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json($e->getMessage(), 422);


        } finally{
            // return redirect(route('timesheet.check', ['file_id'=>$file_id, 'timesheet_id'=>$timesheet_id, 'no_of_rows'=>$count]));
            return response()->json([
                'file_id'=>$file_id,
                'csv_data'=>$csvDatas,
                'timesheet_id'=>$timesheet_id,
                'no_of_rows'=>$count
            ]);
        }
    }

    public function checkCsv($file_id, $timesheet_id, $no_of_rows){
        $csv_datas = FileData::where('file_id', $file_id)->first()->toArray();
        if(empty($csv_datas)){
            abort(404);
        }
        else{
            $csv_datas = FileData::where('file_id', $file_id)->get()->toArray();
        }
        // return view('timesheets.checkcsv', ['file_id'=>$file_id, 'files'=>$csv_datas, 'timesheet_id'=>$timesheet_id, 'no_of_rows'=>$no_of_rows]);
        return response()->json([
            'file_id'=>$file_id,
            'csv_data'=>$csv_datas,
            'timesheet_id'=>$timesheet_id,
            'no_of_rows'=>$no_of_rows
        ]);
    }

    public function storeCsv(Request $request, $timesheet_id){
        try{
            DB::beginTransaction();
            $file_id = $request->fileId;

            $currentTimesheet = Timesheet::where('id', $timesheet_id);
            $currentTimesheet->update(['is_deleted'=> 0]);
            $datas = FileData::where('file_id', $file_id)->select('worker_id','worker_name','timesheet_detail_date','organisation','hourly_pay','hours_worked')->get()->toArray();
            foreach($datas as $data){
                $data['timesheet_id'] = $timesheet_id;
                $timesheetdetail = TimesheetDetail::create($data);

                $jsonData = [
                    'timesheet_id' => $timesheetdetail->timesheet_id,
                    'timesheet_detail_id' => $timesheetdetail->id,
                    'upload_type' => 'csv'
                ];

                $data_schedular['timesheet_detail_id'] = $timesheetdetail->id;
                $data_schedular['task_id'] = 1;
                $data_schedular['status'] = "Pending";
                $data_schedular['created_by'] = '0';
                $data_schedular['updated_by'] = '0';
                $data_schedular['param'] = json_encode($jsonData);
                $data_schedular['timesheet_id'] = $timesheetdetail->timesheet_id;
                TaskSchedular::create($data_schedular);
            }
            $timesheet_detail = TimesheetDetail::where('timesheet_id', $timesheet_id);
            $timesheet = Timesheet::where("id", $timesheet_id);
            $timesheet->update(['timesheet_detail_count' => $timesheet_detail->count(), 'invoice_status' => 1]);

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

        } finally{
            // return redirect(route('timesheetdetail.index',['timesheet_id'=>$timesheet_id]));
            return response()->json([
                "file_id" => $file_id,
            ]);
        }
    }
}
