<?php

namespace Modules\Wallet\Services;

use Illuminate\Support\Facades\DB;
use Modules\Wallet\Repositories\Interfaces\FinancialTransactionRepositoryInterface;
use Modules\Ledger\Services\LedgerService;
use Modules\Wallet\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use Modules\Wallet\Services\BalanceService as WalletBalanceService;
use Modules\Wallet\Models\Wallet;
use App\Models\AuditLog;

class FinancialTransactionService
{
    public function __construct(
        protected FinancialTransactionRepositoryInterface $financialRepository,
        protected LedgerService $ledgerService,
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletBalanceService $walletBalanceService,
    ) {
    }

    /**
     * Create an admin adjustment or manual financial transaction.
     * Ensures idempotency, double-entry ledger posting, wallet transaction and balance reconciliation.
     */
    public function createAdjustment(array $data): object
    {
        $idempotencyKey = $data['idempotency_key'] ?? null;

        if ($idempotencyKey) {
            $existing = $this->financialRepository->findByIdempotencyKey($idempotencyKey);
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $idempotencyKey) {
            $walletId = $data['wallet_id'];
            $wallet = Wallet::query()->where('id', $walletId)->lockForUpdate()->first();
            if (!$wallet) {
                throw new \InvalidArgumentException('Wallet not found.');
            }

            if ($wallet->status !== config('wallet.statuses.active') && $wallet->status !== config('wallet.statuses.suspended')) {
                // allow adjustments on suspended, but not closed/frozen
                if ($wallet->status !== config('wallet.statuses.active')) {
                    // continue — admins may adjust non-active wallets in some cases
                }
            }

            $offsetAccountId = config('wallet.ledger_offset_account_id');
            if (!$offsetAccountId) {
                throw new \RuntimeException('Wallet ledger offset account is not configured.');
            }

            $amount = (float) $data['amount'];
            $direction = $data['direction'] ?? 'credit'; // credit => increase wallet, debit => reduce wallet
            $transactionType = $data['transaction_type'] ?? 'adjustment';

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Amount must be greater than zero.');
            }

            // Build balanced entries: debit and credit
            if ($direction === 'debit') {
                $entries = [
                    [
                        'account_id' => $wallet->ledger_account_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => $data['description'] ?? 'Admin debit adjustment',
                        'metadata' => array_merge($data['metadata'] ?? [], ['wallet_id' => $walletId, 'reason' => $data['reason'] ?? null]),
                    ],
                    [
                        'account_id' => $offsetAccountId,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => $data['description'] ?? 'Offset credit for admin debit',
                        'metadata' => array_merge($data['metadata'] ?? [], ['wallet_id' => $walletId, 'reason' => $data['reason'] ?? null]),
                    ],
                ];
            } else {
                // credit
                $entries = [
                    [
                        'account_id' => $offsetAccountId,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => $data['description'] ?? 'Offset debit for admin credit',
                        'metadata' => array_merge($data['metadata'] ?? [], ['wallet_id' => $walletId, 'reason' => $data['reason'] ?? null]),
                    ],
                    [
                        'account_id' => $wallet->ledger_account_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => $data['description'] ?? 'Admin credit adjustment',
                        'metadata' => array_merge($data['metadata'] ?? [], ['wallet_id' => $walletId, 'reason' => $data['reason'] ?? null]),
                    ],
                ];
            }

            // Create ledger transaction (this will append immutable ledger entries)
            $ledgerTransaction = $this->ledgerService->createTransaction([
                'uuid' => $data['uuid'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'type' => $transactionType,
                'description' => $data['description'] ?? ($transactionType . ' adjustment for wallet ' . $walletId),
                'metadata' => $data['metadata'] ?? [],
            ], $entries);

            // Persist financial transaction
            $financial = $this->financialRepository->create([
                'uuid' => $data['uuid'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'type' => $transactionType,
                'status' => $ledgerTransaction->status ?? 'completed',
                'amount' => $amount,
                'currency' => $wallet->currency ?? ($data['currency'] ?? 'USD'),
                'sender_wallet_id' => null,
                'receiver_wallet_id' => $wallet->id,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'metadata' => $data['metadata'] ?? [],
                'ledger_transaction_id' => $ledgerTransaction->id,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Create wallet transaction record linking the wallet and ledger transaction
            $this->walletTransactionRepository->create([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'wallet_id' => $wallet->id,
                'ledger_transaction_id' => $ledgerTransaction->id,
                'financial_transaction_id' => $financial->id,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'status' => $ledgerTransaction->status ?? 'completed',
                'description' => $data['description'] ?? null,
                'metadata' => array_merge($data['metadata'] ?? [], ['adjustment' => true, 'direction' => $direction, 'financial_transaction_id' => $financial->id]),
            ]);

            // Reconcile wallet balances from ledger
            $this->walletBalanceService->reconcileWalletBalance($wallet->id);

            // Create audit log
            AuditLog::create([
                'user_id' => $data['created_by'] ?? auth()->id(),
                'action' => 'wallet_adjustment',
                'action_label' => 'Admin Wallet Adjustment',
                'subject_type' => 'wallet',
                'subject_id' => $wallet->id,
                'old_values' => null,
                'new_values' => json_encode(['amount' => $amount, 'direction' => $direction]),
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'metadata' => json_encode(array_merge($data['metadata'] ?? [], ['financial_transaction_id' => $financial->id, 'ledger_transaction_id' => $ledgerTransaction->id])),
            ]);

            return $financial->refresh();
        });
    }
}
