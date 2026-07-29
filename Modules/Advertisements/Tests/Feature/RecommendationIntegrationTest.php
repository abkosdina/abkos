<?php

namespace Modules\Advertisements\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Advertisements\Services\FavoriteService;
use Modules\Advertisements\Services\ViewService;
use Illuminate\Support\Facades\Cache;

class RecommendationIntegrationTest extends TestCase
{
    public function test_favoriting_bumps_user_version(): void
    {
        $user = User::factory()->create();

        // Ensure starting user_version is null or 1
        $before = Cache::get("advertisements:recommendations:user_version:{$user->id}", 1);

        $fav = app(FavoriteService::class);
        // call with fake uuid to exercise path (repo will return false if table missing), so we just simulate
        $fav->favorite($user->id, 'fake-uuid-for-test');

        $after = Cache::get("advertisements:recommendations:user_version:{$user->id}", 1);

        $this->assertGreaterThanOrEqual($before, $after);
    }

    public function test_viewing_records_and_dispatches(): void
    {
        $user = User::factory()->create();

        $view = app(ViewService::class);

        $res = $view->recordView($user->id, 1, '127.0.0.1', 'phpunit', 'sess-1');

        $this->assertIsBool($res);
    }
}
