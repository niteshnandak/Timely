<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Timesheet;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TimesheetDetail;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser; 

class TimesheetService
{
    /**
     * To view the timesheets
     * input - required params for pagination, company_id, order(desc / asc)
     * return - timesheets, total number of timesheets, company_name
     * return type - array containing above details
     */
    public function showTimesheets($page, $perPage, $company_id, $order, $org_id)
    {
        try {
            $timesheets = Timesheet::where([
                                'organisation_id' => $org_id,
                                'company_id' => $company_id,
                                'is_deleted' => 0
                            ])
                            ->orderBy('timesheet_id', $order)
                            ->paginate($perPage, ['*'], 'page', $page);

            $company = Company::where('company_id', $company_id)->first();
            $company_name = $company->company_name;

            return ['timesheets' => $timesheets, 'company_name' => $company_name];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * To view the Timesheet details
     * input - params required for pagination, timesheet_id, order of the data
     * return - array of data containing the timesheet details for that timesheet_id
     *
     */
    public function showTimesheetDetails($page, $perPage, $timesheet_id, $order, $org_id, $company_id)
    {
        try {
            $timesheet_details = Timesheet::join('timesheet_detail', 'timesheet.timesheet_id', '=', 'timesheet_detail.timesheet_id')
                                    ->join('assignment', 'timesheet_detail.assignment_num', '=', 'assignment.assignment_num')
                                    ->join('customer', 'assignment.customer_id', '=', 'customer.customer_id')
                                    ->where([
                                        'timesheet.organisation_id' => $org_id,
                                        'timesheet.company_id' => $company_id,
                                        'timesheet.timesheet_id' => $timesheet_id,
                                        'timesheet_detail.is_deleted' => 0,
                                    ])
                                    ->orderBy('timesheet_detail_id', $order)
                                    ->paginate($perPage, [
                                        'timesheet_detail.timesheet_detail_id', 
                                        'timesheet_detail.assignment_num', 
                                        'timesheet_detail.people_name', 
                                        'timesheet_detail.quantity', 
                                        'timesheet_detail.unit_price', 
                                        'timesheet_detail.description',
                                        'customer.customer_name'
                                    ], 'page', $page);

            // $timesheet_details = Timesheet::join('timesheet_detail', 'timesheet.timesheet_id', '=', 'timesheet_detail.timesheet_id')
            //                         ->where([
            //                             'timesheet.organisation_id' => $org_id,
            //                             'timesheet.company_id' => $company_id,
            //                             'timesheet.timesheet_id' => $timesheet_id,
            //                             'timesheet_detail.is_deleted' => 0,
            //                         ])
            //                         ->orderBy('timesheet_detail_id', $order)
            //                         ->paginate($perPage, ['timesheet_detail.timesheet_detail_id', 'timesheet_detail.assignment_num', 'timesheet_detail.people_name', 'timesheet_detail.quantity', 'timesheet_detail.unit_price', 'timesheet_detail.description'], 'page', $page);

            $timesheet_name = Timesheet::where('timesheet_id', $timesheet_id)
                                ->where('is_deleted', 0)
                                ->get(['timesheet_name']);

                                Log::debug($timesheet_details);
            return ['timesheet_details' => $timesheet_details,'timesheet_name' => $timesheet_name];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Fetch all the assignments for that company_id
     * input - company_id
     * return - Assignments under that company as an array
     */
    public function getAssignmentsByCompanyId($company_id, $org_id)
    {
        try {
            $assignment = Assignment::join('people', 'assignment.people_id', '=', 'people.people_id')
                            ->where([
                                'assignment.company_id' => $company_id,
                                'assignment.organisation_id' => $org_id,
                                'assignment.is_deleted' => 0,
                                'assignment.status' => 'Ongoing'
                            ])
                            ->get(['assignment.assignment_id', 'assignment.assignment_num', 'people.people_name']);

            return ['assignment' => $assignment];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Fetch the people name for that assignment to display it when assignment is selected
     * input - assignment number
     * return - the people name as an array
     */
    public function getPeopleNameByAssignmentNum($assignment_num, $org_id)
    {
        try {
            $people_name = Assignment::join('people', 'assignment.people_id', '=', 'people.people_id')
                ->where([
                    'assignment.organisation_id' => $org_id,
                    'assignment.assignment_num' => $assignment_num,
                    'assignment.is_deleted' => 0,
                ])
                ->first(['people.people_name']);
            
            $customer_name = Assignment::join('customer', 'assignment.customer_id', '=', 'customer.customer_id')
                ->where([
                    'assignment.organisation_id' => $org_id,
                    'assignment.assignment_num' => $assignment_num,
                    'assignment.is_deleted' => 0,
                ])
                ->first(['customer.customer_name']);

            return ['people_name' => $people_name, 'customer_name' => $customer_name];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create a new timesheet under that company
     * input - $timesheet data, company_id
     * return - created timesheet_data
     *
     */
    public function createTimesheet($timesheet, $company_id, $organisation_id, $user_id)
    {
        $timesheet_data = Timesheet::create([
            'company_id' => $company_id,
            'organisation_id' => $organisation_id,
            'timesheet_name' => $timesheet['timesheet_name'],
            'num_of_rows' => '0',
            'invoice_status' => 'Pending',
            'invoice_date' => $timesheet['invoice_date'],
            'period_end_date' => $timesheet['period_end_date'],
            'upload_type' => 'Manual',
            'updated_by' => $user_id,
            'created_by' => $user_id
        ]);

        return ['timesheet_data' => $timesheet_data];
    }

    /**
     * add a new timesheet detail for that timesheet
     * input - timesheet detail, timesheet i, user id, people name
     * return - created timesheet detail as an array
     */
    public function addTimesheetDetail($timesheet_detail, $timesheet_id, $user_id, $org_id)
    {
        $people_id = Assignment::join('people', 'assignment.people_id', '=', 'people.people_id')
                        ->where([
                            'assignment.is_deleted' => 0,
                        ])
                        ->get(['assignment.people_id'])->first();

        $timesheet_detail_data = TimesheetDetail::create([
            'timesheet_id' => $timesheet_id,
            'assignment_num' => $timesheet_detail['assignment_num'],
            'people_id' => $people_id['people_id'],
            'people_name' => $timesheet_detail['people_name'],
            'quantity' => $timesheet_detail['quantity'],
            'unit_price' => $timesheet_detail['unit_price'],
            'description' => $timesheet_detail['description'],
            'mapping_status' => 'Mapped',
            'updated_by' => $user_id,
            'created_by' => $user_id
        ]);

        // Update num_of_rows in the Timesheet table
        $numOfRows = TimesheetDetail::where('timesheet_id', $timesheet_id)
                        ->where('is_deleted', 0)
                        ->count();

        $timesheet = Timesheet::where([
                        'organisation_id' =>  $org_id,
                        'timesheet_id' =>  $timesheet_id
                        ])
                        ->update(['num_of_rows'=> $numOfRows]);
                        // Log::debug($timesheet);

        return ['timesheet_detail_data' => $timesheet_detail_data];
    }

    /**
     * Delete a timesheetDetail
     * input - timesheet detail id
     * return - a message if the timesheet delete was succesful or not
     */
    public function deleteTimesheetDetail($timesheet_detail_id, $authUserId)
    {
        try {
            $td = TimesheetDetail::where('timesheet_detail_id', $timesheet_detail_id)->get();

            $timesheet_id = $td[0]['timesheet_id'];

            $timesheet_detail = TimesheetDetail::where('timesheet_detail_id', $timesheet_detail_id)
                                    ->update(['is_deleted' => 1]);
            // $timesheet_detail->is_deleted = 1;
            // $timesheet_detail->save();

            $tdCount = TimesheetDetail::where('timesheet_id', $timesheet_id)
                        ->where('is_deleted', 0)
                        ->get()->count();

            Timesheet::where('timesheet_id', $timesheet_id)
                ->update([
                    'num_of_rows' => $tdCount,
                    'updated_by' => $authUserId,
                    'updated_at' => now()
                ]);

            return ['message' => 'Timesheet Detail Deleted Succesfully'];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a timsheet if the status is pending
     * input - timesheet id
     * return - message that says if the timesheet was deleted or not
     */
    public function deleteTimesheet($timesheet_id)
    {
        try {
            $timesheet = Timesheet::where('timesheet_id', $timesheet_id)->first();

            if ($timesheet['invoice_status'] !== 'Pending') {
                return ['message' => 'Timesheet already sent for invoicing'];
            }

            TimesheetDetail::where('timesheet_id', $timesheet_id)->update(['is_deleted' => 1]);
            Timesheet::where('timesheet_id', $timesheet_id)->update(['is_deleted' => 1]);

            return ['message' => 'Timesheet Deleted Succesfully'];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * save the edited timesheet
     * input - request containing all the data
     * return - message that says if the assignment was deleted succesfully as an array
     */
    public function timesheetEditSave($request, $timesheet_detail_id, $user_id)
    {
        try {
            $people = Assignment::join('people', 'assignment.people_id', '=', 'people.people_id')
                        ->where([
                            'assignment.is_deleted' => 0,
                        ])
                        ->first(['assignment.people_id']);

            if (!$people) {
                return response()->json(['message' => 'Person not found'], 404);
            }

            $people_id = $people->people_id;

            $updated = TimesheetDetail::where(['timesheet_detail_id' => $timesheet_detail_id])
            ->update([
                'assignment_num' => $request->input('assignment_num'),
                'people_id' => $people_id,
                'people_name' => $request->input('people_name'),
                'quantity' => $request->input('quantity'),
                'unit_price' => $request->input('unit_price'),
                'description' => $request->input('description'),
                'is_deleted' => 0,
                'updated_at' => now(),
                'updated_by' => $user_id,
            ]);
            if ($updated) {
                return ['message' => "Assignment Edited Successfully", 'updated_data' => $updated];
            } else {
                return ['message' => "Failed to edit assignment"];
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function searchTimesheet($page, $perPage, $org_id, $company_id, $search_data)
    {
        try {
            $query = Timesheet::where([
                        'organisation_id' => $org_id,
                        'company_id'=> $company_id, 
                        'is_deleted'=> 0 
                    ]);

            if (isset($search_data['timesheet_num']) && !empty($search_data['timesheet_num'])) {
                $query->where('timesheet_num', 'like', '%' . $search_data['timesheet_num'] . '%');
            }

            if (isset($search_data['timesheet_name']) && !empty($search_data['timesheet_name'])) {
                $query->where('timesheet_name', 'like', '%' . $search_data['timesheet_name'] . '%');
            }

            if (isset($search_data['period_end_date']) && !empty($search_data['period_end_date'])) {
                $query->where('period_end_date', 'like', '%' . $search_data['period_end_date'] . '%');
            }

            if (isset($search_data['upload_type']) && !empty($search_data['upload_type'])) {
                $query->where('upload_type', '=', $search_data['upload_type']);
            }

            if (isset($search_data['invoice_status']) && !empty($search_data['invoice_status'])) {
                $query->where('invoice_status', '=', $search_data['invoice_status']);
            }

            $result = $query->paginate($perPage, ['*'], 'page', $page);
            $total = $query->count();

            return ['result' => $result, 'total' => $total];
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload CSV timesheet and
     * Update the data to Database
     * input - upload csv form data
     */
    // public function uploadTimesheetRows($data)
    // {
    //     try {
    //         $authUser = Auth::user();
    //         $authUserId = $authUser->user_id;

    //         //Details of the timesheet to be created
    //         $timesheet['organisation_id'] = $authUser->organisation_id;
    //         $timesheet['timesheet_name'] = $data['timesheet_name'];
    //         $timesheet['period_end_date'] = $data['period_end_date'];
    //         $timesheet['invoice_date'] = $data['invoice_date'];
    //         $timesheet['company_id'] = $data['company_id'];
    //         $timesheet['invoice_status'] = 'Pending';
    //         $timesheet['upload_type'] = 'CSV';
    //         $timesheet['is_deleted'] = 0;
    //         $timesheet['created_by'] = $authUserId;
    //         $timesheet['updated_by'] = $authUserId;
    //         $timesheet['updated_at'] = now();

    //         // Save the Uploaded timesheet in storage/app/public
    //         $fileDetails = $this->saveCsvTimesheet($data['file']);

    //         // Check if file exists in the path, if exists then read the header
    //         if (Storage::disk('public')->exists($fileDetails)) {
    //             $filePath = storage_path('app/public/' . $fileDetails);
    //             $file = fopen($filePath, 'r');

    //             $heading = fgetcsv($file);
    //         } else {
    //             $file = "File Not Found";
    //             $heading = [];
    //         }

    //         // Check the heading of the CSV for required fields
    //         $checkedCSV = $this->checkCSVHeaders($heading);
    //         $fields = ['people_id', 'people_name', 'quantity', 'unit_price', 'description'];
    //         $noOfRequiredFields = count($fields);

    //         if (empty($checkedCSV['missing'])) {

    //             $csvData = Storage::disk('public')->get($fileDetails);
    //             $lines = explode("\r\n", $csvData);
    //             $csvWithHeader = array_map('str_getcsv', $lines);

    //             // Remove the Header line for the data lines
    //             $dataLines = array_slice($csvWithHeader, 1);
    //             $timesheet['num_of_rows'] = 0;
    //             $details = [];
    //             $missing_details = [];

    //             //Loop through uploaded Lines and set unmapped status using checkMultipleAssignments()
    //             foreach ($dataLines as $dataLine) {
    //                 if ($dataLine[0] != '' && $dataLine[1] != '') {
    //                     for ($i = 0; $i < $noOfRequiredFields; $i++) {
    //                         $timesheetDetail[$fields[$i]] = $dataLine[$i];
    //                     }
    //                     $assignmentAndStatus = $this->checkMultipleAssignments($timesheetDetail[$fields[0]]);
    //                     $timesheetDetail['mapping_status'] = $assignmentAndStatus['status'];
    //                     $timesheetDetail['assignment_num'] = $assignmentAndStatus['assignment_num'];
    //                     $timesheetDetail['is_deleted'] = 0;
    //                     $timesheetDetail['created_by'] = $authUserId;
    //                     $timesheetDetail['updated_by'] = $authUserId;
    //                     $timesheetDetail['updated_at'] = now();

    //                     // number of rows for timesheet
    //                     $timesheet['num_of_rows'] += 1;
    //                     array_push($details, $timesheetDetail);
    //                 } else {
    //                     //Store the datalines that have missing details
    //                     array_push($missing_details, $dataLine);
    //                 }
    //             }

    //             /**
    //              * Create timesheet for Uploaded file
    //              * then Create Timesheet details for the Created Timesheet
    //              */
    //             $timesheetCreated = Timesheet::create($timesheet);
    //             foreach ($details as $detail) {
    //                 $detail['timesheet_id'] = $timesheetCreated->timesheet_id;
    //                 TimesheetDetail::create($detail);
    //             }
    //         } else {
    //             return response()->json(['message' => 'CSV file does not contain all fields']);
    //         }

    //         return response()->json([
    //             'message' => 'Timesheet Uploaded Succesfully',
    //             'timesheet_id' => $timesheetCreated->timesheet_id,
    //             'timesheet' => $timesheet,
    //             'timesheetDetails' => $details,
    //             'missing_fields' => $missing_details
    //         ]);
    //     } catch (\Error $e) {

    //         Log::debug("Error in timesheetService/uploadTimesheetRows", $e->getMessage());
    //         return response()->json(['Error' => 'Error occured while uploading CSV']);
    //     }
    // }

//     public function uploadTimesheetRows($data)
// {
//     try {
//         $authUser = Auth::user();
//         $authUserId = $authUser->user_id;

//         $timesheet = [
//             'organisation_id' => $authUser->organisation_id,
//             'timesheet_name' => $data['timesheet_name'],
//             'period_end_date' => $data['period_end_date'],
//             'invoice_date' => $data['invoice_date'],
//             'company_id' => $data['company_id'],
//             'invoice_status' => 'Pending',
//             'upload_type' => 'CSV', // Default to CSV
//             'is_deleted' => 0,
//             'created_by' => $authUserId,
//             'updated_by' => $authUserId,
//             'updated_at' => now()
//         ];

//         $fileDetails = $this->saveFile($data['file']);
//         $filePath = storage_path('app/public/' . $fileDetails);
//         $extension = $data['file']->getClientOriginalExtension();
//         $csvData = '';

//         $fields = ['people_id', 'people_name', 'quantity', 'unit_price', 'description'];
//         $noOfRequiredFields = count($fields);

//         if ($extension === 'csv') {
//             if (Storage::disk('public')->exists($fileDetails)) {
//                 $file = fopen($filePath, 'r');
//                 $heading = fgetcsv($file);
//             } else {
//                 $file = "File Not Found";
//                 $heading = [];
//             }

//             $checkedCSV = $this->checkCSVHeaders($heading);

//             if (empty($checkedCSV['missing'])) {
//                 $csvData = Storage::disk('public')->get($fileDetails);
//                 $lines = explode("\r\n", $csvData);
//                 $csvWithHeader = array_map('str_getcsv', $lines);
//                 $dataLines = array_slice($csvWithHeader, 1);
//             } else {
//                 return response()->json(['message' => 'CSV file does not contain all fields']);
//             }
//         } elseif ($extension === 'pdf') {
//             $csvData = $this->extractTextFromPDF($filePath);
//             $dataLines = $this->parseExtractedText($csvData);
//         } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
//             $csvData = $this->extractTextFromImage($filePath);
//             $dataLines = $this->parseExtractedText($csvData);
//         }

//         Log::info("Processed CSV Data: " . $csvData);

//         $timesheet['num_of_rows'] = 0;
//         $details = [];
//         $missing_details = [];

//         foreach ($dataLines as $dataLine) {
//             if ($dataLine) {
//                 // Check if $dataLine is an array and convert it to a string
//                 if (is_array($dataLine)) {
//                     $dataLine = implode(",", $dataLine);
//                 }
//                 $dataFields = array_map('trim', str_getcsv($dataLine));
//                 Log::info("Processing Line: " . json_encode($dataFields));

//                 if (count($dataFields) >= $noOfRequiredFields) {
//                     $timesheetDetail = [];
//                     for ($i = 0; $i < $noOfRequiredFields; $i++) {
//                         $timesheetDetail[$fields[$i]] = isset($dataFields[$i]) ? $dataFields[$i] : null;
//                     }
//                     $assignmentAndStatus = $this->checkMultipleAssignments($timesheetDetail[$fields[0]]);
//                     $timesheetDetail['mapping_status'] = $assignmentAndStatus['status'];
//                     $timesheetDetail['assignment_num'] = $assignmentAndStatus['assignment_num'];
//                     $timesheetDetail['is_deleted'] = 0;
//                     $timesheetDetail['created_by'] = $authUserId;
//                     $timesheetDetail['updated_by'] = $authUserId;
//                     $timesheetDetail['updated_at'] = now();

//                     $timesheet['num_of_rows'] += 1;
//                     array_push($details, $timesheetDetail);
//                 } else {
//                     array_push($missing_details, $dataFields);
//                 }
//             }
//         }

//         $timesheetCreated = Timesheet::create($timesheet);
//         Log::info("Timesheet Created: " . json_encode($timesheetCreated));

//         foreach ($details as $detail) {
//             $detail['timesheet_id'] = $timesheetCreated->timesheet_id;

//             try {
//                 $createdDetail = TimesheetDetail::create($detail);
//                 Log::info('Successfully inserted timesheet detail', ['detail' => $createdDetail]);
//             } catch (\Exception $e) {
//                 Log::error('Error inserting timesheet detail', ['detail' => $detail, 'error' => $e->getMessage()]);
//             }
//         }

//         return response()->json([
//             'message' => 'Timesheet Uploaded Successfully',
//             'timesheet_id' => $timesheetCreated->timesheet_id,
//             'timesheet' => $timesheet,
//             'timesheetDetails' => $details,
//             'missing_fields' => $missing_details
//         ]);
//     } catch (\Exception $e) {
//         Log::error("Error in timesheetService/uploadTimesheetRows: " . $e->getMessage());
//         return response()->json(['Error' => 'Error occurred while uploading file']);
//     }
// }


public function uploadTimesheetRows($data)
{
    try {
        $authUser = Auth::user();
        $authUserId = $authUser->user_id;

        $timesheet = [
            'organisation_id' => $authUser->organisation_id,
            'timesheet_name' => $data['timesheet_name'],
            'period_end_date' => $data['period_end_date'],
            'invoice_date' => $data['invoice_date'],
            'company_id' => $data['company_id'],
            'invoice_status' => 'Pending',
            'upload_type' => 'CSV', // Default to CSV
            'is_deleted' => 0,
            'created_by' => $authUserId,
            'updated_by' => $authUserId,
            'updated_at' => now()
        ];

        $fileDetails = $this->saveFile($data['file']);
        $filePath = storage_path('app/public/' . $fileDetails);
        $extension = $data['file']->getClientOriginalExtension();
        $csvData = '';

        $fields = ['people_id', 'people_name', 'quantity', 'unit_price', 'description'];
        $noOfRequiredFields = count($fields);

        if ($extension === 'csv') {
            if (Storage::disk('public')->exists($fileDetails)) {
                $file = fopen($filePath, 'r');
                $heading = fgetcsv($file);
            } else {
                return response()->json(['message' => 'File Not Found']);
            }

            $checkedCSV = $this->checkCSVHeaders($heading);

            if (empty($checkedCSV['missing'])) {
                $csvData = Storage::disk('public')->get($fileDetails);
                $lines = explode("\r\n", $csvData);
                $csvWithHeader = array_map('str_getcsv', $lines);
                $dataLines = array_slice($csvWithHeader, 1);
            } else {
                return response()->json(['message' => 'CSV file does not contain all fields']);
            }
        } elseif ($extension === 'pdf') {
            $csvData = $this->extractTextFromPDF($filePath);
            $dataLines = $this->parseExtractedText($csvData);
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $csvData = $this->extractTextFromImage($filePath);
            $dataLines = $this->parseExtractedText($csvData);
        }

        Log::info("Processed CSV Data: " . $csvData);

        $timesheet['num_of_rows'] = 0;
        $details = [];
        $missing_details = [];

        foreach ($dataLines as $dataLine) {
            if ($dataLine) {
                if (is_array($dataLine)) {
                    $dataLine = implode(",", $dataLine);
                }
                $dataFields = array_map('trim', str_getcsv($dataLine));
                Log::info("Processing Line: " . json_encode($dataFields));

                if (count($dataFields) >= $noOfRequiredFields) {
                    $timesheetDetail = [];
                    for ($i = 0; $i < $noOfRequiredFields; $i++) {
                        $timesheetDetail[$fields[$i]] = $dataFields[$i];
                    }
                    $assignmentAndStatus = $this->checkMultipleAssignments($timesheetDetail['people_id']);
                    $timesheetDetail['mapping_status'] = $assignmentAndStatus['status'];
                    $timesheetDetail['assignment_num'] = $assignmentAndStatus['assignment_num'];
                    $timesheetDetail['is_deleted'] = 0;
                    $timesheetDetail['created_by'] = $authUserId;
                    $timesheetDetail['updated_by'] = $authUserId;
                    $timesheetDetail['updated_at'] = now();

                    $timesheet['num_of_rows'] += 1;
                    array_push($details, $timesheetDetail);
                } else {
                    array_push($missing_details, $dataFields);
                }
            }
        }

        $timesheetCreated = Timesheet::create($timesheet);
        Log::info("Timesheet Created: " . json_encode($timesheetCreated));

        foreach ($details as $detail) {
            $detail['timesheet_id'] = $timesheetCreated->timesheet_id;

            try {
                $createdDetail = TimesheetDetail::create($detail);
                Log::info('Successfully inserted timesheet detail', ['detail' => $createdDetail]);
            } catch (\Exception $e) {
                Log::error('Error inserting timesheet detail', ['detail' => $detail, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message' => 'Timesheet Uploaded Successfully',
            'timesheet_id' => $timesheetCreated->timesheet_id,
            'timesheet' => $timesheet,
            'timesheetDetails' => $details,
            'missing_fields' => $missing_details
        ]);
    } catch (\Exception $e) {
        Log::error("Error in timesheetService/uploadTimesheetRows: " . $e->getMessage());
        return response()->json(['Error' => 'Error occurred while uploading file']);
    }
}



private function parseExtractedText($text)
{
    $lines = explode("\n", $text);
    $parsedLines = [];

    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos($line, 'Worker ID') !== false) {
            continue;
        }

        // Use regex to split the line, allowing for multi-word names
        preg_match('/^(\d+)(.*?)\s+(\d+)\s+(\d+)\s+(.+)$/', $line, $matches);

        if (count($matches) == 6) {
            $parsedLines[] = [
                $matches[1],            // people_id
                trim($matches[2]),      // people_name (trimmed to remove extra spaces)
                $matches[3],            // quantity
                $matches[4],            // unit_price
                $matches[5]             // description
            ];
        } else {
            // Log lines that don't match the expected format
            Log::warning("Unable to parse line: " . $line);
        }
    }

    return $parsedLines;
}






public function extractTextFromPdf($pdfFilePath)
{
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($pdfFilePath);
    $text = $pdf->getText();
    // Return the text as a string
    return $text;
}
    
    
    
    


private function extractTextFromImage($filePath)
{
    $text = (new TesseractOCR($filePath))->run();
    return $text;
}

private function saveFile($file)
{
    $fileName = $file->getClientOriginalName();
    $path = $file->storeAs('files', $fileName, 'public');
    return $path;
}

    //Function to check multiple assignments for that worker exists or not
    /**
     * input - workerID
     * returns - status and assignment_num for that worker
     * return type - array []
     */
    private function checkMultipleAssignments($workerId)
    {

        // If the worker has multiple assignments, set the status as unmapped and
        $workerAssignments = Assignment::select('assignment_num', 'people_id')
            ->where('people_id', $workerId)
            ->where('is_deleted', 0)
            ->get();

        if (count($workerAssignments) == 1) {
            $assignment = $workerAssignments->first();
            $responseData['status'] = 'Mapped';
            $responseData['assignment_num'] = $assignment->assignment_num;
        } else {
            $responseData['status'] = 'Unmapped';
            $responseData['assignment_num'] = '';
        }
        return $responseData;
    }

    // Checking CSV data's header row columns
    /**
     * Header of CSV for input
     * returns - missing columns (if any)
     * return type - array []
     */
    private function checkCSVHeaders($header)
    {
        $requiredFields = ['Worker ID', 'Worker Name', 'Quantity', 'Unit Price', 'Description'];

        $missing = array_diff($requiredFields, $header);

        if (empty($missing)) {
            return [
                'missing' => [],
                'requiredFields' => $requiredFields
            ];
        } else {
            return [
                'missing' => implode(',', $missing),
                'requiredFields' => $requiredFields
            ];
        }
    }


    // Function to Save timesheet in storage
    /**
     * input - uploaded CSV file
     * return - path where csv is stored
     * return type - string ""
     */


    // To get inital timesheet details to be shown on the page
    /**
     * input - timesheet_id $id
     * return - timesheet information as an object
     */

    public function getTimesheetInfo($id)
    {

        $timesheetInfo = Timesheet::where('timesheet_id', $id)->first();

        return $timesheetInfo;
    }


    // Fetch timsheets by mapping status
    /**
     * input - timesheed_id, mapping
     * return - timesheet-details of the timesheet_id as an array
     */
    public function getTimesheetbyMapping($id, $mapping)
    {

        $timesheetByMapping = DB::table('timesheet as t')
            ->join('timesheet_detail as td', 't.timesheet_id', '=', 'td.timesheet_id')
            ->where('t.upload_type', 'csv')
            ->where('td.timesheet_id', $id)
            ->where('td.mapping_status', $mapping)
            ->where('t.is_deleted', 0)
            ->where('td.is_deleted', 0)
            ->get();

        return $timesheetByMapping;
    }

    /**
     * Function to Unmap Timesheet Detail
     * input - timesheet detail id - int
     * return updated TimesheetDetailRow
     * return-type - Collection
     */
    public function unmapTD($timesheet_detail_id)
    {
        $response = TimesheetDetail::where('timesheet_detail_id', $timesheet_detail_id)
            ->update(['mapping_status' => 'Unmapped']);

        return $response;
    }

    //Fetch assignments by Worker ID
    public function getAssignmentsByWorkerId($worker_id, $company_id)
    {
        $assignment = Assignment::where('assignment.people_id', $worker_id)
            ->join('people', 'assignment.people_id', '=', 'people.people_id')
            ->where('assignment.is_deleted', 0)
            ->get(['assignment.assignment_num', 'people.people_name']);

        return $assignment;
    }


    /**
     * Maps timesheet details and updates the corresponding record in the database.
     *
     * input - array $formData,  int $timesheet_detail_id
     * return array An array containing the result of the update operation and a success message.
     * return-type array
     */
    public function mapTimesheetDetails($formData, $timesheet_detail_id)
    {

        try {
            $authUser = Auth::user();
            $authUserId = $authUser->user_id;

            $timesheetDetail = TimesheetDetail::where('timesheet_detail_id', $timesheet_detail_id)->update([
                'quantity' => $formData['quantity'],
                'unit_price' => $formData['unit_price'],
                'assignment_num' => $formData['assignment_num'],
                'mapping_status' => 'Mapped',
                'updated_at' => now(),
                'updated_by' => $authUserId
            ]);

            return [$timesheetDetail, 'message' => 'Timesheet Detail Mapped Succesfully'];
        } catch (\Error $e) {
            Log::error("error at maptimesheet", $e->getMessage());
            return response()->json(['Error' => 'Error occured while mapping this timesheet detail']);
        }
    }

    /**
     * Proceed the timesheet to Invoicing
     *
     * input - timesheet ID
     * return - array containing message that says if invoice was succesfully processedor not
     */
    public function proceedToInvoice($timesheet_id)
    {
        $authUser = Auth::user();
        $authUserId = $authUser->user_id;

        $timesheet = Timesheet::where('timesheet_id', $timesheet_id)->get()->first();

        $timesheetDetails = TimesheetDetail::where('timesheet_id', $timesheet_id)->get();

        if ($timesheet['upload_type'] == 'manual') {

            Timesheet::where('timesheet_id', $timesheet_id)->update([
                'invoice_status' => 'In Progress',
                'updated_at' => now(),
                'updated_by' => $authUserId
            ]);
            return ['message' => 'Timesheet Sent to Invoicing Succesfully'];
        } else {
            $td = TimesheetDetail::where('timesheet_id', $timesheet_id)
                ->where('is_deleted', 0);

            $td->where('mapping_status', 'Unmapped')
                ->update([
                    'is_deleted' => 1,
                    'updated_by' => $authUserId,
                    'updated_at' => now()
                ]);

            $tdCount = TimesheetDetail::where('timesheet_id', $timesheet_id)
                ->where('is_deleted', 0)->count();

            Timesheet::where('timesheet_id', $timesheet_id)
                ->update([
                    'num_of_rows' => $tdCount,
                    'invoice_status' => 'In Progress',
                    'updated_by' => $authUserId,
                    'updated_at' => now()
                ]);

            return ['message' => 'Timesheet Sent for Invoicing Succesfully'];
        }
    }
}
