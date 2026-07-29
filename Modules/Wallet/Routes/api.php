<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\Http\Controllers\WalletController;

Route::prefix('api/v1/wallets')->middleware(['auth:sanctum', 'permission:menu.wallet'])->group(function () {
    Route::get('/', [WalletController::class, 'index']);
    Route::post('/', [WalletController::class, 'store']);
    Route::get('/{wallet}', [WalletController::class, 'show']);
    Route::get('/{wallet}/balances', [WalletController::class, 'balance']);
    Route::post('/{wallet}/deposit', [WalletController::class, 'deposit']);
    Route::post('/{wallet}/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/{wallet}/transfer', [WalletController::class, 'transfer']);
    Route::post('/{wallet}/lock', [WalletController::class, 'lock']);
    Route::post('/{wallet}/unlock', [WalletController::class, 'unlock']);
    Route::post('/{wallet}/freeze', [WalletController::class, 'freeze']);
    Route::post('/{wallet}/close', [WalletController::class, 'close']);
});
