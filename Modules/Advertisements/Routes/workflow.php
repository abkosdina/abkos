<?php

use Illuminate\Support\Facades\Route;
use Modules\Advertisements\Http\Controllers\Api\AdvertisementWorkflowController;

/**
 * Advertisement Workflow Routes
 *
 * All workflow action routes
 */
Route::middleware(['api', 'auth'])->prefix('advertisements')->group(function () {
    // Workflow state info
    Route::get('{uuid}/workflow-state', [AdvertisementWorkflowController::class, 'getWorkflowState'])
        ->name('advertisements.workflow.state');

    // Workflow actions
    Route::post('{uuid}/submit', [AdvertisementWorkflowController::class, 'submit'])
        ->name('advertisements.workflow.submit');

    Route::post('{uuid}/approve', [AdvertisementWorkflowController::class, 'approve'])
        ->name('advertisements.workflow.approve');

    Route::post('{uuid}/reject', [AdvertisementWorkflowController::class, 'reject'])
        ->name('advertisements.workflow.reject');

    Route::post('{uuid}/correction', [AdvertisementWorkflowController::class, 'correction'])
        ->name('advertisements.workflow.correction');

    Route::post('{uuid}/publish', [AdvertisementWorkflowController::class, 'publish'])
        ->name('advertisements.workflow.publish');

    Route::post('{uuid}/pause', [AdvertisementWorkflowController::class, 'pause'])
        ->name('advertisements.workflow.pause');

    Route::post('{uuid}/resume', [AdvertisementWorkflowController::class, 'resume'])
        ->name('advertisements.workflow.resume');

    Route::post('{uuid}/archive', [AdvertisementWorkflowController::class, 'archive'])
        ->name('advertisements.workflow.archive');

    Route::post('{uuid}/restore', [AdvertisementWorkflowController::class, 'restore'])
        ->name('advertisements.workflow.restore');

    Route::post('{uuid}/sold', [AdvertisementWorkflowController::class, 'sold'])
        ->name('advertisements.workflow.sold');
});
