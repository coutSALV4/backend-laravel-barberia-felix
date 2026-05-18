<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
});