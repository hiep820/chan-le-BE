<?php

use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TbGameResultController;
use App\Http\Controllers\Api\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::post('/sepay-webhook', [WebhookReceiverController::class, 'webhook']);
Route::get('/list-bank', [BankAccountController::class, 'list']);
Route::get('/history-all', [TbGameResultController::class, 'historyAll']);

Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerController::class, 'register']);
    Route::post('/login', [CustomerController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::post('/logout', [CustomerController::class, 'logout']);
        Route::get('/history', [CustomerController::class, 'historyCustomer']);
        Route::get('/sum-amount/{id}', [CustomerController::class, 'totalBetDate']);
    });


});