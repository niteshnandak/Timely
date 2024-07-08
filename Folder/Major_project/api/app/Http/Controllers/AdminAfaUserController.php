<?php

namespace App\Http\Controllers;

use App\Services\AdminAfaUserService;
use App\Mail\AdminSetPasswordMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Config;


class AdminAfaUserController extends Controller
{

    protected $adminAfaUserService;

    // Function to Create new Instance of AdminAfaUser Service
    private function getAdminAfaUserService()
    {
        if ($this->adminAfaUserService == null) {
            $this->adminAfaUserService = new AdminAfaUserService();
        }
        return $this->adminAfaUserService;
    }

    // Fetch All/Searched AFA users
    public function getAfaUsers($orgId, Request $request) {

        try {

            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);

            // Fetch all AFA Users based on searched fields
            $data = $this->getAdminAfaUserService()->fetchAfaUsers($orgId, $skip, $take, $request);

            $searchedData = $data['searchedData'];
            $totalCount = $data['totalCount'];

            if ($searchedData->isEmpty()) {
                return response()->json(['message' => Config::get('message.error.no_users_found')], 404);
            }

            return response()->json([
                'searchedUserData' => $searchedData,
                'total' => $totalCount
            ], 200);

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }

    }


    // Fetch AFA User Card Details
    public function getAfaUserStats($orgId) {
        try {
            // Fetching AFA User Stats
            $data = $this->getAdminAfaUserService()->getUserStats($orgId);

            $organisation_name = $data['organisation_name'];
            $user_count = $data['user_count'];
            $active_users = $data['active_users'];
            $inactive_users = $data['inactive_users'];
            $active_users_last_24_hours = $data['active_users_last_24_hours'];
            $new_users_last_month = $data['new_users_last_month'];
            $users_pending_verification = $data['users_pending_verification'];

            return response()->json([
                'organisation_name' => $organisation_name,
                'user_count' => $user_count,
                'active_users' => $active_users,
                'inactive_users' => $inactive_users,
                'active_users_last_24_hours' => $active_users_last_24_hours,
                'new_users_last_month' => $new_users_last_month,
                'users_pending_verification' => $users_pending_verification
            ]);

        }
        catch(Exception $e) {
            return response()->json(['error' => Config::get('message.error.exception_error')], 500); // $e->getMessage()
        }

    }


    // Toggle the Status of the User
    public function toggleActiveStatus($orgId, $userId) {
        // return response()->json(['org_id' => $orgId, 'user_id' => $userId]);

        try {
            // fetch the user details
            $user = $this->getAdminAfaUserService()->getUser($userId);

            if(!$user) {
                return response()->json(['message' => Config::get('message.error.no_users_found')], 404);
            }

            // toggle the user status
            $this->getAdminAfaUserService()->toggleUserStatus($user);

            if($user->status == true) {
                return response()->json(['message' => Config::get('message.success.user_activate_success')], 200);
            }
            else {
                return response()->json(['message' => Config::get('message.success.user_deactivate_success')], 200);
            }

        }
        catch(Exception $e){
            return response()->json(['error' => Config::get('message.error.exception_error')], 500);
        }

    }


    // Create and register the new AFA user
    public function createAfaUser($orgId, Request $request) {
        // return response()->json(['org' => $orgId, 'data' => $request->all()]);
        // return response()->json($request->all());
        // return response()->json($request->data['firstName']);

        $validatedData = $request->validate([
            'data.firstName' => 'required|string|max:255',
            'data.surName' => 'required|string|max:255',
            'data.userName' => 'required|max:255|unique:users,username',
            'data.email' => 'required|max:255|unique:users,email',
        ]);

        try {
            // Create the user and generate token
            $data = $this->getAdminAfaUserService()->createUser($orgId, $request);

            $user = $data['user'];
            $token = $data['token'];
            $email = $request->data['email'];

            // Get additional data of user for email template
            $id = $user->user_id;
            $userName = $user->username;
            $fullname = $user->firstname . " " . $user->surname;
            $firstName = $user->firstname;
            $surName = $user->surname;

            // Fetch the organisation details
            $organisation = $this->getAdminAfaUserService()->getOrganisationDetails($orgId);
            $organisation_name = $organisation->name;

            // Generate password reset link
            $url = url("http://localhost:4200/set-password/{$token}");

            // Send email with the reset link
            if(Mail::to($email)->send(new AdminSetPasswordMail($url, $token, $id, $fullname, $userName, $organisation_name, $firstName, $surName, $email))) {
                return response()->json(
                    [
                        'message' => Config::get('message.success.password_set_link'),
                        'success' => Config::get('message.success.afauser_created_success'),
                    ], 201);
            }
            else {
                return response()->json(['message' => Config::get('message.error.reset_mailsent_error')], 405);
            }

        }
        catch (Exception $e) {
            return response()->json(['message' => "Failed to create AFA User"], 400);
        }

    }


    // Fetch Edit details of AFA user
    public function getAfaUserEditDetails($orgId, $userId) {
        try {
            // fetching the user and his details for editing
            $user = $this->getAdminAfaUserService()->getEditDetails($userId);

            if (!$user) {
                return response()->json(['error' => Config::get('message.error.no_users_found')], 404);
            }

            return response()->json(['afa_data' => $user], 200);

        }
        catch(Exception $e) {
            return response()->json(['error' => Config::get('message.error.exception_error')], 400);
        }
    }

    // Update AFA User Details
    public function updateAfaUserEditDetails($orgId, $userId, Request $request) {

        try {
            $this->getAdminAfaUserService()->updateUserDetails($userId, $request);
        }
        catch(Exception $e) {
            // Log::error($e);
            return response()->json(['error' => Config::get('message.error.afauser_update_error')], 400);
        }
    }


    // Delete AFA User
    public function deleteAfaUser($orgId, $userId) {
        try {
            // Fetch the user details to be deleted
            $user = $this->getAdminAfaUserService()->getUser($userId);

            if(!$user) {
                return response()->json(['message' => Config::get('message.error.no_users_found')], 404);
            }

            $this->getAdminAfaUserService()->deleteUser($user);

            return response()->json(['message' => Config::get('message.success.afauser_delete_success')], 200);

        }
        catch(Exception $e) {
            return response()->json(['error' => Config::get('message.error.afauser_delete_error')], 400);
        }
    }



}
