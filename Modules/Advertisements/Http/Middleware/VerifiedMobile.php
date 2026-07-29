<?php

namespace Modules\Advertisements\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VerifiedMobile
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Prefer explicit mobile verification column
        if (isset($user->mobile_verified_at)) {
            if (empty($user->mobile_verified_at)) {
                return response()->json(['success' => false, 'message' => 'Mobile number not verified.'], 403);
            }
        } else {
            // fallback: require mobile exists
            if (empty($user->mobile)) {
                return response()->json(['success' => false, 'message' => 'Mobile number required.'], 403);
            }
        }

        return $next($request);
    }
}
