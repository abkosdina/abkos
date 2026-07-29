<?php

namespace Modules\Wallet\Services;

use Modules\Ledger\Enums\LedgerTransactionType;
use Modules\Ledger\Services\LedgerService;
use Modules\Wallet\Enums\WalletStatus;
use Modules\Wallet\Enums\WalletTransactionType;
use Modules\Wallet\Repositories\Interfaces\WalletBalanceRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletTransactionRepositoryInterface;

class WalletService
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected WalletBalanceRepositoryInterface $balanceRepository,
        protected WalletTransactionRepositoryInterface $transactionRepository,
        protected \Modules\Wallet\Repositories\Interfaces\FinancialTransactionRepositoryInterface $financialTransactionRepository,
        protected WalletLockService $lockService,
        protected WalletLimitService $limitService,
        protected LedgerService $ledgerService,
        protected BalanceService $balanceService,
        protected TransferService $transferService,
    ) {
    }

    public function listWallets(): array
    {
        return $this->walletRepository->all();
    }

    public function createWallet(array $data): object
    {
        $payload = array_merge($data, [
            'uuid' => $data['uuid'] ?? \Illuminate\Support\Str::uuid()->toString(),
            'status' => $data['status'] ?? config('wallet.statuses.active'),
        ]);

        $wallet = $this->walletRepository->create($payload);

        $this->balanceService->reconcileWalletBalance($wallet->id);

        return $wallet;
    }

    public function getWallet(int|string $id): ?object
    {
        return $this->walletRepository->find($id);
    }

    public function getWalletBalance(int|string $id): object
    {
        return $this->balanceService->getWalletBalance($id);
    }

    public function listTransactions(int|string $walletId, array $filters = []): array
    {
        return $this->transactionRepository->filterByWallet($walletId, $filters);
    }

    public function deposit(int|string $walletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->processWalletTransaction($walletId, 'deposit', $amount, $description, $metadata);
    }

    public function withdraw(int|string $walletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->processWalletTransaction($walletId, WalletTransactionType::WITHDRAW->value, $amount, $description, $metadata);
    }

    public function transfer(int|string $walletId, int|string $destinationWalletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->transferService->executeTransfer($walletId, $destinationWalletId, $amount, $description, $metadata);
    }

    public function lockBalance(int|string $walletId, float $amount, string $reason, ?string $notes = null, \DateTime|string|null $expiresAt = null): object
    {
        return $this->lockService->lockBalance($walletId, $amount, $reason, $notes, $expiresAt);
    }

    public function unlockBalance(int|string $lockId): bool
    {
        return $this->lockService->unlockBalance($lockId);
    }

    public function freezeWallet(int|string $walletId): object
    {
        return $this->updateWalletStatus($walletId, WalletStatus::FROZEN->value);
    }

    public function closeWallet(int|string $walletId): object
    {
        return $this->updateWalletStatus($walletId, WalletStatus::CLOSED->value);
    }

    public function updateWalletStatus(int|string $walletId, string $status): object
    {
        $wallet = $this->walletRepository->find($walletId);

        if (!$wallet) {
            throw new \InvalidArgumentException('Wallet not found.');
        }

        $this->walletRepository->update($wallet->id, ['status' => $status]);

        return $this->walletRepository->find($wallet->id);
    }

    protected function processWalletTransaction(int|string $walletId, string $transactionType, float $amount, ?string $description, array $metadata): object
    {
        $wallet = $this->walletRepository->find($walletId);

        if (!$wallet) {
            throw new \InvalidArgumentException('Wallet not found.');
        }

        if ($wallet->status !== WalletStatus::ACTIVE->value) {
            throw new \RuntimeException('Wallet is not active.');
        }

        $offsetAccountId = config('wallet.ledger_offset_account_id');

        if (!$offsetAccountId) {
            throw new \RuntimeException('Wallet ledger offset account is not configured.');
        }

        $ledgerEntries = match ($transactionType) {
            WalletTransactionType::DEPOSIT->value => [
                [
                    'account_id' => $offsetAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Wallet deposit source',
                    'metadata' => array_merge($metadata, ['wallet_id' => $walletId]),
                ],
                [
                    'account_id' => $wallet->ledger_account_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Wallet deposit',
                    'metadata' => array_merge($metadata, ['wallet_id' => $walletId]),
                ],
            ],
            WalletTransactionType::WITHDRAW->value => [
                [
                    'account_id' => $wallet->ledger_account_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Wallet withdrawal',
                    'metadata' => array_merge($metadata, ['wallet_id' => $walletId]),
                ],
                [
                    'account_id' => $offsetAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Withdrawal destination',
                    'metadata' => array_merge($metadata, ['wallet_id' => $walletId]),
                ],
            ],
            default => throw new \InvalidArgumentException('Unsupported wallet transaction type.'),
        };

        $ledgerTransaction = $this->ledgerService->createTransaction([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => $transactionType,
            'description' => $description ?? ucfirst($transactionType) . ' for wallet ' . $walletId,
            'metadata' => $metadata,
        ], $ledgerEntries);

        $financialTransaction = $this->financialTransactionRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => $transactionType,
            'status' => $ledgerTransaction->status,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'sender_wallet_id' => $transactionType === WalletTransactionType::WITHDRAW->value ? $wallet->id : null,
            'receiver_wallet_id' => $transactionType === WalletTransactionType::DEPOSIT->value ? $wallet->id : null,
            'reference_type' => null,
            'reference_id' => null,
            'idempotency_key' => null,
            'metadata' => array_merge($metadata, ['wallet_id' => $walletId, 'transaction_direction' => $transactionType]),
            'ledger_transaction_id' => $ledgerTransaction->id,
            'created_by' => auth()->id(),
        ]);

        $walletTransaction = $this->transactionRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'wallet_id' => $wallet->id,
            'ledger_transaction_id' => $ledgerTransaction->id,
            'financial_transaction_id' => $financialTransaction->id,
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'status' => $ledgerTransaction->status,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->balanceService->reconcileWalletBalance($walletId);

        return $walletTransaction;
    }
}
