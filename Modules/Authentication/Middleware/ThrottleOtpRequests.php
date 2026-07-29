<?php

namespace Modules\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;

class ThrottleOtpRequests
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'otp:' . $request->ip();

        if (rate_limit_exceeded($key, 5, 1)) {
            return response()->json(['message' => 'Too many requests.'], 429);
        }

        return $next($request);
    }
}
