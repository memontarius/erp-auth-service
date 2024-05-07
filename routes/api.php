<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('login', [UserController::class, 'login']);

    //Route::post('logout', [UserController::class, 'logout']);
    //Route::post('me', [UserController::class, 'user']);
    //Route::post('refresh', [UserController::class, 'refresh']);
});

Route::middleware('auth:invitation')->group(function () {
    Route::post('user/activate', [UserController::class, 'activate']);
});

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('company/invite', [InvitationController::class, 'inviteCompany']);
    Route::post('user/invite', [InvitationController::class, 'inviteUser']);
});
