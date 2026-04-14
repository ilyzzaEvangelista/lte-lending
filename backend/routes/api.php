<?php

use App\Http\Controllers\Api\AdminActivityLogController;
use App\Http\Controllers\Api\AdminLoanController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientLoanController;
use App\Http\Controllers\Api\ClientPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('customer')->group(function () {
        Route::get('/client/loans', [ClientLoanController::class, 'index']);
        Route::post('/client/loans', [ClientLoanController::class, 'store']);
        Route::get('/client/loans/{loanDetail}', [ClientLoanController::class, 'show']);
        Route::get('/client/loans/{loanDetail}/payslip', [ClientLoanController::class, 'payslip']);
        Route::get('/client/loans/{loanDetail}/records', [ClientLoanController::class, 'records']);
        Route::post('/client/loans/{loanDetail}/payments', [ClientPaymentController::class, 'store']);
    });

    Route::middleware('admin')->group(function () {
        Route::get('/admin/loans', [AdminLoanController::class, 'index']);
        Route::get('/admin/loans/{loanDetail}/payslip', [AdminLoanController::class, 'payslip']);
        Route::patch('/admin/loans/{loanDetail}', [AdminLoanController::class, 'update']);
        Route::get('/admin/payments', [AdminPaymentController::class, 'index']);
        Route::get('/admin/payments/{loanPayment}/receipt', [AdminPaymentController::class, 'receipt']);
        Route::patch('/admin/payments/{loanPayment}', [AdminPaymentController::class, 'update']);
        Route::get('/admin/logs', [AdminActivityLogController::class, 'index']);
    });
});
