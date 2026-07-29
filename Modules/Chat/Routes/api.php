<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatController;

Route::middleware(['auth:sanctum'])->prefix('api/v1/chat')->group(function () {
    Route::get('/rooms', [ChatController::class, 'indexRooms']);
    Route::get('/rooms/archived', [ChatController::class, 'indexArchivedRooms']);
    Route::post('/rooms', [ChatController::class, 'storeRoom']);
    Route::get('/rooms/{room}', [ChatController::class, 'showRoom']);
    Route::get('/rooms/{room}/messages', [ChatController::class, 'listMessages']);
    Route::post('/rooms/{room}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/rooms/{room}/mark-read', [ChatController::class, 'markRoomRead']);
    Route::post('/rooms/{room}/archive', [ChatController::class, 'archiveRoom']);
    Route::post('/rooms/{room}/restore', [ChatController::class, 'restoreRoom']);
    Route::post('/messages/{message}/attachments', [ChatController::class, 'addAttachment']);
});
