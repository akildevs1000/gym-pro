<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::apiResource('members', MemberController::class);
Route::get('members-list', [MemberController::class, 'dropDownList']);
Route::post('members-update/{id}', [MemberController::class, 'memberUpdate']);
Route::post('membership-update/{id}', [MemberController::class, 'memberShipUpdate']);
Route::get('get-next-system-user-id', [MemberController::class, 'getNextSystemUserId']);
Route::get('membership-renewal/{id}', [MemberController::class, 'membershipRenewal']);

