<?php

namespace App\Http\Controllers;
use App\Services\PeopleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeopleController extends Controller
{
    protected $peopleService;

    public function __construct(PeopleService $peopleService)
    {
        $this->peopleService = $peopleService;
    }


    public function peopleCreate(Request $request)
    {


        $request->validate([
             'addPeopleData.peopleDetails.people_name' => 'required',
            'addPeopleData.peopleDetails.job_title' => 'required',
            'addPeopleData.peopleDetails.birth_date' => 'required',
            'addPeopleData.peopleDetails.email_address' => 'required',
            'addPeopleData.peopleDetails.gender' => 'required',
            'addPeopleData.peopleDetails.phone_number' => 'required',

            'addPeopleData.peopleAddressDetails.address_line_1' => 'required',
            'addPeopleData.peopleAddressDetails.city' => 'required',
            'addPeopleData.peopleAddressDetails.state' => 'required',
            'addPeopleData.peopleAddressDetails.country' => 'required',
            'addPeopleData.peopleAddressDetails.pincode' => 'required',
        ]);

        $people_data = $request->addPeopleData;

        // $user = $request->user;

        try {
            DB::beginTransaction(); // Start transaction

            $addpeople = $this->getPeopleService()->createPeople($people_data);
            if ($addpeople) {
                DB::commit(); // Commit transaction if successful

                return response()->json([
                    "toaster_success" => Config::get('message.success.people_add_success')
                ]);
            } else {
                DB::rollBack(); // Rollback transaction if failed
                return response()->json([
                    "toaster_error" => Config::get('message.error.people_add_error')
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction if an exception occurs
            return response()->json([
                "toaster_error" => Config::get('message.error.people_add_error'),
                "message" => $e->getMessage()
            ]);
        }
    }
    public function peopleEdit(Request $request)
    {

        $people_id = $request->people_id;

        try {
            $people = $this->getPeopleService()->getPeopleData($people_id);
            $people_details = $people["people_details"];
            $people_employment_details = $people["people_employment_details"];
            $people_address_details = $people["people_address_details"];
            $people_bank_details = $people["people_bank_details"];
            return response()->json(
                [
                    "people_data" => $people_details,
                    'people_employment_details' => $people_employment_details,
                    "people_address_details" => $people_address_details,
                    "people_bank_details" => $people_bank_details,
                    "toaster_success" => Config::get('message.success.people_editPeople_success')
                ]
            );
        } catch (Exception $e) {
            return response()->json([
                "toaster_error" => Config::get('message.error.people_editPeople_error'),
                "message"=> $e->getMessage()
            ]);
        }


    }

    public function peopleEditSave(Request $request)
    {

        $request->validate([
            'people_data.peopleDetails.people_name' => 'required',
            'people_data.peopleDetails.job_title' => 'required',
            'people_data.peopleDetails.birth_date' => 'required',
            'people_data.peopleDetails.email_address' => 'required',
            'people_data.peopleDetails.gender' => 'required',
            'people_data.peopleDetails.phone_number' => 'required',

            'people_data.peopleAddressDetails.address_line_1' => 'required',
            'people_data.peopleAddressDetails.city' => 'required',
            'people_data.peopleAddressDetails.state' => 'required',
            'people_data.peopleAddressDetails.country' => 'required',
            'people_data.peopleAddressDetails.pincode' => 'required',

            'people_data.peopleEmploymentDetails.joining_date' => 'required',
            'people_data.peopleEmploymentDetails.pay_frequency' => 'required',

            'people_data.peopleBankDetails.bank_name' => 'required',
            'people_data.peopleBankDetails.account_number' => 'required',
            'people_data.peopleBankDetails.bank_branch' => 'required',
            'people_data.peopleBankDetails.bank_ifsc_code' => 'required',


        ]);

        $people_id = $request->people_id;

        $people_data = $request->people_data;

        try {
            DB::beginTransaction(); // Start transaction

            if ($this->getPeopleService()->editPeopleData($people_id, $people_data)) {
                DB::commit(); // Commit transaction if successful

                return response()->json([
                    "toaster_success" => Config::get('message.success.people_edit_success')
                ]);
            } else {
                DB::rollBack(); // Rollback transaction if failed

                return response()->json([
                    "toaster_error" => Config::get('message.error.people_edit_error')
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction if an exception occurs

            return response()->json([
                "toaster_error" => Config::get('message.error.people_edit_error'),
                "message" => $e->getMessage()
            ]);
        }
    }

    public function peopleDelete($id)
    {
        try {
            DB::beginTransaction(); // Start transaction

            $people_data = $this->getPeopleService()->deletePoeple($id);
            $user = Auth::user();

            if ($people_data) {
                DB::commit(); // Commit transaction if successful

                return response()->json([
                    "toaster_success" => Config::get('message.success.people_delete_success'),
                    "id"=>$id,
                    "user"=>$user
                ]);
            } else {
                DB::rollBack(); // Rollback transaction if failed

                return response()->json([
                    "toaster_error" => Config::get('message.error.people_delete_error')
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction if an exception occurs

            return response()->json([
                "toaster_error" => Config::get('message.error.people_delete_error'),
                "message" => $e->getMessage()
            ]);
        }
    }

    public function peopleGridShow(Request $request)
    {
        $skip = $request->query('skip', 0);
        $take = $request->query('take', 10);
        $order = 'desc';
        $search = $request->searchData && $request->searchData['peopleSearchDetails'] ?$request->searchData['peopleSearchDetails'] : null;

        $people_data = $this->getPeopleService()->gridPeopleData($skip,$take,$order, $search);

        try {
            return response()->json([
                "people_data" => $people_data[0],
                "total" => $people_data[1]
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getAllCompanies()
    {
        try {
            $companies =  $this->getPeopleService()->getCompanies();
            return response()->json([
                "companies" => $companies
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getPeopleStatusCard(){

        $statusData =  $this->getPeopleService()->getCardInfo();
        return response()->json([
            "status_data"=>$statusData,

        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|int'
            ]);

            $status = $request->status;
            $result = $this->peopleService->updateStatus($id, $status);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error updating status: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the status'], 500);
        }
    }


    public function getPeopleAssignments(Request $request){
        try {
            Log::debug($request);
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);
            $people_id=$request->route('peopleId');
            Log::debug($people_id);


            $data = $this->peopleService->fetchAssignmentsData($skip, $take,$people_id, $request);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Failed to fetch the Assignments data."
            ], 404);
        }

    }


    public function getPeopleInvoices(Request $request){

        try {
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);
            $people_id=$request->route('peopleId');
         
            $data = $this->peopleService->fetchInvoicesData($skip, $take,$people_id, $request);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Failed to fetch the Invoices data."
            ], 404);
        }
    }

    private function getPeopleService()
    {
        if ($this->peopleService == null) {
            $this->peopleService = new PeopleService();
        }
        return $this->peopleService;
    }


}
