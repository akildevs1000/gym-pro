<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payments', PaymentController::class);
Route::get('payments-summary-by-payment-mode', [PaymentController::class,"paymentSummaryByMode"]);