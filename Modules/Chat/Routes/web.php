<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatRoomController;

Route::get('/chat/room/{roomId}', [ChatRoomController::class, 'show'])->name('chat.room.show');
Route::get('/api/chat/room/{roomId}/messages', [ChatRoomController::class, 'getNewMessages'])->name('chat.messages.new');
