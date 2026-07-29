<?php

use Illuminate\Support\Facades\Route;
use Modules\Negotiation\Http\Controllers\AdvertisementNegotiationController;
use Modules\Negotiation\Http\Controllers\NegotiationController;

Route::prefix('api/v1')->middleware(['auth:sanctum', 'permission:menu.negotiations'])->group(function () {
    Route::get('/negotiations', [NegotiationController::class, 'index']);
    Route::get('/negotiations/{uuid}', [NegotiationController::class, 'show']);
    Route::post('/advertisements/{uuid}/negotiations', [AdvertisementNegotiationController::class, 'store']);
    Route::get('/advertisements/{uuid}/negotiations', [AdvertisementNegotiationController::class, 'index']);
    Route::post('/negotiations/{uuid}/offers', [NegotiationController::class, 'storeOffer']);
    Route::post('/offers/{uuid}/accept', [NegotiationController::class, 'accept']);
    Route::post('/offers/{uuid}/reject', [NegotiationController::class, 'reject']);
    Route::post('/offers/{uuid}/counter', [NegotiationController::class, 'counter']);
    Route::post('/negotiations/{uuid}/cancel', [NegotiationController::class, 'cancel']);
});
