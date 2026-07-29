<?php

namespace Modules\Advertisements\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (isset($user->is_suspended) && $user->is_suspended) {
            return response()->json(['success' => false, 'message' => 'Your account is suspended.'], 403);
        }

        if (isset($user->suspended_at) && $user->suspended_at) {
            return response()->json(['success' => false, 'message' => 'Your account is suspended.'], 403);
        }

        return $next($request);
    }
}
