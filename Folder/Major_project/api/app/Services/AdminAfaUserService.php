<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AdminAfaUserService {

    // Fetch all AFA Users based on searched fields
    public function fetchAfaUsers($orgId, $skip, $take, $request) {

        try {
            // return response()->json([$request->all()]);

            // Start with a query builder instance
            $query = User::query();

            $query->where('organisation_id', $orgId)
            ->where('is_deleted', false)
            ->orderBy('updated_at', 'DESC');

            if($request->searchFormData) {

                // Retrieve form input search data
                $firstname = $request->searchFormData['firstName'];
                $surname = $request->searchFormData['surName'];
                $username = $request->searchFormData['userName'];
                $email = $request->searchFormData['email'];
                $status = $request->searchFormData['status'];
                $activity = $request->searchFormData['activity'];
                $last_active = $request->searchFormData['lastActive'];

                // Apply filters based on individual inputs if they are provided

                if ($firstname) {
                    $query->where('firstname', 'like', '%' . $firstname . '%');
                }

                if ($surname) {
                    $query->where('surname', 'like','%' . $surname . '%');
                }

                if($username) {
                    $query->where('username', 'like', '%' . $username . '%');
                }

                if($email) {
                    $query->where('email', 'like', '%' . $email . '%');
                }

                if($status == "activated") {
                    $query->where('status', true);
                }

                if($status == "deactivated") {
                    $query->where('status', false)
                          ->whereNotNull('password');
                }

                if($status == "pending") {
                    $query->where('password', null);
                }

                if($activity == "LoggedIn") {
                    $query->whereNotNull('last_active');
                }

                if($activity == "notLoggedIn") {
                    $query->where('last_active', null);
                }

                if($last_active) {
                    // $last_active_date = \Carbon\Carbon::createFromFormat('d-m-Y', $last_active)->format('Y-m-d');
                    $query->whereDate('last_active', $last_active);
                }
            }


            // Query to get the count of data
            $totalCount = $query->count();

            // Query to get the data
            $searchedData = $query->skip($skip)
                                ->take($take)
                                ->get();

            return [
                'searchedData' => $searchedData,
                'totalCount' => $totalCount
            ];

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Fetching AFA User Stats
    public function getUserStats($orgId) {
        try{

            $organisation = Organisation::where('organisation_id', $orgId)->first();
            $organisation_name = $organisation->name;

            $user_count = User::where('organisation_id', $orgId)
                                ->where('is_deleted', 0)
                                ->count();

            $active_users = User::where('organisation_id', $orgId)
                                  ->where('is_deleted', 0)
                                  ->where('status', true)
                                  ->count();

            $inactive_users = $user_count - $active_users;

            // Active users in the last 24 hours
            $active_users_last_24_hours = User::where('organisation_id', $orgId)
                                                ->where('is_deleted', 0)
                                                ->where('last_active', '>=', now()->subDay())
                                                ->count();

            // Newly registered users in the last month
            $new_users_last_month = User::where('organisation_id', $orgId)
                                        ->where('is_deleted', 0)
                                        ->where('created_at', '>=', now()->subMonth())
                                        ->count();

            // Users with null or not set passwords
            $users_pending_verification = User::where('organisation_id', $orgId)
                                                ->where('is_deleted', 0)
                                                ->whereNull('password')
                                                ->count();

            return [
                'organisation_name' => $organisation_name,
                'user_count' => $user_count,
                'active_users' => $active_users,
                'inactive_users' => $inactive_users,
                'active_users_last_24_hours' => $active_users_last_24_hours,
                'new_users_last_month' => $new_users_last_month,
                'users_pending_verification' => $users_pending_verification
            ];

        }
        catch(Exception $e){
            return $e->getMessage();
        }

    }

    // Fetch user
    public function getUser($userId) {
        try {
            $user = User::where('user_id', $userId)->first();
            return $user;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }

    }

    // Fetch Organisation Details of the user
    public function getOrganisationDetails($orgId) {
        try {
            $organisation = Organisation::where('organisation_id', $orgId)->first();
            return $organisation;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Toggle status of User
    public function toggleUserStatus($user) {
        try {
            $authUser = Auth::user();

            $user->status = !$user->status;
            $user->updated_by = $authUser->user_id;
            $user->save();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }

    }

    // Fetch the Edit details of selected user
    public function getEditDetails($userId) {
        try {
            $user = User::select('firstname', 'surname', 'username', 'email')
                        ->where('user_id', $userId)
                        ->first();

            return $user;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Update User Details in DB.
    public function updateUserDetails($userId, $request) {
        try {
            // $admin_id = $request->admin_id;
            $admin = Auth::user();
            $admin_id = $admin->user_id;

            $user = User::where('user_id', $userId)->first();

            $user->firstname = $request->data['firstName'];
            $user->surname = $request->data['surName'];
            $user->username = $request->data['userName'];
            $user->email = $request->data['email'];
            $user->updated_by = $admin_id;

            $user->save();

        }
        catch(Exception $e) {
            return $e->getMessage();
        }

    }

    // Soft delete the user from the DB.
    public function deleteUser($user){
        try {
            $authUser = Auth::user();

            $user->is_deleted = true;
            $user->updated_by = $authUser->user_id;
            $user->save();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }

    }


    // Create the user and insert the token in the DB.
    public function createUser($orgId, $request) {
        try {
            // $admin_id = $request->admin_id;
            $admin = Auth::user();
            $admin_id = $admin->user_id;

            $user = new User;
            $user->firstname = $request->data['firstName'];
            $user->surname = $request->data['surName'];
            $user->username = $request->data['userName'];
            $user->email = $request->data['email'];
            $user->organisation_id = $orgId;
            $user->created_by = $admin_id;
            $user->updated_by = $admin_id;

            // Generate a token for password set
            $token = Str::random(60);
            $user->token = $token; // save generated token in user table

            $user->save();

            return [
                'user' => $user,
                'token' => $token
            ];

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


}
