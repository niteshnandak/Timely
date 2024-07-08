<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function register($request)
    {
        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->phone_number = $request->input('phone_number');
        $user->save();
        return $user;
    }

    public function saveUserToken($email, $token)
    {
        User::where('email', $email)->update(['token' => $token]);
        $user = User::where('email', $email)->first();
        return $user;
    }

    public function postSavePassword($request)
    {
        $password = Hash::make($request->input('data.password'));
        $user = User::where('token', $request->input('token'))->first();
        if (empty($user->password)) {
            $user->password = $password;
            User::where('token', $request->input('token'))->update(['token' => "Expired"]);
            $user->save();
            return true;
        }
        return false;
    }
}
