<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ResetPasswordService {

    // Retrieve the user by identifier
    public function getUserData($identifier) {
        try {

            $user = User::where('email', $identifier)->first();

            return $user;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Set token for password reset in DB.
    public function setResetToken($email, $token) {
        try {
            DB::insert('insert into password_reset_tokens (email,token) values(?,?)',[$email, $token]);
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // fetch user details who requested for reset password
    public function getForgotPasswordUser($token) {
        try {
            $user = DB::table('password_reset_tokens')->where('token', $token)->first();

            return $user;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Validate the token against the password_reset_tokens table
    public function validateToken($token) {
        try {
            $userData = DB::table('password_reset_tokens')->where('token',$token)->first();

            return $userData;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Delete used toke after password reset completed from DB
    public function deleteUsedToken($userData) {
        try {
            DB::table('password_reset_tokens')->where('email', $userData->email)->delete();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

}
