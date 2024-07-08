<?php

namespace App\Http\Controllers;

use App\Services\TimesheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

ini_set('max_execution_time', '600');

class TimesheetController extends Controller
{
    protected $timesheetService = null;

    // Displays a paginated list of timesheets for a given company
    public function showTimesheets(Request $request)
    {
        try {
            $page = (int) $request->query('page');
            $perPage = (int) $request->query('perPage');
            $org_id = Auth::user()->organisation_id;
            $company_id = $request->query('company_id');
            $order = 'desc';
            $show_timesheets = $this->getTimesheetService()->showTimesheets($page, $perPage, $company_id, $order, $org_id);
            $timesheets = $show_timesheets['timesheets'];
            $company_name = $show_timesheets['company_name'];

            return response()->json([
                'timesheets' => $timesheets,
                'company_name' => $company_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    // Displays timesheet details for a specific timesheet.
    public function showTimesheetDetails(Request $request)
    {
        try {
            $page = (int) $request->query('page');
            $perPage = (int) $request->query('perPage');
            $org_id = Auth::user()->organisation_id;
            $company_id = $request->query('company_id');
            $timesheet_id = $request->query('timesheet_id');
            $order = 'desc';
            $show_timesheetDetails = $this->getTimesheetService()->showTimesheetDetails($page, $perPage, $timesheet_id, $order, $org_id, $company_id);
            $timesheet_details = $show_timesheetDetails['timesheet_details'];
            $timesheet_name = $show_timesheetDetails['timesheet_name'];

            return response()->json([
                'timesheet_details' => $timesheet_details,
                'timesheet_name' => $timesheet_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Retrieves assignments based on the given company ID.
    public function getAssignmentsByCompanyId(Request $request)
    {
        try {
            $org_id = Auth::user()->organisation_id;
            $company_id = $request->company_id;
            $get_assignments_by_company_id = $this->getTimesheetService()->getAssignmentsByCompanyId($company_id, $org_id);
            $assignment = $get_assignments_by_company_id['assignment'];

            return response()->json([
                'assignment' => $assignment
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Retrieves the name of the person associated with a given assignment number.
    public function getPeopleNameByAssignmentNum(Request $request)
    {
        try {
            $org_id = Auth::user()->organisation_id;
            $assignment_num = $request->assignment_num;
            $get_people_name_by_assignment_num = $this->getTimesheetService()->getPeopleNameByAssignmentNum($assignment_num, $org_id);
            $people_name = $get_people_name_by_assignment_num['people_name'];
            $customer_name = $get_people_name_by_assignment_num['customer_name'];

            return response()->json([
                'people_name' => $people_name,
                'customer_name' => $customer_name
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    // Adds a new timesheet to the system.
    public function addTimesheet(Request $request)
    {
        try {
            $organisation_id = Auth::user()->organisation_id;
            $user_id = Auth::user()->user_id;
            $timesheet_data = $request->all();
            $add_timesheet = $this->getTimesheetService()->createTimesheet($timesheet_data['data'], $timesheet_data['company_id'], $organisation_id, $user_id);
            $timesheet = $add_timesheet['timesheet_data'];

            return response()->json([
                'timesheet' => $timesheet,
                'message' => 'Timesheet created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Adds a new detail to an existing timesheet.
    public function addTimesheetDetail(Request $request)
    {
        try {
            $user_id = Auth::user()->user_id;
            $org_id = Auth::user()->organisation_id;
            Log::debug($org_id);
            $timesheet_detail_data = $request->all();
            $people_name = $request->data['people_name'];
            $this->getTimesheetService()->addTimesheetDetail($timesheet_detail_data['data'], $timesheet_detail_data['timesheet_id'], $user_id, $org_id);

            return response()->json([
                'message' => 'Timesheet detail added successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Deletes a specific detail from a timesheet.
    public function deleteTimesheetDetail(Request $request)
    {
        try {
            $authUserId = Auth::user()->user_id;
            $timesheet_detail_id = $request->timesheet_detail_id;
            $delete_timesheet_detail = $this->getTimesheetService()->deleteTimesheetDetail($timesheet_detail_id, $authUserId);
            $message = $delete_timesheet_detail['message'];

            return response()->json([
                'message' => $message,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to delete timesheet detail'
            ], 400);
        }
    }

    // Deletes a specific timesheet.
    public function deleteTimesheet($timesheet_id)
    {
        try {
            $delete_timesheet = $this->getTimesheetService()->deleteTimesheet($timesheet_id);
            $message = $delete_timesheet['message'];

            return response()->json([
                'message' => $message,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to delete timesheet'
            ], 400);
        }
    }

    // Saves edits made to a specific timesheet detail
    public function timesheetEditSave(Request $request, $timesheet_detail_id)
    {
        try {
            $org_id = Auth::user()->organisation_id;
            $user_id = Auth::user()->user_id;
            $timesheet_edit_save = $this->getTimesheetService()->timesheetEditSave($request, $timesheet_detail_id, $user_id);

            return response()->json([
                'message' => $timesheet_edit_save['message']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Searches for timesheets based on provided criteria.
    public function searchTimesheet(Request $request)
    {
        try {
            $page = (int) $request->query('page');
            $perPage = (int) $request->query('perPage');
            $org_id = Auth::user()->organisation_id;
            $company_id = $request->query('company_id');
            $search_data = $request->all();
            $search_timesheet = $this->getTimesheetService()->searchTimesheet($page, $perPage, $org_id, $company_id, $search_data);
            $result = $search_timesheet['result'];
            $total = $search_timesheet['total'];

            return response()->json([
                'result' => $result,
                'total' => $total,
                'search_data ' => $search_data
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Retrieves detailed information about a specific timesheet.
    public function getTimesheetInfo($timesheet_id)
    {
        $timesheetInfo = $this->getTimesheetService()->getTimesheetInfo($timesheet_id);

        return response()->json($timesheetInfo);
    }

    // Retrieves timesheet details based on specific mapping criteria.
    public function getTimesheetMappingDetails($timesheet_id, $mapping)
    {
        $data = $this->getTimesheetService()->getTimesheetbyMapping($timesheet_id, $mapping);

        return response()->json([
            'timesheet_id' => $timesheet_id,
            'mapping' => $mapping,
            'timesheetbyMapping' => $data
        ]);
    }

    // Uploads a timesheet file and related information.
    // public function uploadTimesheet(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required',
    //         'timesheet_name' => 'required|string',
    //         'period_end_date' => 'required',
    //         'invoice_date' => 'required',
    //         'company_id' => 'required'
    //     ]);

    //     $uploadedData = [
    //         'file' => $request->file('file'),
    //         'timesheet_name' => $request->input('timesheet_name'),
    //         'period_end_date' => $request->input('period_end_date'),
    //         'invoice_date' => $request->input('invoice_date'),
    //         'company_id' => $request->input('company_id')
    //     ];

    //     $uploadResponse = $this->getTimesheetService()->uploadTimesheetRows($uploadedData);

    //     return $uploadResponse;
    // }
    public function uploadTimesheet(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,jpg,jpeg,png,pdf|max:2048',
            'timesheet_name' => 'required|string',
            'period_end_date' => 'required|date',
            'invoice_date' => 'required|date',
            'company_id' => 'required|integer'
        ]);
    
        $uploadedData = [
            'file' => $request->file('file'),
            'timesheet_name' => $request->input('timesheet_name'),
            'period_end_date' => $request->input('period_end_date'),
            'invoice_date' => $request->input('invoice_date'),
            'company_id' => $request->input('company_id')
        ];
    
        $uploadResponse = $this->getTimesheetService()->uploadTimesheetRows($uploadedData);
    
        return $uploadResponse;
    }
    


    // Unmaps a specific timesheet detail.
    public function unmapTimsheetDetail($timesheet_detail_id)
    {
        $data = $this->getTimesheetService()->unmapTD($timesheet_detail_id);
        return response()->json([
            'edited-timesheet-detail' => $data,
            'message' => 'Timesheet Detail Unmapped Succesfully'
        ]);
    }

    // Retrieves assignments for a specific worker within a company
    public function getAssignmentByWorkerId($worker_id, $company_id)
    {
        $data = $this->getTimesheetService()->getAssignmentsByWorkerId($worker_id, $company_id);
        return response()->json($data);
    }

    // Maps and updates a specific timesheet detail.
    public function mapTimesheetDetails(Request $request, $timesheet_detail_id)
    {
        $formData = $request->all();

        $data = $this->getTimesheetService()->mapTimesheetDetails($formData, $timesheet_detail_id);

        return response()->json($data);
    }


    // Proceeds a timesheet to the invoicing stage.
    public function proceedToInvoice($timesheet_id)
    {
        $data = $this->getTimesheetService()->proceedToInvoice($timesheet_id);
        $message = $data['message'];

        return response()->json([
            'data' => $data,
            'message' => $message
        ]);
    }



    // Retrieves the TimesheetService instance.
    private function getTimesheetService()
    {
        if ($this->timesheetService == null) {
            $this->timesheetService = new TimesheetService();
        }
        return $this->timesheetService;
    }
}
