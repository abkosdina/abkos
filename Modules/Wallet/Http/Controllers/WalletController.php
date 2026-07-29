<?php

namespace Modules\Wallet\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use Modules\Shared\Base\BaseController;

class WalletController extends BaseController
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->listWallets(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->createWallet($request->all()),
        ]);
    }

    public function show($wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->getWallet($wallet),
        ]);
    }

    public function balance($wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->getWalletBalance($wallet),
        ]);
    }

    public function deposit(Request $request, $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->deposit($wallet, (float) $request->input('amount'), $request->input('description'), $request->input('metadata', [])),
        ]);
    }

    public function withdraw(Request $request, $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->withdraw($wallet, (float) $request->input('amount'), $request->input('description'), $request->input('metadata', [])),
        ]);
    }

    public function transfer(Request $request, $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->transfer($wallet, (int) $request->input('destination_wallet_id'), (float) $request->input('amount'), $request->input('description'), $request->input('metadata', [])),
        ]);
    }

    public function lock(Request $request, $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->lockBalance($wallet, (float) $request->input('amount'), $request->input('reason'), $request->input('notes'), $request->input('expires_at')),
        ]);
    }

    public function unlock(Request $request, $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->unlockBalance((int) $request->input('lock_id')),
        ]);
    }

    public function freeze($wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->freezeWallet($wallet),
        ]);
    }

    public function close($wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->walletService->closeWallet($wallet),
        ]);
    }
}
