<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\EmployeeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Anwani zote hapa zinaanza na /api otomatiki.
| Zinazohitaji login zimewekwa ndani ya middleware('auth:sanctum').
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/user', fn (Request $request) => $request->user());

    // Miamala
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    // Mitandao
    Route::get('/networks', [NetworkController::class, 'index']);
    Route::post('/networks', [NetworkController::class, 'store']);
    Route::put('/networks/{network}', [NetworkController::class, 'update']);
    //report
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
});
