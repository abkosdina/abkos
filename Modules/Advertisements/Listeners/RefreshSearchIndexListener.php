<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Advertisements\Events\AdvertisementPublished;
use Illuminate\Support\Facades\Bus;

class RefreshSearchIndexListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AdvertisementPublished $event): void
    {
        try {
            if (class_exists('\\Modules\\Search\\Jobs\\IndexAdvertisementJob')) {
                Bus::dispatch(new \Modules\Search\Jobs\IndexAdvertisementJob($event->advertisement->id));
                return;
            }

            if (class_exists('\\Modules\\Advertisements\\Jobs\\IndexAdvertisementJob')) {
                Bus::dispatch(new \Modules\Advertisements\Jobs\IndexAdvertisementJob($event->advertisement->id));
                return;
            }

            Log::info('RefreshSearchIndexListener fallback', ['advertisement_id' => $event->advertisement->id]);
        } catch (\Throwable $e) {
            Log::error('RefreshSearchIndexListener failed: ' . $e->getMessage());
        }
    }
}
