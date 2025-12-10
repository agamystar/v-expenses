<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Vendors
        Route::get('/vendors', [VendorController::class, 'index']);
        Route::post('/vendors', [VendorController::class, 'store'])->middleware('role:admin');
        Route::put('/vendors/{id}', [VendorController::class, 'update'])->middleware('role:admin');
        Route::delete('/vendors/{id}', [VendorController::class, 'destroy'])->middleware('role:admin');
        
        // Categories
        Route::get('/categories', [ExpenseCategoryController::class, 'index']);
        Route::post('/categories', [ExpenseCategoryController::class, 'store'])->middleware('role:admin');
        Route::put('/categories/{id}', [ExpenseCategoryController::class, 'update'])->middleware('role:admin');
        Route::delete('/categories/{id}', [ExpenseCategoryController::class, 'destroy'])->middleware('role:admin');
        
        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
        Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);
        
        // Reports
        Route::get('/reports/summary', [ReportController::class, 'summary']);
    });
});

