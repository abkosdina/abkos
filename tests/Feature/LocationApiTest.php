<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\Province;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // seed the full database so provinces/cities exist
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Cache::flush();
    }

    public function test_get_all_provinces_returns_31()
    {
        $response = $this->getJson('/api/v1/locations/provinces');
        $response->assertStatus(200);
        $this->assertCount(31, $response->json('data'));
    }

    public function test_get_cities_for_tehran_province()
    {
        $provinceId = Province::where('name', 'تهران')->value('id');
        $this->assertNotNull($provinceId);

        $response = $this->getJson("/api/v1/locations/provinces/{$provinceId}/cities");
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->contains(fn($c) => $c['name'] === 'تهران'));
    }

    public function test_search_cities_returns_matching_results()
    {
        // province 1 is آذربایجان شرقی with city 'تبریز'
        $provinceId = 1;
        $response = $this->getJson("/api/v1/locations/provinces/{$provinceId}/cities?search=تبریز");
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('تبریز', $data[0]['name']);
    }

    public function test_provinces_are_cached_after_request()
    {
        $cacheKey = 'locations:provinces';
        Cache::forget($cacheKey);
        $this->assertFalse(Cache::has($cacheKey));

        $this->getJson('/api/v1/locations/provinces')->assertStatus(200);
        $this->assertTrue(Cache::has($cacheKey));
    }
}
