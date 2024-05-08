<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;


Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:invitation')->group(function () {
    Route::post('user/activate', [UserController::class, 'activate']);
});

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('company/invite', [InvitationController::class, 'inviteCompany']);
    Route::post('user/invite', [InvitationController::class, 'inviteUser']);
});
