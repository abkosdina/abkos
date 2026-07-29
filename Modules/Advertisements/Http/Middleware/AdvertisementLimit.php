<?php

namespace Modules\Advertisements\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $limit = Config::get('advertisements.limits.active_per_user', 10);

        $count = Advertisement::query()
            ->ownedBy($user->id)
            ->active()
            ->select('id')
            ->limit($limit)
            ->get()
            ->count();

        if ($count >= $limit) {
            return response()->json(['success' => false, 'message' => sprintf('Advertisement limit reached (%d active ads).', $limit)], 403);
        }

        return $next($request);
    }
}
