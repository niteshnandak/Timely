<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminOrgService;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;


class AdminOrganisationsController extends Controller
{
    protected $AdminOrgService = null;

    //LAZY INITIALIZATION
    private function getAdminOrgService()
    {
        if ($this->AdminOrgService == null) {
            $this->AdminOrgService = new AdminOrgService();
        }

        return $this->AdminOrgService;
    }

    //FUNCTION LOAD ORGANISATION
    public function getOrganisations(Request $request)
    {
        // return response($request);
        try {
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);
            $data = $this->getAdminOrgService()->fetchOrgsData($skip, $take, $request);

            //return success message
            return response()->json([
                "data" => $data,
            ], 200);
        } catch (Exception $e) {

            //return error message
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Failed to fetch the organisations data."
            ], 404);
        }
    }

    //FUNCTION CREATE ORGANISATION
    public function createOrganisation(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'organisationName' => 'required|unique:organisation,name',
                'status' => 'required',
                'firstName' => 'required|string|max:255',
                'surName' => 'required|string|max:255',
                'userName' => 'required|max:255|unique:users,username',
                'email' => 'required|max:255|unique:users,email',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            //laravel log error
            Log::error($e);

            //return error message
            return response()->json(['errors' => $errors], 422);
        }

        try {
            $org_data = $this->getAdminOrgService()->createOrg($request);
            $organisation_id = $org_data['organisation_id'];

            //laravel log error
            Log::debug($organisation_id);

            $user_data = $this->getAdminOrgService()->createUser($request, $organisation_id);
            // Log::debug($user_data);

            //return success message
            return response()->json(['message' => Config::get('message.success.organisation_create')]);
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);

            //return error message
            return response()->json(['message' => Config::get('message.error.organisation_create')]);
        }
    }

    //FUNCTION TO PATCH THE EDIT INPUTS
    public function getEditDetails($org_id)
    {

        try {
            $org_data = $this->getAdminOrgService()->getEditOrgDetails($org_id);
        } catch (Exception $e) {

            //laravel log error
            Log::error($e);
            return $e->getMessage();
        }

        //return success message
        return response()->json(
            ["org_data" => $org_data]
        );
    }

    //FUNCTION TO UPDATE ORGANISATION DETAILS
    public function updateOrganisation($org_id, Request $request)
    {

        try {
            $update_data = $this->getAdminOrgService()->updateOrgDetails($org_id, $request);

            ////return success message
            return response()->json([
                "message" => Config::get('message.success.organisation_update')
            ]);
        } catch (Exception $e) {

            //laravel log error
            Log::error($e);

            ////return error message
            return response()->json([
                "message" => Config::get('message.error.organisation_update')
            ], 500);
        }
    }

    //FUNCTION TO DELETE ORGANISATION
    public function deleteOrganisation($org_id)
    {
        try {
            $org = Organisation::where('organisation_id', $org_id)->first();
            if (!$org) {

                return response()->json(['message' => Config::get('message.error.organisation_not_found_delete')], 404);
            }
            $authUser = Auth::user();

            $org->is_deleted = true;
            $org->updated_by = $authUser->user_id;

            $org->save();

            ////return success message
            return response()->json(['message' => Config::get('message.success.organisation_delete')], 200);
        } catch (Exception $e) {

            //laravel log error
            Log::error($e);

            ////return error message
            return response()->json([
                "message" => Config::get('message.error.organisation_delete')
            ], 500);
        }
    }

    //FUNCTION TO DYNAMICALLY CHANGE ACTIVE STATUS
    public function toggleActiveStatus($org_id)
    {
        

        $org = Organisation::where('organisation_id', $org_id)->first();

        if (!$org) {

            ////return error message
            return response()->json(['message' => Config::get('message.error.organisation_not_found')], 404);
        }
        $authUser = Auth::user();

        $org->status = !$org->status;
        $org->updated_by = $authUser->user_id;

        $org->save();


        if ($org->status == true) {
            User::where('organisation_id', $org_id)
                ->where(function ($query) {
                    $query->WhereNotNull('password');
                })
                ->update(['status' => true]);

            //return success message
            return response()->json(['message' => Config::get('message.success.organisation_activated')], 200);
        } else {

            User::where('organisation_id', $org_id)->update(['status' => false]);

            //return error message
            return response()->json(['message' => Config::get('message.success.organisation_deactivated')], 200);
        }
    }

    //FUNCTION TO GET THE ORGANISATION STATISTICS
    public function getOrgStats()

    {
        try {
            $org_stats = $this->getAdminOrgService()->fetchOrgStats();

            ////return error message
            return response()->json($org_stats);
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);
        }
    }
    public function getOrgDetails($org_id)

    {

        // return response()->json(['message' => 'getting details']);
        try {
            $org_details = $this->getAdminOrgService()->fetchOrganisationDetailsInfo($org_id);

            ////return error message
            return response()->json($org_details);
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);
        }
    }



    //FUNCTION TO FETCH FILTERED DATA
    //NOT USING ANYMORE
    // public function getSearchOrgs(Request $request)

    // {
    //     try {

    //         $org_data = $this->getAdminOrgService()->searchOrgs($request);

    //         return response()->json([
    //             "org_data" => $org_data,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error($e);
    //     }
    // }
}
