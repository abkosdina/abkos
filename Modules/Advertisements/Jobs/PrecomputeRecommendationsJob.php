<?php

namespace Modules\Advertisements\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Advertisements\Services\AdvertisementRecommendationService;

class PrecomputeRecommendationsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array|null $userIds = null)
    {
    }

    public function handle(AdvertisementRecommendationService $recommendation): void
    {
        // if userIds provided, precompute for those users, otherwise skip
        if (! $this->userIds) {
            return;
        }

        foreach ($this->userIds as $userId) {
            $recommendation->recommendForUser($userId);
        }
    }
}
