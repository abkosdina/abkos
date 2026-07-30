<?php

namespace Modules\Advertisements\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Models\Advertisement;
use Tests\TestCase;

class AdvertisementDetailPageViewMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_ads_detail_page_load_records_view(): void
    {
        $user = User::factory()->create();

        $advertisement = Advertisement::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'advertisement_number' => 'ADV-DETAIL-1',
            'user_id' => $user->id,
            'seller_user_id' => $user->id,
            'title' => 'Detail Page View Test',
            'slug' => 'detail-page-view-test',
            'short_description' => 'Short desc',
            'description' => 'Detailed description.',
            'status' => 'Published',
            'visibility' => 'Public',
            'priority' => 0,
            'province_id' => null,
            'city_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->get('/ads/detail?id=' . $advertisement->id);

        $response->assertStatus(200);

        $this->assertDatabaseHas('advertisement_views', [
            'advertisement_id' => $advertisement->id,
            'user_id' => $user->id,
        ]);
    }
}
