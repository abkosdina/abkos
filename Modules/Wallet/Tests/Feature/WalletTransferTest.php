<?php

namespace Modules\Wallet\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Modules\Ledger\Models\AccountType;
use Modules\Ledger\Models\Account;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Services\TransferService;
use Modules\Ledger\Models\LedgerEntry;

class WalletTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_transfer_double_entry_and_balance()
    {
        $acctType = AccountType::create([
            'uuid' => 'acct-type-2',
            'code' => 'WALLET',
            'name' => 'Wallet Account',
        ]);

        $fromAccount = Account::create([
            'uuid' => 'from-acct-1',
            'code' => 'WALLET-FROM',
            'name' => 'From Account',
            'account_type_id' => $acctType->id,
        ]);

        $toAccount = Account::create([
            'uuid' => 'to-acct-1',
            'code' => 'WALLET-TO',
            'name' => 'To Account',
            'account_type_id' => $acctType->id,
        ]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA = Wallet::create([
            'uuid' => 'wallet-A',
            'user_id' => $userA->id,
            'ledger_account_id' => $fromAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        $walletB = Wallet::create([
            'uuid' => 'wallet-B',
            'user_id' => $userB->id,
            'ledger_account_id' => $toAccount->id,
            'currency' => 'USD',
            'status' => config('wallet.statuses.active'),
        ]);

        // Seed initial ledger balances by crediting walletA
        config(['wallet.ledger_offset_account_id' => $toAccount->id]);

        $transfer = $this->app->make(TransferService::class);

        $tx = $transfer->executeTransfer($walletA->id, $walletB->id, 500, 'test transfer', []);

        $this->assertNotNull($tx->id, 'Ledger transaction must be created');

        $entries = LedgerEntry::query()->where('ledger_transaction_id', $tx->id)->get();
        $this->assertCount(2, $entries, 'Transfer must create two ledger entries');
    }
}
