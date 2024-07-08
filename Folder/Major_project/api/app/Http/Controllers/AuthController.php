<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    // Function to Create new Instance of Auth Service
    private function getAuthService()
    {
        if ($this->authService == null) {
            $this->authService = new AuthService();
        }
        return $this->authService;
    }

    // Function to check for Login Authentication
    public function login(Request $request) {

        try {
            // return response()->json(['message' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'identifier' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 422);
            }

            $identifier = $request->input('identifier');
            $password = $request->input('password');

            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // Retrieve the user by the identifier
            $user = $this->getAuthService()->getUserData($field, $identifier);

            // check if user has registered but not set password
            if ($user && !$user->password) {
                return response()->json(['message' => Config::get('message.error.account_not_activated')], 403);
            }

            // Check if the user account is active
            if ($user && !$user->status) {
                return response()->json(['message' => Config::get('message.error.account_deactivated')], 403);
            }

            if(!Auth::attempt([$field => $identifier, 'password' => $password])) {
                return response()->json(['message' => Config::get('message.error.invalid_credentials')], 401);
            }

            // Generation of tokens and saving user login details
            $data = $this->getAuthService()->saveAuthUserDetails();

            $user = $data['user'];
            $tokenResult = $data['tokenResult'];
            $token = $data['token'];


            return response()->json([
                'user' => $user,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at->toDateTimeString(),
                'role' => $user->role->role_name,
            ], 200);

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }

    // Logout Functionality
    public function logout(Request $request) {

        try {
            if($request->user()){
                // revoke the current user access token
                $this->getAuthService()->revokeUserToken($request->user());

                return response()->json(['message' => Config::get('message.success.logged_out')]);
            }
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }


    public function logoutAllDevices(Request $request) {

        try{
            // $bearerToken = $request->bearerToken();

            if($request->user()) { // find the authenticated user using passport
                // revoke the current user's all access token
                $this->getAuthService()->revokeAllUserToken($request->user());

                // return response()->json(['message' => $allTokens]);

                return response()->json(['message' => Config::get('message.success.allDevices_logged_out')]);
            }
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 404);
        }

    }



}
