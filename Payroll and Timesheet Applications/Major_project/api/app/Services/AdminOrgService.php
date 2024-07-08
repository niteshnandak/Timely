<?php

namespace App\Services;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Company;
use App\Models\Customer;
use App\Models\People;
use App\Models\Assignment;
use App\Models\Role;
use App\Models\Address;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminOrgService
{
    //DB:FETCH ORGANISATION DATA
    public function fetchOrgsData($skip, $take, $request)
    {
        try {

            //set organisation model to query from the table
            $query = Organisation::query();

            // if there is search data process filter accordingly
            if ($request->searchFormData) {
                $query->where('is_deleted', false)
                    ->orderBy('created_at', 'DESC');

                // $query->where('is_deleted', false);

                $org_name = $request->searchFormData['organisationName'];
                $status = $request->searchFormData['status'];

                //search condition to check every possibility of the organisation name
                if ($org_name) {
                    $query->where('name', 'like', $org_name . '%');
                }

                //check for the status condition
                if ($status) {
                    $query->where('status', $status);
                }
            }

            // retrieve the organisation data without filtering
            else {

                //else select all the data checking is_deleted
                $query->where('is_deleted', false)
                    ->orderBy('created_at', 'DESC');
            }


            //calculate the org count for pagination
            $total_org_count = $query->count();
            $orgs_data = $query->skip($skip)
                ->take($take)
                ->get();


            // success return of the total data and total orgs count
            return [
                'orgs_data' => $orgs_data,
                'total_org_count' => $total_org_count,
            ];
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);
        }
    }

    //DB:CREATE ORGANISATION DATA
    public function createOrg($request)
    {
        try {
            //add address and prcess the id to organisation table
            $address = Address::create();

            //fetch the organisation details from the request
            $org_data = $request->only(['organisationName', 'status', 'adminId']);

            $organisation = Organisation::create([
                'name' => $org_data['organisationName'],
                'status' => $org_data['status'],
                'address_id' => $address->address_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => $org_data['adminId'],
                'updated_by' => $org_data['adminId'],


            ]);

            //return the updated organisation table
            return $organisation;
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);

            return $e->getMessage();
        }
    }

    //DB:FETCH USER DATA
    public function createUser($request, $org_id)
    {
        try {


            Log::debug($org_id);
            $user_data = $request->only(['firstName', 'surName', 'userName', 'email', 'adminId']);
            $user = User::create([
                'firstname' => $user_data['firstName'],
                'surname' => $user_data['surName'],
                'username' => $user_data['userName'],
                'email' => $user_data['email'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'organisation_id' => $org_id,
                'created_by' => $user_data['adminId'],
                'updated_by' => $user_data['adminId'],
            ]);

            Log::debug($user);

            return $user_data;
        } catch (\Throwable $e) {

            //laravel log error
            Log::error($e);

            return response()->json(['error' => $e->getMessage(), 'message' => 'user registeration is unsuccessfull'], 500);
        }
    }

    //DB:FETCH EDIT ORGANISATION DATA USING ID
    public function getEditOrgDetails($org_id)
    {

        try {
            $organisation = Organisation::select('name', 'email_address', 'description', 'contact_number')
                ->where('organisation_id', $org_id)
                ->first();
            if (!$organisation) {
                return response()->json(['error' => 'Organisation not found'], 404);
            }

            return $organisation;
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);
        }
    }

    //DB:UPDATE ORGANISATION DATA
    public function updateOrgDetails($org_id, $request)
    {
        try {
            $organisation = Organisation::where('organisation_id', $org_id)->first();
            // Log::debug($organisation);

            $organisation->name = $request['organisationName'];
            $organisation->email_address = $request['emailAddress'];
            $organisation->description = $request['description'];
            $organisation->contact_number = $request['contactNumber'];
            $organisation->updated_at = Carbon::now();
            $organisation->updated_by = $request['adminId'];
            // $organisation->updated_by = "Admin";
            $organisation->save();

            return $organisation;
        } catch (\Exception $e) {

            //laravel log error
            Log::error($e);
            return response()->json(['error' => 'Failed to update organization'], 500);
        }
    }

    //DB:FETCH ORGANISATION DATA AND CREATE STATISTICS
    public function fetchOrgStats()
    {

        try {

            //single query to fetch the last week stats
            $org_in_last_week = Organisation::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()->format('Y-m-d H:i:s')])
                ->where('is_deleted', 0)
                ->count();

            //single query to fetch the last month record
            $org_in_last_month = Organisation::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()->format('Y-m-d H:i:s')])
                ->where('is_deleted', 0)
                ->count();

            $org_total = Organisation::where('is_deleted', 0)->count();

            //active inactive data feth 
            $org_active_count = Organisation::where('status', 1)
                ->where('is_deleted', 0)
                ->count();
            $org_inactive_count = $org_total - $org_active_count;

            //store in a variable to return
            $result = [
                'org_total' => $org_total,
                'org_last_week' => $org_in_last_week,
                'org_last_month' => $org_in_last_month,
                'org_active' => $org_active_count,
                'org_inactive' => $org_inactive_count
            ];

            return $result;
        } catch (\throwable $e) {
            $e->getMessage();

            //laravel log error
            Log::error($e);
            Log::debug($e);
        }
    }



    public function fetchOrganisationDetailsInfo($org_id)
    {
        try {
            // Fetch organisation details
            $org_details = Organisation::where('organisation_id', $org_id)
                ->first();
            $org_address = Organisation::join('address', 'organisation.address_id', '=', 'address.address_id')
                ->where('organisation.organisation_id', $org_id)
                ->select('address.*')
                ->first();

            // Fetch AFA user details
            $total_afa_users = User::where('is_deleted', 0)->where('organisation_id', $org_id)->count();
            $active_afa_users = User::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 1)->count();
            $inactive_afa_users = User::where('is_deleted', 0)->where('organisation_id', $org_id)->whereNotNull('password')->where('status', 0)->count();
            $yet_to_set_password_users = User::where('is_deleted', 0)->where('organisation_id', $org_id)->whereNull('password')->count();

            // Fetch company details
            $total_companies = Company::where('is_deleted', 0)->where('organisation_id', $org_id)->count();
            $active_companies = Company::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 0)->count();
            $inactive_companies = Company::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 0)->count();

            // Fetch customer details
            $total_customers = Customer::where('is_deleted', 0)->where('organisation_id', $org_id)->count();

            // Fetch people details
            $total_people = People::where('is_deleted', 0)->where('organisation_id', $org_id)->count();
            $male_people = People::where('is_deleted', 0)->where('organisation_id', $org_id)->where('gender', 'Male')->count();
            $female_people = People::where('is_deleted', 0)->where('organisation_id', $org_id)->where('gender', 'Female')->count();

            // Fetch assignment details
            $total_assignments = Assignment::where('is_deleted', 0)->where('organisation_id', $org_id)->count();
            $pending_assignments = Assignment::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 'Pending')->count();
            $ongoing_assignments = Assignment::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 'Ongoing')->count();
            $completed_assignments = Assignment::where('is_deleted', 0)->where('organisation_id', $org_id)->where('status', 'ompleted')->count();

            //Complete details
            $organisation_details = [ 'organisation' => [ 'about' => $org_details, 'address' => $org_address ], 'afaUsers' => [ 'total' => $total_afa_users, 'active' => $active_afa_users, 'inactive' => $inactive_afa_users, 'yetToSetPassword' => $yet_to_set_password_users ], 'company' => [ 'total' => $total_companies, 'active' => $active_companies, 'inactive' => $inactive_companies ], 'customer' => [ 'total' => $total_customers ], 'people' => [ 'total' => $total_people, 'male' => $male_people, 'female' => $female_people ], 'assignments' => [ 'total' => $total_assignments, 'pending' => $pending_assignments, 'ongoing' => $ongoing_assignments, 'completed' => $completed_assignments ] ];

            return $organisation_details;
        } catch (\Throwable $e) {
            // Log the error
            Log::error($e->getMessage());
            Log::debug($e);

            return null;
        }
    }



    //DB:FILTER ORGANISATION DATA
    //NOT USING ANYMORE
    // public function searchOrgs($request)
    // {
    //     $org_name = $request->input('organisationName');
    //     $status = $request->input('status');

    //     //organisation model with the given conditions
    //     try {

    //         $query = Organisation::query();

    //         $query->where('is_deleted', false);

    //         if ($org_name) {
    //             $query->where('name', $org_name);
    //         }

    //         if ($status) {
    //             $query->where('status', $status);
    //         }

    //         $searchedData = $query->get();

    //         return $searchedData;
    //     } catch (\throwable $e) {
    //         Log::error($e->getMessage());
    //     }
    // }
}
