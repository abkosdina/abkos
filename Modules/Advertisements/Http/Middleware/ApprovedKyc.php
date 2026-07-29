<?php

namespace Modules\Advertisements\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovedKyc
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // If user has a direct kyc_status attribute, prefer it
        if (isset($user->kyc_status)) {
            if ($user->kyc_status !== 'approved') {
                return response()->json(['success' => false, 'message' => 'KYC not approved.'], 403);
            }
            return $next($request);
        }

        // Fallback: check kyc_profiles table if present
        if (Schema::hasTable('kyc_profiles')) {
            $record = DB::table('kyc_profiles')->where('user_id', $user->id)->orderBy('id', 'desc')->first();
            if (! $record || ($record->status ?? null) !== 'approved') {
                return response()->json(['success' => false, 'message' => 'KYC not approved.'], 403);
            }
            return $next($request);
        }

        // If no KYC system present, allow by default
        return $next($request);
    }
}
