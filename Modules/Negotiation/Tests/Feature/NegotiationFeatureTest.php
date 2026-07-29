<?php

namespace Modules\Negotiation\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegotiationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_negotiation_index_endpoint_is_protected(): void
    {
        $response = $this->getJson('/api/v1/negotiations');

        $response->assertStatus(401);
    }
}
