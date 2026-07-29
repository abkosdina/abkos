<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\Http\Controllers\Admin\WalletAdjustmentController;

Route::prefix('api/v1/admin/wallets')->middleware(['auth:sanctum', 'role:admin|super-admin'])->group(function () {
    Route::post('/adjustments', [WalletAdjustmentController::class, 'store']);
});
