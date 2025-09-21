<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::post('/sepay-webhook', [WebhookReceiverController::class, 'webhook']);


Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerController::class, 'register']);
    Route::post('/login', [CustomerController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::post('/logout', [CustomerController::class, 'logout']);
    });
});