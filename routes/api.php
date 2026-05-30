<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancialController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────────
    Route::post('register', [AuthController::class, 'register']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);

    // ── Usuarios ──────────────────────────────────────────────
    Route::get('users', [UserController::class, 'index']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // ── Servicios ─────────────────────────────────────────────
    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);

    // ── Citas ─────────────────────────────────────────────────
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);
    //Pendiente Route::put('appointments/{id}', [AppointmentController::class, 'update']);
    Route::patch('appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    Route::delete('appointments/{id}', [AppointmentController::class, 'destroy']);

    // ── Pagos ─────────────────────────────────────────────────
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);

    // ── Gastos ────────────────────────────────────────────────
    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);
    Route::delete('expenses/{id}', [ExpenseController::class, 'destroy']);

    // ── Reporte financiero ────────────────────────────────────
    Route::get('financial/summary', [FinancialController::class, 'summary']);
});