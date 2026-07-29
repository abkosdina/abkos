<?php

namespace Modules\Advertisements\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementViewMiddlewareTest extends TestCase
{
    public function test_advertisement_detail_request_records_view(): void
    {
        $user = User::factory()->create();

        $advertisement = Advertisement::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'advertisement_number' => 'ADV-VIEW-1',
            'user_id' => $user->id,
            'title' => 'View Tracking Test',
            'slug' => 'view-tracking-test',
            'short_description' => 'Short desc',
            'description' => 'Detailed description.',
            'status' => 'Published',
            'visibility' => 'Public',
            'priority' => 0,
            'province_id' => null,
            'city_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/advertisements/' . $advertisement->uuid);

        $response->assertStatus(200);
        $response->assertJson([ 'views' => 1 ]);

        $this->assertDatabaseHas('advertisement_views', [
            'advertisement_id' => $advertisement->id,
            'user_id' => $user->id,
        ]);
    }
}
