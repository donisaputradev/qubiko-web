<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/firebase', [AuthController::class, 'firebase']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/reset-password', [AuthController::class, 'reset_password']);
    Route::post('/resend-otp', [AuthController::class, 'resend_otp']);
    Route::post('/verify-otp', [AuthController::class, 'verify_otp']);
    Route::post('/new-password', [AuthController::class, 'new_password']);
});

Route::group(['prefix' => 'user'], function () {
    Route::post('/personal', [UserController::class, 'personal'])->middleware('auth:sanctum');
    Route::put('/change-password', [UserController::class, 'change_password'])->middleware('auth:sanctum');
});
