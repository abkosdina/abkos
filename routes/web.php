<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankController;
use Modules\Advertisements\Http\Controllers\DiscoveryController;

require base_path('Modules/Shared/Routes/web.php');

Route::get('/', function () {
    return view('landing.index');
});

Route::get('/ads/loadLoans', [BankController::class, 'loadLoans']);

Route::get('/ads/detail', function () {
    return view('ads.adsDetails');
});

Route::get('/dashboard', function () {
    return view('ads.dashboard.dashboard');
});

Route::prefix('api/v1')->group(function () {
    Route::get('banks', [BankController::class, 'banks']);
    Route::get('listings', [DiscoveryController::class, 'index']);
});

