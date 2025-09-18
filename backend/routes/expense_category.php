<?php

use App\Http\Controllers\ExpenseCategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('expense-category', ExpenseCategoryController::class);
Route::get('expense-category-list', [ExpenseCategoryController::class, 'dropdownList']);
