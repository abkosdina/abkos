<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Advertisements\Services\AdvertisementRecommendationService;

class InvalidateUserRecommendationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected AdvertisementRecommendationService $recommendation)
    {
    }

    public function handle($event): void
    {
        try {
            if (property_exists($event, 'userId') && $event->userId) {
                $this->recommendation->invalidateUserCache($event->userId);
            }
        } catch (\Throwable $e) {
            \Log::error('InvalidateUserRecommendationListener failed: ' . $e->getMessage());
        }
    }
}
