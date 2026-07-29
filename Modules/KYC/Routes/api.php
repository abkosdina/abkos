<?php

use Illuminate\Support\Facades\Route;
use Modules\KYC\Http\Controllers\KycRequestController;

Route::prefix('api/v1/kyc')->group(function () {
    Route::get('/', [KycRequestController::class, 'index'])->middleware(['auth:sanctum']);
    Route::post('/', [KycRequestController::class, 'store'])->middleware(['auth:sanctum']);
    Route::get('/{kycRequest}', [KycRequestController::class, 'show'])->middleware(['auth:sanctum']);
    Route::put('/{kycRequest}', [KycRequestController::class, 'update'])->middleware(['auth:sanctum']);

    Route::post('/{kycRequest}/profile', [KycRequestController::class, 'saveProfile'])->middleware(['auth:sanctum']);
    Route::post('/{kycRequest}/documents', [KycRequestController::class, 'uploadDocument'])->middleware(['auth:sanctum']);
    Route::post('/{kycRequest}/documents/{document}/download', [KycRequestController::class, 'downloadDocument'])
        ->middleware(['auth:sanctum'])
        ->name('kyc.documents.download');

    Route::post('/{kycRequest}/submit', [KycRequestController::class, 'submit'])->middleware(['auth:sanctum']);
    Route::post('/{kycRequest}/approve', [KycRequestController::class, 'approve'])->middleware(['auth:sanctum', 'permission:menu.kyc']);
    Route::post('/{kycRequest}/reject', [KycRequestController::class, 'reject'])->middleware(['auth:sanctum', 'permission:menu.kyc']);
    Route::post('/{kycRequest}/request-correction', [KycRequestController::class, 'requestCorrection'])->middleware(['auth:sanctum', 'permission:menu.kyc']);
});
