<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Assignment;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\People;
use App\Models\PeopleBankDetail;
use App\Models\PeopleEmploymentDetail;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class PeopleService
{
    public function gridPeopleData($skip, $take, $order, $people_details)
    {
        $user = Auth::user();
        $peopleData = People::join('address', 'people.address_id', '=', 'address.address_id')
            ->join('people_employment_details', 'people.people_id', '=', 'people_employment_details.people_id')
            ->select([
                'people.people_id',
                'people.people_name',
                'people.phone_number',
                'people.email_address',
                'people.job_title',
                'people.birth_date',
                'people.gender',
                'people.status',
                'address.address_line_1',
                'address.address_line_2',
                'address.state',
                'address.city',
                'address.country',
                'address.pincode',
                'people_employment_details.joining_date',
                'people_employment_details.pay_frequency',
                'people_employment_details.company_name'
            ])
            ->where(['people.is_deleted' => People::STATUS_DELETED, 'people.organisation_id' => $user['organisation_id']])
            ->orderBy('people.created_at', $order);


        if (!empty($people_details)) {
            if (!empty($people_details['people_name'])) {
                $peopleData->where('people.people_name', 'like', '%' . $people_details['people_name'] . '%');
            }
            if (!empty($people_details['job_title'])) {
                $peopleData->where('people.job_title', 'like', '%' . $people_details['job_title'] . '%');
            }
            if (!empty($people_details['gender'])) {
                $peopleData->where('people.gender', $people_details['gender']);
            }
            if (!empty($people_details['company_name'])) {
                $peopleData->where('people_employment_details.company_id', $people_details['company_name']);
            }
            if (!empty($people_details['joining_date'])) {
                $peopleData->where('people_employment_details.joining_date', 'like', '%' . $people_details['joining_date'] . '%');
            }
            // $skip =0;
            // $take =10;
        }

        $countPeopleData = clone $peopleData; // Clone the query builder instance for counting total records
        $peopleData = $peopleData->skip($skip)->take($take)->get();


        return [$peopleData, $countPeopleData->get()->count()];
    }

    public function createPeople($people_data)
    {
        $user = Auth::user();

        $add_people_details =  $people_data['peopleDetails'];
        $people = $this->createPeopleDetails($add_people_details, $user);

        $add_people_address_details = $people_data["peopleAddressDetails"];
        $people_address_details = $this->createPeopleAddressDetails($people, $add_people_address_details, $user);


        $peopleUpdate =    People::where('people_id', $people['people_id'])->first()->update(['address_id' => $people_address_details['address_id'], 'update_at' => now(), 'updated_by' => $user['user_id']]);

        if ($peopleUpdate) {
            Address::where('address_id', $people_address_details['address_id'])->update(['people_id' => $people['people_id']]);
        }
        $people_employment_details = $this->createPeopleEmploymentDetails($people, $user);
        $people_bank_details = $this->createPeopleBankDetails($people, $user);
        if ($people && $people_address_details && $people_employment_details && $people_bank_details) {
            return true;
        } else {
            return false;
        }
    }

    public function createPeopleDetails($add_people_details, $user)
    {
        try {
            $people  =  People::create([
                'people_name' => $add_people_details['people_name'],
                'birth_date' => $add_people_details['birth_date'],
                'email_address' => $add_people_details['email_address'],
                'phone_number' => $add_people_details['phone_number'],
                'gender' => $add_people_details['gender'],
                'job_title' => $add_people_details['job_title'],
                'organisation_id' => $user['organisation_id'],
                'status' => People::STATUS_ACTIVE,
                'is_deleted' => People::STATUS_DELETED,
                'created_at' => Carbon::now(),
                'created_by' => $user['user_id'],
            ]);

            return $people;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function createPeopleAddressDetails($people, $add_people_address_details, $user)
    {
        try {
            $peopleAddress =  Address::create([
                'people_id' => $people['people_id'],
                'address_line_1' => $add_people_address_details['address_line_1'],
                'address_line_2' => $add_people_address_details['address_line_2'],
                'city' => $add_people_address_details['city'],
                'state' => $add_people_address_details['state'],
                'country' => $add_people_address_details['country'],
                'pincode' => $add_people_address_details['pincode'],
                'is_deleted' => Address::STATUS_DELETED,
                'created_at' => Carbon::now(),
                'created_by' =>  $user['user_id'],
            ]);
            return $peopleAddress;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function createPeopleEmploymentDetails($people, $user)
    {
        try {
            $add_people_employment_details = PeopleEmploymentDetail::create([
                'people_id' => $people['people_id'],
                'people_name' => $people['people_name'],
                'organisation_id' => $user['organisation_id'],
                'is_deleted' => PeopleEmploymentDetail::STATUS_DELETED,
                'created_at' => Carbon::now(),
                'created_by' =>  $user['user_id'],
            ]);
            return $add_people_employment_details;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function createPeopleBankDetails($people, $user)
    {
        try {
            $add_people_bank_details  =  PeopleBankDetail::create([
                'people_id' => $people['people_id'],
                'people_name' => $people['people_name'],
                'organisation_id' => $user['organisation_id'],
                'is_deleted' => PeopleBankDetail::STATUS_DELETED,
                'created_at' => Carbon::now(),
                'created_by' =>  $user['user_id'],
            ]);
            return $add_people_bank_details;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getPeopleData($people_id)
    {
        $people = [];
        $people["people_details"] = People::where(['people_id' => $people_id, 'is_deleted' => People::STATUS_DELETED])->get();
        $people["people_employment_details"] = PeopleEmploymentDetail::where(['people_id' => $people_id, 'is_deleted' => PeopleEmploymentDetail::STATUS_DELETED])->get();
        $people["people_address_details"] = Address::where(['people_id' => $people_id, 'is_deleted' => Address::STATUS_DELETED])->get();
        $people["people_bank_details"] = PeopleBankDetail::where(['people_id' => $people_id, 'is_deleted' => PeopleBankDetail::STATUS_DELETED])->get();
        return $people;
    }


    public function editPeopleData($people_id, $people_data)
    {
        $user = Auth::user();
        if (
            $this->updatePeopleDetails($people_id, $people_data, $user) &&
            $this->updateAddressDetails($people_id, $people_data, $user)  &&
            $this->updateEmploymentDetails($people_id, $people_data, $user) &&
            $this->updateBankDetails($people_id, $people_data, $user)
        ) {
            return true;
        }
    }

    public function updatePeopleDetails($people_id, $people_data, $user)
    {
        $people_details =  $people_data['peopleDetails'];
        try {
            People::where('people_id', $people_id)->update(
                [
                    'people_name' => $people_details['people_name'],
                    'birth_date' => $people_details['birth_date'],
                    'email_address' => $people_details['email_address'],
                    'phone_number' => $people_details['phone_number'],
                    'gender' => $people_details['gender'],
                    'job_title' => $people_details['job_title'],
                    'updated_at' => Carbon::now(),
                    'updated_by' => $user['user_id']
                ]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function updateAddressDetails($people_id, $people_data, $user)
    {
        $people_address_details = $people_data["peopleAddressDetails"];
        try {
            Address::where('people_id', $people_id)->update(
                [
                    'address_line_1' => $people_address_details['address_line_1'],
                    'address_line_2' => $people_address_details['address_line_2'],
                    'city' => $people_address_details['city'],
                    'state' => $people_address_details['state'],
                    'country' => $people_address_details['country'],
                    'pincode' => $people_address_details['pincode'],
                    'updated_at' => Carbon::now(),
                    'updated_by' => $user['user_id']
                ]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function updateEmploymentDetails($people_id, $people_data, $user)
    {
        $people_details =  $people_data['peopleDetails'];
        $people_employment_details = $people_data["peopleEmploymentDetails"];
        $company_id = $people_employment_details['company_name'];
        $company_name = Company::where('company_id', $company_id)->get(['company_name'])->first();
        try {
            PeopleEmploymentDetail::where('people_id', $people_id)->update(
                [
                    'organisation_id' => $user['organisation_id'],
                    'people_name' => $people_details['people_name'],
                    'company_id' => $company_id,
                    'company_name' => $company_name->company_name,
                    'joining_date' => $people_employment_details['joining_date'],
                    'pay_frequency' => $people_employment_details['pay_frequency'],
                    'nino_number' => $people_employment_details['nino_number'],
                    'updated_at' => Carbon::now(),
                    'updated_by' => $user['user_id']
                ]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
        //dd($company_name);
        return response()->json([
            "people_data" => $company_name
        ]);
    }

    public function updateBankDetails($people_id, $people_data, $user)
    {
        $people_details =  $people_data['peopleDetails'];
        $people_employment_details = $people_data["peopleEmploymentDetails"];
        $company_id = $people_employment_details['company_name'];
        $company_name = Company::where('company_id', $company_id)->get(['company_name'])->first();
        $people_bank_details = $people_data["peopleBankDetails"];

        try {
            PeopleBankDetail::where('people_id', $people_id)->update(
                [
                    'organisation_id' => $user['organisation_id'],
                    'people_name' => $people_details['people_name'],
                    'company_id' => $company_id,
                    'company_name' => $company_name->company_name,
                    'bank_name' => $people_bank_details['bank_name'],
                    'account_number' => $people_bank_details['account_number'],
                    'bank_branch' => $people_bank_details['bank_branch'],
                    'bank_ifsc_code' => $people_bank_details['bank_ifsc_code'],
                    'updated_at' => Carbon::now(),
                    'updated_by' => $user['user_id']
                ]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
        return response()->json([
            "people_data" => $company_name
        ]);
    }

    public function getCompanies()
    {
        $user = Auth::user();
        $companies = Company::select(["company_id", "company_name"])->where(['is_deleted' => People::STATUS_DELETED, 'organisation_id' => $user['organisation_id']])->get();
        return $companies;
    }

    public function deletePoeple($id)
    {
        $user = Auth::user();
        $people = People::where('people_id', $id)->update(['is_deleted' => 1, 'updated_at' => Carbon::now(), 'updated_by' => $user['user_id']]);
        $peopleEmployee = PeopleEmploymentDetail::where('people_id', $id)->update(['is_deleted' => 1, 'updated_at' => Carbon::now(), 'updated_by' => $user['user_id']]);
        $peopleBank =  PeopleBankDetail::where('people_id', $id)->update(['is_deleted' => 1, 'updated_at' => Carbon::now(), 'updated_by' => $user['user_id']]);
        if ($people && $peopleEmployee && $peopleBank) {
            return true;
        }
        return false;
    }

    public function getCardInfo()
    {
        $user = Auth::user();
        Log::error($user);
        //month dates
        $month_startDate = Carbon::now()->startOfMonth();
        $month_endDate = Carbon::now()->endOfMonth();
        //  Year Date
        $year_startDate = Carbon::now()->startOfYear();
        $year_endDate = Carbon::now()->endOfYear();

        $people = People::where(['organisation_id' => $user['organisation_id'], 'is_deleted' => People::STATUS_DELETED]);
        //getting total customer count
        $totalPeople = $people->count();
        $newPeopleLastMonth = $people
            ->whereBetween('created_at', [
                $month_startDate,
                $month_endDate
            ])->count();

        $newPeopleLastYear = $people
            ->whereBetween('created_at', [
                $year_startDate,
                $year_endDate
            ])->count();


        $peopleAssignmentMapped = Assignment::leftJoin('people', 'people.people_id', '=', 'assignment.people_id')
            ->where('people.organisation_id', $user['organisation_id'])
            ->groupBy('assignment.people_id')
            ->count();

        $peopleCompanyMapped  = People::leftJoin('people_employment_details', 'people.people_id', '=', 'people_employment_details.people_id')
            ->whereNotNull('people_employment_details.company_id')
            ->where(['people.is_deleted' => People::STATUS_DELETED, 'people.organisation_id' => $user['organisation_id']])
            ->count();

        $status_info = [
            "totalPeople" => $totalPeople,
            "newPeopleLastWeek" => $newPeopleLastMonth,
            "newPeopleLastMonth" => $newPeopleLastYear,
            "peopleAssignmentMapped" => $peopleAssignmentMapped,
            "peopleCompanyMapped" => $peopleCompanyMapped,
        ];

        return $status_info;
    }

    public function updateStatus($people_id, $status)
    {
        try {
            $people = People::find($people_id);
            if ($people) {
                $people->status = $status;
                $people->save();
                return ['message' => Config::get('message.success.people_status_success')];
            } else {
                return ['message' => Config::get('message.error.people_editPeople_error')];
            }
        } catch (\Exception $e) {
            Log::error('Error updating status: ' . $e->getMessage());
            return ['error' => Config::get('message.error.people_status_error')];
        }
    }

    // FUNCTION TO FETCH ASSIGNMENTS DATA UNDER THE PEOPLE
    public function fetchAssignmentsData($skip, $take, $people_id, $request)
    {
        try {
            $query = Assignment::where('is_deleted', 0)
                ->where('people_id', $people_id)
                ->orderBy('created_at', 'DESC');

            if ($request->searchFormData) {
                $assignmentNumber = $request->searchFormData['assignmentNumber'];
                $customerName = $request->searchFormData['customerName'];
                $location = $request->searchFormData['location'];
                $status = $request->searchFormData['status'];

                if ($assignmentNumber) {
                    $query->where('assignment_num', 'like','%'. $assignmentNumber . '%');
                }

                if ($customerName) {
                    $query->whereHas('customer', function ($q) use ($customerName) {
                        $q->where('customer_name', 'like','%'. $customerName . '%');
                    });
                }

                if ($location) {
                    $query->where('location', 'like','%'. $location . '%');
                }

                if ($status) {
                    $query->where('status', $status);
                }
            }

            $totalRecords = $query->count();
            $assignments = $query->skip($skip)
                ->take($take)
                ->with('customer:customer_id,customer_name')
                ->get();

            $result = $assignments->map(function ($assignment) {
                return [
                    'assignment_num' => $assignment->assignment_num,
                    'people_id' => $assignment->people_id,
                    'customer_name' => $assignment->customer->customer_name,
                    'start_date' => $assignment->start_date,
                    'end_date' => $assignment->end_date,
                    'location' => $assignment->location,
                    'status' => $assignment->status,
                ];
            });

            Log::debug($result);

            return [$result, $totalRecords];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }

    // FUNCTION TO FETCH INVOICES DATA UNDER THE PEOPLE
    public function fetchInvoicesData($skip, $take, $people_id, $request)
    {
        try {

            // query to retrieve the invoices and the other details based on the people id
            $invoice_datas = DB::table('invoice as i')
            ->leftJoin('people as p', "i.people_id", "=", "p.people_id")
            ->leftJoin('assignment as a', "i.assignment_id", "=", "a.assignment_id")
            ->leftJoin('customer as c', "i.customer_id", "=", "c.customer_id")
            ->where('i.people_id', $people_id);


            if ($request->searchFormData){
                if($request->searchFormData['invoiceNumber']){
                    $invoice_datas->where('i.invoice_number','like','%'.$request->searchFormData['invoiceNumber']. "%");
                }
                if($request->searchFormData['assignmentNumber']){
                    $invoice_datas->where('a.assignment_num','like','%'.$request->searchFormData['assignmentNumber']. "%");
                }
                if($request->searchFormData['customerName']){
                    $invoice_datas->where('c.customer_name','like','%'.$request->searchFormData['customerName']. "%");
                }
                if($request->searchFormData['periodEndDate']){
                    $invoice_datas->where('i.period_end_date','like',$request->searchFormData['periodEndDate']);
                }
                if($request->searchFormData['emailStatus']){
                    $invoice_datas->where('i.email_status','like',$request->searchFormData['emailStatus']);
                }
                if($request->searchFormData['payrollStatus']){
                    $invoice_datas->where('i.payroll_status','like',$request->searchFormData['payrollStatus']);
                }

                // fetching all the invoices from base query
                $invoice_datas = $invoice_datas
                ->get()
                ->toArray();

                return ["invoice_data" => $invoice_datas, "total" => sizeof($invoice_datas)];
            }
            else{
                // fetching all the invoices from base query
                $invoice_datas = $invoice_datas
                ->get()
                ->toArray();

                return ["invoice_data" => $invoice_datas, "total" => sizeof($invoice_datas)];

            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
