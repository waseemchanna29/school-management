<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ── Public routes (no token required) ───────────────────────────────────────
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login',          [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password',[AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
});

// ── Protected routes (JWT token required + teacher/student only) ─────────────
Route::middleware(['auth:api', 'api.role'])->prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/me',       [AuthController::class, 'me'])->name('me');
});