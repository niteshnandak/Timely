<?php

namespace App\Http\Middleware;

use App\Models\Oauth_access_tokens;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;


class AuthenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{$user =Auth::user();
        // Check if the token is present in the request headers
        //    $token = Session::get('bearer_token');
        $requesttoken = Session::get('token');

        $id = $user->id;

        $storedtoken = Oauth_access_tokens::where('user_id',$id)->get();

        if (!$storedtoken) {
            // return response()->json(['error' => 'Unauthorized'], 401);
            return redirect()->route('showlogin');
        }}catch(Exception $e){
            // return response()->json(['error' => 'Unauthorized'], 401);
            return redirect()->route('showlogin');
        }

        // If the user is authenticated, proceed with the request
        return $next($request);
   }
}
