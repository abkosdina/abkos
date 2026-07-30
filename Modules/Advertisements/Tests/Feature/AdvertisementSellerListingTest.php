<?php

namespace Modules\Advertisements\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Enums\AdvertisementVisibility;
use Modules\Advertisements\Models\Advertisement;
use Tests\TestCase;

class AdvertisementSellerListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_advertisements_by_seller_id_and_exclude_current_advertisement(): void
    {
        $seller = User::factory()->create();

        $currentAd = Advertisement::factory()->create([
            'seller_user_id' => $seller->id,
            'user_id' => $seller->id,
            'status' => AdvertisementStatus::Published,
            'visibility' => AdvertisementVisibility::Public,
        ]);

        Advertisement::factory()->count(3)->create([
            'seller_user_id' => $seller->id,
            'user_id' => $seller->id,
            'status' => AdvertisementStatus::Published,
            'visibility' => AdvertisementVisibility::Public,
        ]);

        $response = $this->getJson('/api/advertisements?sellerId=' . $seller->id . '&exclude=' . $currentAd->id . '&per_page=5');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 3);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonMissing(['id' => $currentAd->id]);
    }
}
