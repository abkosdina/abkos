<?php

use Illuminate\Support\Facades\Route;
use Modules\Deals\Http\Controllers\DealController;

Route::prefix('api/v1/deals')->middleware(['auth:sanctum', 'permission:menu.deals'])->group(function () {
    Route::get('/', [DealController::class, 'index']);
    Route::get('/{uuid}', [DealController::class, 'show']);
    Route::post('/{uuid}/cancel', [\Modules\Deals\Http\Controllers\DealLifecycleController::class, 'cancel']);
    Route::post('/{uuid}/expire', [\Modules\Deals\Http\Controllers\DealLifecycleController::class, 'expire']);
    Route::post('/{uuid}/dispute', [\Modules\Deals\Http\Controllers\DealLifecycleController::class, 'dispute']);
    Route::post('/{uuid}/close', [\Modules\Deals\Http\Controllers\DealLifecycleController::class, 'close']);
    Route::post('/{uuid}/complete', [\Modules\Deals\Http\Controllers\DealLifecycleController::class, 'complete']);
});

Route::prefix('api/v1/my')->middleware(['auth:sanctum'])->group(function () {
    Route::get('deals', [\Modules\Deals\Http\Controllers\DealController::class, 'myDeals']);
});
