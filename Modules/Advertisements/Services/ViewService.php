<?php

namespace Modules\Advertisements\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Events\AdvertisementViewed;

class ViewService
{
    public function __construct()
    {
    }

    /**
     * Record every view request and dispatch AdvertisementViewed.
     * If the views_count column exists, increment it.
     */
    public function recordView(?int $userId, int $advertisementId, ?string $ip = null, ?string $device = null, ?string $sessionId = null): bool
    {
        // persist view
        if (Schema::hasTable('advertisement_views')) {
            DB::table('advertisement_views')->insert([
                'user_id' => $userId,
                'advertisement_id' => $advertisementId,
                'ip' => $ip,
                'device' => $device,
                'session_id' => $sessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Increment views_count on advertisement only when the column exists
        if (Schema::hasTable('advertisements') && Schema::hasColumn('advertisements', 'views_count')) {
            DB::table('advertisements')->where('id', $advertisementId)->increment('views_count');
        }

        // Dispatch event for listeners to invalidate caches / update counters
        event(new AdvertisementViewed($userId, $advertisementId, $ip, $device));

        return true;
    }
}
