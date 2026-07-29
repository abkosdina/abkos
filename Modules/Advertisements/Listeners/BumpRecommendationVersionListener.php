<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class BumpRecommendationVersionListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        try {
            $key = 'advertisements:recommendations:version';
            if (! Cache::has($key)) {
                Cache::forever($key, 1);
            }
            Cache::increment($key);
        } catch (\Throwable $e) {
            \Log::error('BumpRecommendationVersionListener failed: ' . $e->getMessage());
        }
    }
}
