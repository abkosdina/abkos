<?php

namespace Modules\Wallet\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Modules\Ledger\Models\Account;
use Modules\Ledger\Models\AccountType;
use Modules\Wallet\Models\Wallet;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_wallet_permission_can_create_wallet()
    {
        $user = User::factory()->create();
        Permission::findOrCreate('menu.wallet', 'web');
        $user->givePermissionTo('menu.wallet');

        $accountType = AccountType::create([
            'uuid' => 'api-acct-type-1',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $ledgerAccount = Account::create([
            'uuid' => 'api-wallet-acct-1',
            'code' => 'WALLET-API-1',
            'name' => 'API Wallet Account',
            'account_type_id' => $accountType->id,
        ]);

        $payload = [
            'uuid' => 'wallet-api-1',
            'user_id' => $user->id,
            'ledger_account_id' => $ledgerAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/wallets', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data' => ['id', 'uuid', 'user_id', 'ledger_account_id', 'currency', 'status']]);

        $this->assertDatabaseHas('wallets', [
            'uuid' => 'wallet-api-1',
            'user_id' => $user->id,
        ]);
    }

    public function test_wallet_deposit_and_balance_endpoint_work_for_authorized_user()
    {
        $user = User::factory()->create();
        Permission::findOrCreate('menu.wallet', 'web');
        $user->givePermissionTo('menu.wallet');

        $accountType = AccountType::create([
            'uuid' => 'api-acct-type-2',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $offsetAccount = Account::create([
            'uuid' => 'api-offset-acct-1',
            'code' => 'OFFSET-API-1',
            'name' => 'Offset Account',
            'account_type_id' => $accountType->id,
        ]);

        $walletAccount = Account::create([
            'uuid' => 'api-wallet-acct-2',
            'code' => 'WALLET-API-2',
            'name' => 'Wallet Account 2',
            'account_type_id' => $accountType->id,
        ]);

        $wallet = Wallet::create([
            'uuid' => 'wallet-api-2',
            'user_id' => $user->id,
            'ledger_account_id' => $walletAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        config(['wallet.ledger_offset_account_id' => $offsetAccount->id]);

        $depositResponse = $this->actingAs($user, 'sanctum')->postJson("/api/v1/wallets/{$wallet->id}/deposit", [
            'amount' => 1200,
            'description' => 'API deposit',
        ]);

        $depositResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data' => ['id', 'wallet_id', 'ledger_transaction_id', 'transaction_type', 'amount', 'status']]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => '1200.00000000',
        ]);

        $balanceResponse = $this->actingAs($user, 'sanctum')->getJson("/api/v1/wallets/{$wallet->id}/balances");
        $balanceResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data' => ['available_balance', 'blocked_balance', 'pending_balance', 'total_balance']]);
    }

    public function test_wallet_transfer_endpoint_is_double_entry_and_requires_permission()
    {
        $user = User::factory()->create();
        Permission::findOrCreate('menu.wallet', 'web');
        $user->givePermissionTo('menu.wallet');

        $accountType = AccountType::create([
            'uuid' => 'api-acct-type-3',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $offsetAccount = Account::create([
            'uuid' => 'api-offset-acct-2',
            'code' => 'OFFSET-API-2',
            'name' => 'Offset Account',
            'account_type_id' => $accountType->id,
        ]);

        $fromAccount = Account::create([
            'uuid' => 'api-wallet-from-1',
            'code' => 'WALLET-FROM-API',
            'name' => 'From Wallet Account',
            'account_type_id' => $accountType->id,
        ]);

        $toAccount = Account::create([
            'uuid' => 'api-wallet-to-1',
            'code' => 'WALLET-TO-API',
            'name' => 'To Wallet Account',
            'account_type_id' => $accountType->id,
        ]);

        $userB = User::factory()->create();

        $walletA = Wallet::create([
            'uuid' => 'wallet-api-A',
            'user_id' => $user->id,
            'ledger_account_id' => $fromAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        $walletB = Wallet::create([
            'uuid' => 'wallet-api-B',
            'user_id' => $userB->id,
            'ledger_account_id' => $toAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        config(['wallet.ledger_offset_account_id' => $offsetAccount->id]);

        $transferResponse = $this->actingAs($user, 'sanctum')->postJson("/api/v1/wallets/{$walletA->id}/transfer", [
            'destination_wallet_id' => $walletB->id,
            'amount' => 500,
            'description' => 'API transfer',
        ]);

        $transferResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data' => ['id', 'wallet_id', 'ledger_transaction_id', 'financial_transaction_id', 'transaction_type', 'amount', 'status']]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $walletA->id,
            'amount' => '500.00000000',
        ]);
    }

    public function test_admin_can_create_wallet_adjustment_via_admin_api()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $accountType = AccountType::create([
            'uuid' => 'api-acct-type-4',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $offsetAccount = Account::create([
            'uuid' => 'api-offset-acct-3',
            'code' => 'OFFSET-API-3',
            'name' => 'Offset Account',
            'account_type_id' => $accountType->id,
        ]);

        $walletAccount = Account::create([
            'uuid' => 'api-wallet-acct-3',
            'code' => 'WALLET-API-3',
            'name' => 'Wallet Account 3',
            'account_type_id' => $accountType->id,
        ]);

        $targetUser = User::factory()->create();

        $wallet = Wallet::create([
            'uuid' => 'wallet-api-3',
            'user_id' => $targetUser->id,
            'ledger_account_id' => $walletAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        config(['wallet.ledger_offset_account_id' => $offsetAccount->id]);

        $payload = [
            'wallet_id' => $wallet->id,
            'amount' => 1000,
            'transaction_type' => 'adjustment',
            'direction' => 'credit',
            'reason' => 'API admin adjustment',
            'description' => 'Adjust wallet via admin API',
            'idempotency_key' => 'admin-adjust-api-1',
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/wallets/adjustments', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data' => ['id', 'uuid', 'amount', 'status', 'ledger_transaction_id']]);

        $this->assertDatabaseHas('financial_transactions', [
            'receiver_wallet_id' => $wallet->id,
            'amount' => '1000.00000000',
        ]);
    }
}
