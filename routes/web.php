<?php

use App\Http\Controllers\Api\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

