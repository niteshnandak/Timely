<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Log;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as FacadesLog;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email =  $request->data['email'];
        $password = $request->data['password'];

        if( Auth::attempt(['email' => $email, 'password' => $password])){


            $user = Auth::user();

            // Generate access token
            $token = $user->createToken('myToken')->accessToken;

            // Redirect to dashboard page with token
            return response()->json([
                "status" => 200,
                "user" => $user,
                "token" => $token,
                "toaster_success"=>Config::get('message.success.login_success')],200);
        }else{
            return response()->json([
                "status" => 405,
                "toaster_error"=>Config::get('message.error.login_error')],405);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => Config::get('message.error.token_error')], 401);
        }

        // Retrieve the token ID (JTI) from the token
        $parser = new Parser(new JoseEncoder());
        $tokenId = $parser->parse($token)->claims()->get('jti');

        // Revoke the token
        if(Auth::user()->tokens()->where('id', $tokenId)->first()->revoke()){
            return response()->json([
                "toaster_success"=>Config::get('message.success.logout_success')],200);
        }else{
            return response()->json([
                "toaster_error"=>Config::get('message.error.logout_error')],405);
        }
    }
}
