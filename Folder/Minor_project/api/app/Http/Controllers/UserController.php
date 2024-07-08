<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SetPasswordEmailService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class UserController extends Controller
{
    protected $userService = null;


    public function register(Request $request)
    {

        $request->validate(
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users,email',
                'phone_number' => 'required|unique:users,phone_number',
            ],
            [
                'phone_number' => 'Phone Number has already been taken',
            ]
        );
        try {
            $user = $this->getUserService()->register($request);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $id = $user['id'];
        $email = $user['email'];
        $fullname = $user['first_name'] . " " . $user['last_name'];

        // Create the token for the Authentication of registered User
        $token = $this->create_token();
        $user = $this->getUserService()->saveUserToken($email, $token);


        $url = url("http://localhost:4200/set-password/{$token}");

        $sendmail = (new SetPasswordEmailService())->send_email($email, $fullname, $id, $url);

        if ($sendmail) {

            return response()->json(
                [
                    "toaster_success" => Config::get('message.success.register_success')
                ],
                201
            );
        } else {
            return response()->json(
                [
                    "toaster_error" => Config::get('message.error.register_error')
                ],
                422
            );
        }
    }

    private function create_token()
    {
        $token = Str::random(60);
        return $token;
    }

    // This validates the User and sends tge response
    public function password_page(Request $request)
    {
        $token = $request->input('token');
        $user = User::where('token', $token)->first();

        if ($user) {
            return response()->json(
                [
                    "toaster_info" => Config::get('message.success.setpasswordpage_info')
                ],
                200
            );
        } else {
            return response()->json(
                [
                    "message" => Config::get('message.error.setpasswordpage_error')
                ],
                422
            );
        }
    }


    public function save_password(Request $request)
    {
        Validator::make($request->all(), [
            'password' => ['required', 'confirmed', 'min:8']
        ]);
        $password_saved = $this->getUserService()->postSavePassword($request);
        if ($password_saved) {
            return response()->json(
                [
                    "toaster_success" => Config::get('message.success.setpassword_success')
                ],
                200
            );
        } else {
            return response()->json(
                [
                    "toaster_error" => Config::get('message.error.setpassword_error')
                ],
                422
            );
        }
    }


    private function getUserService()
    {
        if ($this->userService == null) {
            $this->userService = new UserService();
        }
        return $this->userService;
    }
}
