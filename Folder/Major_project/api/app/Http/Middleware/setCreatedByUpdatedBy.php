<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class setCreatedByUpdatedBy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $request->merge([
                'created_by' => $user->user_id,
                'updated_by' => $user->user_id,
                'organisation_id' => $user->organisation_id   
            ]);
        }

        return $next($request);
    }
}
