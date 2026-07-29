<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Repositories\Interfaces\WalletRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\FinancialTransactionRepositoryInterface;
use Modules\Ledger\Services\LedgerService;
use Modules\Ledger\Enums\LedgerTransactionType;

class TransferService
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected LedgerService $ledgerService,
        protected BalanceService $balanceService,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected FinancialTransactionRepositoryInterface $financialTransactionRepository,
    ) {
    }

    public function executeTransfer(int|string $sourceWalletId, int|string $destinationWalletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        $sourceWallet = $this->walletRepository->find($sourceWalletId);
        $destinationWallet = $this->walletRepository->find($destinationWalletId);

        if (!$sourceWallet || !$destinationWallet) {
            throw new \InvalidArgumentException('Both source and destination wallets must exist.');
        }

        if ($sourceWallet->status !== config('wallet.statuses.active')) {
            throw new \RuntimeException('Source wallet is not active.');
        }

        if ($destinationWallet->status !== config('wallet.statuses.active')) {
            throw new \RuntimeException('Destination wallet is not active.');
        }

        $ledgerTransaction = $this->ledgerService->createTransaction([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => LedgerTransactionType::TRANSFER->value,
            'description' => $description ?? sprintf('Wallet transfer %s -> %s', $sourceWalletId, $destinationWalletId),
            'metadata' => $metadata,
        ], [
            [
                'account_id' => $sourceWallet->ledger_account_id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Wallet transfer debit',
                'metadata' => array_merge($metadata, ['target_wallet_id' => $destinationWalletId]),
            ],
            [
                'account_id' => $destinationWallet->ledger_account_id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Wallet transfer credit',
                'metadata' => array_merge($metadata, ['source_wallet_id' => $sourceWalletId]),
            ],
        ]);

        $financialTransaction = $this->financialTransactionRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => LedgerTransactionType::TRANSFER->value,
            'status' => $ledgerTransaction->status,
            'amount' => $amount,
            'currency' => $sourceWallet->currency,
            'sender_wallet_id' => $sourceWallet->id,
            'receiver_wallet_id' => $destinationWallet->id,
            'reference_type' => null,
            'reference_id' => null,
            'idempotency_key' => null,
            'metadata' => array_merge($metadata, ['transfer' => true, 'source_wallet_id' => $sourceWalletId, 'destination_wallet_id' => $destinationWalletId]),
            'ledger_transaction_id' => $ledgerTransaction->id,
            'created_by' => auth()->id(),
        ]);

        $sourceTransaction = $this->walletTransactionRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'wallet_id' => $sourceWallet->id,
            'ledger_transaction_id' => $ledgerTransaction->id,
            'financial_transaction_id' => $financialTransaction->id,
            'transaction_type' => LedgerTransactionType::TRANSFER->value,
            'amount' => $amount,
            'status' => $ledgerTransaction->status,
            'description' => $description,
            'metadata' => array_merge($metadata, ['direction' => 'debit', 'target_wallet_id' => $destinationWalletId]),
        ]);

        $this->walletTransactionRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'wallet_id' => $destinationWallet->id,
            'ledger_transaction_id' => $ledgerTransaction->id,
            'financial_transaction_id' => $financialTransaction->id,
            'transaction_type' => LedgerTransactionType::TRANSFER->value,
            'amount' => $amount,
            'status' => $ledgerTransaction->status,
            'description' => $description,
            'metadata' => array_merge($metadata, ['direction' => 'credit', 'source_wallet_id' => $sourceWalletId]),
        ]);

        $this->balanceService->reconcileWalletBalance($sourceWalletId);
        $this->balanceService->reconcileWalletBalance($destinationWalletId);

        return $sourceTransaction;
    }
}
