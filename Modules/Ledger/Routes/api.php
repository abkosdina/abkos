<?php

use Illuminate\Support\Facades\Route;
use Modules\Ledger\Http\Controllers\LedgerController;

Route::prefix('api/v1/ledger')->group(function () {
    Route::get('/accounts', [LedgerController::class, 'accounts']);
    Route::post('/accounts', [LedgerController::class, 'createAccount']);
    Route::get('/accounts/{account}', [LedgerController::class, 'showAccount']);
    Route::get('/accounts/{account}/balances', [LedgerController::class, 'accountBalance']);

    Route::get('/transactions', [LedgerController::class, 'transactions']);
    Route::post('/transactions', [LedgerController::class, 'createTransaction']);
    Route::get('/transactions/{transaction}', [LedgerController::class, 'showTransaction']);
    Route::post('/transactions/{transaction}/reverse', [LedgerController::class, 'reverseTransaction']);
    Route::post('/transactions/transfer', [LedgerController::class, 'transferFunds']);
});
