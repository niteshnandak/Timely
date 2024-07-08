<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Exception;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\Validator;
use App\Services\RegistrationService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;


class UserController extends Controller
{

    protected $registration_service = null;

    //LAZY CREATION TO GET THE REGISTRATION SERVICE
    private function getRegistrationService()
    {
        try {
            if ($this->registration_service == null) {
                $this->registration_service = new RegistrationService();
            }

            return $this->registration_service;
        } catch (\throwable $e) {
            return $e->getMessage();
        }
    }

    //FUNCTION CALL FOR REGISTRATION MODULE
    public function registration(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'firstName' => 'required|string|max:255',
                'surName' => 'required|string|max:255',
                'userName' => 'required|max:255|unique:users,username',
                'organisationName' => 'required',
                'email' => 'required|max:255|unique:users,email',
            ]);
        } catch (ValidationException $e) {

            $errors = $e->errors();

            return response()->json(['errors' => $errors,'message'=>Config::get('message.error.reg_validation')], 422);
        }


        try {
            $all_data = $request->all();

            //$org_data store the name of the organizatrion as well as the ID
            $org_data = $this->getRegistrationService()->registerOrg($request);

            $organisation_id = $org_data['organisation_id'];

            //service call to create Userdata into the data base
            $user_data = $this->getRegistrationService()->registerUser($request,$organisation_id);

            $email = $user_data['email'];
            $token = $this->create_token();

            //Service call to save the token
            $save_reg_token = $this->getRegistrationService()->saveRegisteredUserToken($email, $token);
            $url = url("http://localhost:4200/set-password/{$token}");

            //data to be sent to the mail
            $email_data = [
                'subject' => 'Set your password ' . $user_data['userName'],
                'to_mail' => $user_data['email'],
                'url' => $url,
                'user_data' => $user_data,
                'org_data' => $org_data,
            ];

            $send_mail = $this->getRegistrationService()->sendSetPasswordEmail($email_data);

            return response()->json(['message' => Config::get('message.success.register_user') ], 200);
        } catch (\throwable $e) {
            Log::error($e);

            return response()->json(['message' => Config::get('message.error.register_user')], 400);
        }
    }


    //TOKEN CREATION FOR VALIDATING THE MAIL
    private function create_token()
    {
        try {
            $token = Str::random(60);

            return $token;
        } catch (\Throwable $e) {
            Log::error($e);

            return $e->getMessage();
        }
    }


    //FUNCTION TO VALIDATE THE USER WITH THE HELP OF TOKEN CREATED.
    public function verifyUser(Request $request)
    {
        try {
            $token = $request->input('token');
            $user = User::where('token', $token)->first();

            if ($user) {

                return response()->json(
                    [
                        "message" => Config::get('message.success.verify_user_found')
                    ],
                    200
                );
            } else {

                return response()->json(
                    [
                        "message" => Config::get('message.error.verify_user_found'),
                        "password_already_set" => Config::get('message.error.user_already_set_password')
                    ],
                    422
                );
            }
        } catch (\throwable $e) {
            Log::error($e);

            return $e->getMessage();
        }
    }

    //SAVE PASSWORD
    //422->unprocessable entity
    public function savePassword(Request $request)
    {
        Validator::make($request->all(), [
            'password' => ['required', 'confirmed']
        ]);
        $password_create = $this->getRegistrationService()->putPassword($request);
        if ($password_create) {

            return response()->json(['message' => Config::get('message.success.password_saved') ], 200);
        } else {
            
            return response()->json(['message' => Config::get('message.error.password_save_failed')], 422);
        }
    }
}
