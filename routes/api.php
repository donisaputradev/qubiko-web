<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HelpController;
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

Route::group(['middleware' =>  'auth:sanctum', 'prefix' => 'user'], function () {
    Route::get('/', [UserController::class, 'profile']);
    Route::post('/personal', [UserController::class, 'personal']);
    Route::put('/change-password', [UserController::class, 'change_password']);
});

Route::group(['prefix' => 'help'], function () {
    Route::get('/faq-category', [HelpController::class, 'category']);
    Route::get('/faq', [HelpController::class, 'faq']);
    Route::get('/contact', [HelpController::class, 'contact']);
    Route::get('/privacy', [HelpController::class, 'privacy']);
    Route::get('/abouts', [HelpController::class, 'abouts']);
});
