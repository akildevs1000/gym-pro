<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard-counts', [DashboardController::class, "dashboardCounts"]);