<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::apiResource('expense', ExpenseController::class);
Route::get('expense-list', [ExpenseController::class, 'dropdownList']);
