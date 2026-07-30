<?php

namespace Modules\UserManagement\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Models\Advertisement;
use Modules\Ledger\Models\Account;
use Modules\Ledger\Models\AccountType;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletBalance;
use Modules\UserManagement\Models\ActivityLog;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_and_activity_are_returned_from_backend(): void
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $user->assignRole('User');

        $accountType = AccountType::create([
            'uuid' => 'account-type-1',
            'code' => 'wallet-default',
            'name' => 'Wallet Default',
            'description' => 'Default wallet account type',
            'metadata' => [],
        ]);

        $ledgerAccount = Account::create([
            'uuid' => 'account-1',
            'code' => 'WALLET-1',
            'name' => 'Wallet Ledger Account',
            'account_type_id' => $accountType->id,
            'currency' => 'IRR',
            'status' => 'active',
            'metadata' => [],
        ]);

        $wallet = Wallet::create([
            'uuid' => 'wallet-1',
            'user_id' => $user->id,
            'wallet_type' => 'default',
            'ledger_account_id' => $ledgerAccount->id,
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
            'status' => NegotiationStatus::Active,
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
            ->assertJsonFragment(['label' => 'آگهی‌های فعال', 'value' => '1'])
            ->assertJsonFragment(['label' => 'موجودی کیف پول', 'value' => '1,230,000']);

        $activityResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/activity');
        $activityResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }
}
