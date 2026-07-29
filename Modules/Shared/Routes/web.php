<?php

use Illuminate\Support\Facades\Route;

Route::get('/shared/health', function () {
    return response()->json(['status' => 'ok']);
});
