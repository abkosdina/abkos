<?php

namespace Modules\Advertisements\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Advertisements\Models\Advertisement;
class AdvertisementSubmitTest extends TestCase
{

    public function test_submit_advertisement(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        $ad = Advertisement::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'advertisement_number' => 'ADV-TEST-1',
            'user_id' => $user->id,
            'title' => 'To Submit',
            'slug' => 'to-submit',
            'short_description' => 'short',
            'description' => 'long',
            'status' => 'Draft',
            'visibility' => 'Public',
            'priority' => 0,
            'province_id' => 1,
            'city_id' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/advertisements/user/' . $ad->uuid . '/submit');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }
}
