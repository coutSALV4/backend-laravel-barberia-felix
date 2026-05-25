<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::get('users', [UserController::class, 'index']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    Route::post('logout', [AuthController::class, 'logout']);
});