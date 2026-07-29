<?php

use Illuminate\Support\Facades\Route;
use Modules\Advertisements\Http\Controllers\AdvertisementController;
use Modules\Advertisements\Http\Controllers\AdvertisementWorkflowController;
use Modules\Advertisements\Http\Controllers\DiscoveryController;

Route::prefix('api/advertisements')->group(function () {
    Route::get('/', [DiscoveryController::class, 'index']);
    Route::middleware(['auth:sanctum', 'approved_kyc', 'not_suspended', 'advertisement_limit'])->post('/', [AdvertisementController::class, 'store']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('user', [AdvertisementController::class, 'index']);
        Route::get('user/{uuid}', [AdvertisementController::class, 'show']);
        Route::put('{uuid}', [AdvertisementController::class, 'update']);
        Route::delete('{uuid}', [AdvertisementController::class, 'destroy']);
        Route::post('user/{uuid}/submit', [AdvertisementController::class, 'submit']);
    });
    Route::get('filters', [DiscoveryController::class, 'filters']);
    Route::get('similar/{uuid}', [DiscoveryController::class, 'similar']);
    Route::get('recommended', [DiscoveryController::class, 'recommended']);
    Route::get('popular', [DiscoveryController::class, 'popular']);
    Route::get('latest', [DiscoveryController::class, 'latest']);
    Route::get('trending', [DiscoveryController::class, 'trending']);
    Route::get('{uuid}', [DiscoveryController::class, 'show'])->middleware(['record_ad_view']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('{uuid}/favorite', [DiscoveryController::class, 'favorite']);

        Route::delete('{uuid}/favorite', [DiscoveryController::class, 'unfavorite']);
        Route::get('user/favorites', [DiscoveryController::class, 'userFavorites']);
        Route::get('user/recently-viewed', [DiscoveryController::class, 'recentlyViewed']);
    });
});

