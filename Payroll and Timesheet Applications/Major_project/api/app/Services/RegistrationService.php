<?php

namespace App\Services;

use App\Models\User;
use App\Mail\SetPasswordMail;
use App\Models\Address;
use App\Models\Organisation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Throwable;
use Illuminate\Support\Facades\Config;


class RegistrationService
{

    //DB:CREATE ORG SELF
    public function registerOrg($request)
    {
        try {
            $org_data = $request->only(['organisationName']);

            $address = Address::create();

            $organisation = Organisation::create([
                'name' => $org_data['organisationName'],
                'address_id' => $address->address_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);


            return $organisation;
        } catch (\throwable $e) {

            //return error message
            return $e->getMessage();
        }
    }

    //DB:CREATE USER SELF REGISTRATION
    public function registerUser($request, $org_id)
    {
        try {
            $user_data = $request->only(['firstName', 'surName', 'userName', 'email']);


            $user = User::create([
                'firstname' => $user_data['firstName'],
                'surname' => $user_data['surName'],
                'username' => $user_data['userName'],
                'email' => $user_data['email'],
                'organisation_id' => $org_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),

            ]);


            return $user_data;
        } catch (\Throwable $e) {

            //laravel log error
            Log::error($e);

            //return error message
            return response()->json(['error' => $e->getMessage(), 'message' =>Config::get('message.error.db_registration_failed') ], 500);
        }
    }

    //DB:SAVE TOKEN
    public function saveRegisteredUserToken($email, $token)
    {
        //update expects the array where key are the col names
        //values are the data to be updated
        try {
            User::where('email', $email)->update(['token' => $token]);
            $user = User::where('email', $email)->first();

            return $user;
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);

            //return error message
            return $e->getMessage();
        }
    }

    //SET PASSWORD MAIL SERVICE
    public function sendSetPasswordEmail($data)
    {
        try {
            if (Mail::send(new SetPasswordMail($data))) {

                return true;
            }
            // Email sent successfully
        } catch (\Exception $e) {
            Log::error($e);

            //return error message
            return $e->getMessage();
        }
    }

    //DB::SAVE THE SET PASSWORD
    public function putPassword($request)
    {

        try {
            $password = Hash::make($request->input('data.password'));
            $token = $request->input('token');
            $user = User::where('token', $token)->first();

            if (!isset($user->password)) {
                $user->password = $password;
                $user->token = 'Expired';
                $user->status = true;
                $user->save();

                return true;
            }

            //return error message
            return false;
        } catch (\throwable $e) {

            //laravel log error
            Log::error($e);

            //return error message
            return $e->getMessage();
        }
    }
}
