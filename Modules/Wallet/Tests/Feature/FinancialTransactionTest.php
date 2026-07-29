<?php

namespace Modules\Wallet\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Modules\Ledger\Models\AccountType;
use Modules\Ledger\Models\Account;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Services\FinancialTransactionService;
use Modules\Ledger\Models\LedgerEntry;

class FinancialTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_adjustment_idempotency()
    {
        $admin = User::factory()->create();

        $acctType = AccountType::create([
            'uuid' => 'acct-type-1',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $offset = Account::create([
            'uuid' => 'offset-1',
            'code' => 'OFFSET',
            'name' => 'Offset Account',
            'account_type_id' => $acctType->id,
        ]);

        $walletAccount = Account::create([
            'uuid' => 'wallet-acct-1',
            'code' => 'WALLET-USER-1',
            'name' => 'User Wallet Account',
            'account_type_id' => $acctType->id,
        ]);

        $user = User::factory()->create();

        $wallet = Wallet::create([
            'uuid' => 'wallet-1',
            'user_id' => $user->id,
            'ledger_account_id' => $walletAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        config(['wallet.ledger_offset_account_id' => $offset->id]);

        $service = $this->app->make(FinancialTransactionService::class);

        $payload = [
            'wallet_id' => $wallet->id,
            'amount' => 1000,
            'direction' => 'credit',
            'transaction_type' => 'adjustment',
            'reason' => 'test adjustment',
            'idempotency_key' => 'adj-req-1',
            'created_by' => $admin->id,
        ];

        $first = $service->createAdjustment($payload);
        $second = $service->createAdjustment($payload);

        $this->assertEquals($first->id, $second->id, 'Idempotent call should return same financial transaction');

        $entries = LedgerEntry::query()->where('ledger_transaction_id', $first->ledger_transaction_id)->get();
        $this->assertCount(2, $entries, 'Ledger transaction must have two entries');
    }
}
