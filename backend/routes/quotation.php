<?php

use App\Http\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;

Route::post('quotations', [QuotationController::class,"store"]);
Route::get('quotations', [QuotationController::class, 'index']);
Route::get('quotations-list', [QuotationController::class, 'dropDownList']);

