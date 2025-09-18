<?php

use App\Http\Controllers\PaymentModeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payment-modes', PaymentModeController::class);
Route::get('payment-modes-list', [PaymentModeController::class, 'dropdownList']);
