<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-Token') ?: $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized - no token'], 401);
        }

        $user = \App\Models\User::where('api_token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized - invalid token'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
