<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\Advertisements\Events\AdvertisementUpdated;

class ClearCacheListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AdvertisementUpdated $event): void
    {
        try {
            Cache::forget("advertisement:{$event->advertisement->id}");
            Log::info('Advertisement cache invalidated', ['advertisement_id' => $event->advertisement->id]);
        } catch (\Throwable $e) {
            Log::error('ClearCacheListener failed: ' . $e->getMessage());
        }
    }
}
