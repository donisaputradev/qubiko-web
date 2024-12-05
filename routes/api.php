<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/firebase', [AuthController::class, 'firebase']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
