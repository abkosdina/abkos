<?php

namespace Modules\Negotiation\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Models\Advertisement;
use Tests\TestCase;

class NegotiationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_negotiation_creation_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/advertisements/does-not-matter/negotiations', []);

        $response->assertStatus(401);
    }
}
