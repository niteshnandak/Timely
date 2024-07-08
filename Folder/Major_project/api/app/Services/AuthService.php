<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService {

    // Retrieve the user by identifier
    public function getUserData($field, $identifier) {
        try {

            $user = User::where($field, $identifier)->first();

            return $user;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Generation of tokens and saving Authenticated user details
    public function saveAuthUserDetails() {
        try {
            /**
             * @var User $user
             */
            $user = Auth::user();
            $tokenResult = $user->createToken('Personal Access Token: ' . $user->username);
            $token = $tokenResult->token; // accessing the oauth_access_token table

            $user->last_active = now();
            $user->save();

            return [
                'user' => $user,
                'tokenResult' => $tokenResult,
                'token' => $token
            ];
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Revoke the current auth user access token from database
    public function revokeUserToken($user) {
        try {
            $token = $user->token(); //fetches the ouauth_access_token table instance for the authenticated user
            $token->revoke();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Revoke all the curren auth user's Access token from database
    public function revokeAllUserToken($user) {
        try {
            //$user = $request->user(); // currently authenticated user
            $allTokens = $user->tokens->where('revoked', false); // finds all tokens for the authenticated user which are not revoked
            $allTokens->each(function($token) {
                $token->revoke(); // revoking each toke to logout from all devices
            });
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


}
