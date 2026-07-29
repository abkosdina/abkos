<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\DocumentController;
use Modules\Documents\Http\Controllers\DocumentTypeController;

Route::prefix('api/v1/documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::post('/', [DocumentController::class, 'store'])->middleware(['auth:sanctum', 'permission:menu.documents']);
    Route::get('/{document}', [DocumentController::class, 'show']);
    Route::put('/{document}', [DocumentController::class, 'update'])->middleware(['auth:sanctum', 'permission:menu.documents']);
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->middleware(['auth:sanctum', 'permission:menu.documents']);
    Route::post('/{document}/download', [DocumentController::class, 'download'])->middleware(['auth:sanctum', 'permission:menu.documents']);
    Route::post('/{document}/share', [DocumentController::class, 'share'])->middleware(['auth:sanctum', 'permission:menu.documents']);
    Route::post('/{document}/replace', [DocumentController::class, 'replace'])->middleware(['auth:sanctum', 'permission:menu.documents']);
});

Route::prefix('api/v1/document-types')->group(function () {
    Route::get('/', [DocumentTypeController::class, 'index']);
    Route::post('/', [DocumentTypeController::class, 'store']);
});
