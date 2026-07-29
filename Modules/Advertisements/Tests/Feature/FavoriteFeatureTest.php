<?php

namespace Modules\Advertisements\Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class FavoriteFeatureTest extends TestCase
{
    public function test_favorite_endpoints_require_authentication(): void
    {
        $response = $this->postJson('/api/advertisements/fake-uuid/favorite');
        $response->assertStatus(401);
    }
}
