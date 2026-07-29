<?php

namespace Modules\Advertisements\Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Modules\Advertisements\Services\AdvertisementRecommendationService;

class AdvertisementRecommendationCachingTest extends TestCase
{
    public function test_recommendation_is_cached(): void
    {
        $service = app(AdvertisementRecommendationService::class);

        Cache::shouldReceive('get')->with('advertisements:recommendations:version', 1)->once()->andReturn(1);
        Cache::shouldReceive('get')->with('advertisements:recommendations:user_version:1', 1)->once()->andReturn(1);
        Cache::shouldReceive('remember')->once()->andReturn(collect());

        $result = $service->recommendForUser(1, []);

        $this->assertIsIterable($result);
    }
}
