<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::post('invoices', [InvoiceController::class,"store"]);
Route::get('invoices', [InvoiceController::class, 'index']);
Route::get('invoices-list', [InvoiceController::class, 'dropDownList']);
Route::get('invoices-counts', [InvoiceController::class, 'counts']);
Route::post('invoices-payments', [InvoiceController::class, 'payment']);

