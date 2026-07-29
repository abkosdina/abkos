<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflow\Http\Controllers\WorkflowController;
use Modules\Workflow\Http\Controllers\WorkflowInstanceController;
use Modules\Workflow\Http\Controllers\WorkflowApprovalController;

Route::prefix('api/v1/workflow')->group(function () {
    Route::get('/', [WorkflowController::class, 'index']);
    Route::post('/', [WorkflowController::class, 'store']);
    Route::get('/{workflow}', [WorkflowController::class, 'show']);
    Route::put('/{workflow}', [WorkflowController::class, 'update']);
    Route::post('/{workflow}/activate', [WorkflowController::class, 'activate']);
    Route::post('/{workflow}/deactivate', [WorkflowController::class, 'deactivate']);
    Route::post('/{workflow}/versions', [WorkflowController::class, 'createVersion']);
    Route::get('/{workflow}/versions', [WorkflowController::class, 'versions']);
    Route::post('/{workflow}/start', [WorkflowInstanceController::class, 'start']);
});

Route::prefix('api/v1/workflow-instances')->group(function () {
    Route::get('/', [WorkflowInstanceController::class, 'index']);
    Route::get('/{instance}', [WorkflowInstanceController::class, 'show']);
    Route::post('/{instance}/approve', [WorkflowApprovalController::class, 'approve']);
    Route::post('/{instance}/reject', [WorkflowApprovalController::class, 'reject']);
    Route::post('/{instance}/return', [WorkflowApprovalController::class, 'return']);
    Route::post('/{instance}/cancel', [WorkflowApprovalController::class, 'cancel']);
});
