<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // =========================================================================
    // 1. ENDPOINT PUBLIK (Autentikasi & Login)
    // =========================================================================
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (Wajib auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);
    });
});
