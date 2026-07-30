<?php

namespace Modules\Advertisements\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Services\ViewService;

class RecordAdvertisementView
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        $uuid = $request->route('uuid') ?? $request->query('id');
        if ($uuid) {
            $advertisement = is_numeric($uuid)
                ? Advertisement::select('id')->find($uuid)
                : Advertisement::where('uuid', $uuid)->select('id')->first();

            if ($advertisement) {
                $userId = $request->user()?->id;
                $sessionId = null;

                if ($request->hasSession() && $request->session()->isStarted()) {
                    $sessionId = $request->session()->getId();
                }

                app(ViewService::class)->recordView(
                    $userId,
                    $advertisement->id,
                    $request->ip(),
                    $request->header('User-Agent'),
                    $sessionId,
                );
            }
        }

        return $next($request);
    }
}
