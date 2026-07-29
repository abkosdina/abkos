<?php

namespace Modules\Ledger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ledger\Http\Requests\CreateAccountRequest;
use Modules\Ledger\Http\Requests\CreateTransactionRequest;
use Modules\Ledger\Http\Requests\ReverseTransactionRequest;
use Modules\Ledger\Http\Requests\TransferFundsRequest;
use Modules\Ledger\Services\LedgerService;
use Modules\Shared\Base\BaseController;

class LedgerController extends BaseController
{
    public function __construct(protected LedgerService $ledgerService)
    {
    }

    public function accounts(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->listAccounts(),
            'message' => 'Accounts retrieved successfully.',
        ]);
    }

    public function createAccount(CreateAccountRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->createAccount($request->validated()),
            'message' => 'Ledger account created successfully.',
        ]);
    }

    public function showAccount($account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->getAccount($account),
            'message' => 'Ledger account retrieved successfully.',
        ]);
    }

    public function accountBalance($account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->getAccountBalance($account),
            'message' => 'Account balance retrieved successfully.',
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->listTransactions(),
            'message' => 'Transactions retrieved successfully.',
        ]);
    }

    public function createTransaction(CreateTransactionRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->createTransaction($request->validated(), $request->validated()['entries']),
            'message' => 'Ledger transaction created successfully.',
        ]);
    }

    public function showTransaction($transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->getTransaction($transaction),
            'message' => 'Ledger transaction retrieved successfully.',
        ]);
    }

    public function reverseTransaction(ReverseTransactionRequest $request, $transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->reverseTransaction($transaction, $request->validated()['reason'] ?? null),
            'message' => 'Ledger transaction reversed successfully.',
        ]);
    }

    public function transferFunds(TransferFundsRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->ledgerService->transferFunds(
                $request->validated()['from_account_id'],
                $request->validated()['to_account_id'],
                (float) $request->validated()['amount'],
                $request->validated()['description'] ?? null,
                $request->validated()['metadata'] ?? []
            ),
            'message' => 'Funds transferred successfully.',
        ]);
    }
}
