<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {

        if (! $request->bearerToken()) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            Auth::setUser($user);
        }
        return $next($request);
    }
}
