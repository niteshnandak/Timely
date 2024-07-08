<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\password_reset_tokens;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function validateForgotPasswordUser(Request $request)
    {
       $token = $request->input('token');
       $user = DB::table('password_reset_tokens')->where('token',$token)->first();

       if($user){
        return response()->json(["toaster_info"=> Config::get('message.success.reset_validate_success')], 200);
       }else{
        return response()->json(["message"=>Config::get('message.error.reset_validate_error'),"token"=>$token,"User"=>$user],422);
       }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => ['required','email'],
        ]);

        $email = $request->input('email');

        // Check if the user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['toaster_error'=>Config::get('message.error.forgot_password_user_error'),"user"=> $request->all()],422);
        }

        // Generate a token for password reset
        $token = Str::random(60);

        DB::insert('insert into password_reset_tokens (email,token) values(?,?)',[$email['email'],$token]);

        $id = $user->id;
        $fullname = $user->first_name ." ".$user->last_name;

        // Generate password reset link
        $url = url("http://localhost:4200/reset-password/{$token}");

        // Send email with the reset link
        if(Mail::to($email)->send(new ResetPasswordMail($url, $token, $id, $fullname))){
            return response()->json([
                "toaster_success"=>Config::get('message.success.forgot_password_success')],201);
        }else{
            return response()->json([
                "toaster_error"=>Config::get('message.error.forgot_password_error')], 405);
        }
    }

    public function forgotSetPassword(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);

        $password = $request->input('data.password');
        $token = $request->input('token');

        // Validate the token against the password_reset_tokens table
        $userData = DB::table('password_reset_tokens')->where('token',$token)->first();

        $password = Hash::make($password);

        if(User::where('email',$userData->email)->update(['password'=> $password, 'updated_at' => Carbon::now()])){
            DB::table('password_reset_tokens')->where('email', $userData->email)->delete();
            return response()->json(["toaster_success"=>Config::get('message.success.forgot_reset_password_success')],200);
        }else{
            return response()->json(["toaster_error"=>Config::get('message.error.forgot_reset_password_error')],422);
        }

    }
}
