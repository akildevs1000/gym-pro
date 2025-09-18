<?php

use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::apiResource('vendor', VendorController::class);
Route::get('vendor-list', [VendorController::class, 'dropdownList']);
