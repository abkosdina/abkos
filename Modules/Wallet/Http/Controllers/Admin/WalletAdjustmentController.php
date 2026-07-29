<?php

namespace Modules\Wallet\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Wallet\Http\Requests\WalletAdjustmentRequest;
use Modules\Wallet\Services\FinancialTransactionService;
use Modules\Shared\Base\BaseController;

class WalletAdjustmentController extends BaseController
{
    public function __construct(protected FinancialTransactionService $financialService)
    {
    }

    public function store(WalletAdjustmentRequest $request): JsonResponse
    {
        $result = $this->financialService->createAdjustment($request->validated());

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
