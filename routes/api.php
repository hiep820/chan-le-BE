<?php

use App\Http\Controllers\Api\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::post('/sepay-webhook', [WebhookReceiverController::class, 'webhook']);