<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::get('/users/login', [LoginController::class, 'showLoginForm'])->name('users.login');
    Route::get('/users/register', [LoginController::class, 'showRegisterForm'])->name('users.register');

    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/users/login', [LoginController::class, 'login'])->name('users.login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/users/logout', [LoginController::class, 'logout'])->name('users.logout');
});
