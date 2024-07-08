<?php

namespace App\Http\Controllers;

use App\Services\ResetPasswordService;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class ResetPasswordController extends Controller
{

    protected $resetPasswordService;

    // Function to Create new Instance of ResetPassword Service
    private function getResetPasswordService()
    {
        if ($this->resetPasswordService == null) {
            $this->resetPasswordService = new ResetPasswordService();
        }
        return $this->resetPasswordService;
    }

    // Forgot Password Functionality
    public function forgotPassword(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                'identifier' => ['required', 'string'],
            ]);

            if($validator->fails()) {
                return response()->json(['message' => Config::get('message.error.exception_error')], 422); //$validator->errors()
            }

            $identifier = $request->input('identifier');

            // $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // Retrieve the user by the identifier from Service
            $user = $this->getResetPasswordService()->getUserData($identifier);

            if (!$user) {
                return response()->json(['message' => Config::get('message.error.user_not_found')], 422);
            }

            // check if user has registered but not set password
            if ($user && !$user->password) {
                return response()->json(['message' => Config::get('message.error.account_not_activated_reset')], 403);
            }

            // Check if the user account is active
            if ($user && !$user->status) {
                return response()->json(['message' => Config::get('message.error.account_deactivated')], 403);
            }

            // Generate a token for password reset
            $token = Str::random(60);

            $email = $user->email;

            // return response()->json(['message' => 'hii'], 500);

            // Insert the token for password reset in DB
            DB::insert('insert into password_reset_tokens (email,token) values(?,?)',[$email, $token]);
            // $this->getResetPasswordService()->setResetToken($email, $token);

            // Get additional data of user for email template
            $id = $user->user_id;
            $userName = $user->username;
            $fullname = $user->firstname . " " . $user->surname;
            $firstName = $user->firstname;
            $surName = $user->surname;

            // Generate password reset link
            $url = url("http://localhost:4200/reset-password/{$token}");

            // Send email with the reset link
            if(Mail::to($email)->send(new ResetPasswordMail($url, $token, $id, $fullname, $userName, $firstName, $surName, $email))) {
                return response()->json(['message' => Config::get('message.success.reset_mailsent_success')], 201);
            }
            else {
                return response()->json(['message' => Config::get('message.error.reset_mailsent_error')], 405);
            }

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }

    // Validate the user requested for forgot password using token link
    public function validateForgotPasswordUser(Request $request) {

        try {
            $token = $request->input('token');

            // fetch user details who requested for reset password
            $user = $this->getResetPasswordService()->getForgotPasswordUser($token);

            if($user) {
                return response()->json(['message' => Config::get('message.success.set_new_password')], 200);
            }
            else {
                return response()->json(['message' => Config::get('message.error.reset_link_expired')], 422); // Invalid User Error
            }
        }
        catch (Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }

    // Save the reset password
    public function saveResetPassword(Request $request) {

        try {
            $validator = Validator::make($request->all(),[
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'required'
            ]);

            $password = $request->input('data.password');
            $token = $request->input('token');

            // Validate the token against the password_reset_tokens table
            $userData = $this->getResetPasswordService()->validateToken($token);

            $password = Hash::make($password);

            if(User::where('email', $userData->email)->update(['password' => $password])) {
                // Delete the used token after password reset
                $this->getResetPasswordService()->deleteUsedToken($userData);

                return response()->json(['message' => Config::get('message.success.password_update_success')], 200);
            }
            else {
                return response()->json(['message' => Config::get('message.error.invalid_user_error')], 422);
            }

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }




}
