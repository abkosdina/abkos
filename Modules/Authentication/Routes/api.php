<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\AuthenticationController;

Route::middleware(['auth:sanctum'])->get('/api/user', [AuthenticationController::class, 'me']);

Route::prefix('api/v1/auth')->group(function () {
    Route::post('/otp/request', [AuthenticationController::class, 'requestOtp']);
    Route::post('/otp/verify', [AuthenticationController::class, 'verifyOtp']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthenticationController::class, 'logout']);
        Route::get('/me', [AuthenticationController::class, 'me']);
        Route::get('/broker-registration-status', [AuthenticationController::class, 'brokerRegistrationStatus']);
        Route::post('/broker-registration-toggle', [AuthenticationController::class, 'toggleBrokerRegistration']);
    });
});
