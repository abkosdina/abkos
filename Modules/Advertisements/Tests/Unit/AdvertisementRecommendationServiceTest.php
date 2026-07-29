<?php

namespace Modules\Advertisements\Tests\Unit;

use Tests\TestCase;
use Modules\Advertisements\Services\AdvertisementRecommendationService;

class AdvertisementRecommendationServiceTest extends TestCase
{
    public function test_service_resolves_from_container(): void
    {
        $this->assertTrue(app()->bound(AdvertisementRecommendationService::class));
        $service = app(AdvertisementRecommendationService::class);
        $this->assertInstanceOf(AdvertisementRecommendationService::class, $service);
    }
}
