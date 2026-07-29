<?php

namespace Modules\UserManagement\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Models\Negotiation;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletBalance;
use Modules\UserManagement\Models\ActivityLog;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_and_activity_are_returned_from_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $wallet = Wallet::create([
            'uuid' => 'wallet-1',
            'user_id' => $user->id,
            'name' => 'Main Wallet',
            'currency' => 'IRR',
            'status' => 'active',
            'metadata' => [],
        ]);
        WalletBalance::create([
            'uuid' => 'wallet-balance-1',
            'wallet_id' => $wallet->id,
            'available_balance' => 1230000,
            'blocked_balance' => 0,
            'pending_balance' => 0,
            'total_balance' => 1230000,
            'metadata' => [],
        ]);

        Advertisement::create([
            'uuid' => 'ad-1',
            'advertisement_number' => 'ADV-1',
            'title' => 'Test Ad',
            'slug' => 'test-ad',
            'status' => 'Published',
            'visibility' => 'Public',
            'user_id' => $user->id,
            'seller_user_id' => $user->id,
            'price' => 1000000,
            'currency' => 'IRR',
            'priority' => 1,
            'views_count' => 12,
            'contacts_count' => 3,
        ]);

        Negotiation::create([
            'uuid' => 'neg-1',
            'advertisement_id' => 1,
            'buyer_id' => $user->id,
            'seller_id' => $user->id,
            'status' => 'Open',
        ]);

        ActivityLog::create([
            'uuid' => 'activity-1',
            'user_id' => $user->id,
            'causer_id' => $user->id,
            'event' => 'advertisement.created',
            'subject_type' => Advertisement::class,
            'subject_id' => 1,
            'properties' => ['message' => 'Created'],
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/stats');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.active_ads', 1)
            ->assertJsonPath('data.wallet_balance', '1,230,000');

        $activityResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/activity');
        $activityResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }
}
