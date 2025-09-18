<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);
Route::get('products-list', [ProductController::class, 'dropDownList']);
Route::post('products-update/{id}', [ProductController::class, 'productUpdate']);

