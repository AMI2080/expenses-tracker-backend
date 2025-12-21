<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\ExpenseCenterController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API v1 Routes
// ============================================================================

Route::prefix('v1')->group(function () {
    // ============================================================================
    // Public Authentication Routes (Guest Only)
    // ============================================================================

    Route::middleware('guest')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    });

    // ============================================================================
    // Protected Routes (Authentication Required)
    // ============================================================================

    Route::middleware('auth:sanctum')->group(function () {
        // Authentication Routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

        // ========================================================================
        // Admin Routes
        // ========================================================================
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/pending-users', [AdminController::class, 'pendingUsers']);
            Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
            Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
        });

        // ========================================================================
        // Expense Management Resources
        // ========================================================================

        // Expense Groups Resource
        Route::apiResource('groups', ExpenseGroupController::class)->parameters([
            'groups' => 'id'
        ]);

        // Group Members (Nested Resource)
        Route::apiResource('groups.members', GroupMemberController::class)
            ->only(['index', 'store', 'destroy']);

        // Expense Centers Resource
        Route::apiResource('centers', ExpenseCenterController::class)->parameters([
            'centers' => 'id'
        ]);
        Route::get('/centers/{id}/report', [ExpenseCenterController::class, 'expensesReport']);

        // Expenses Resource
        Route::apiResource('expenses', ExpenseController::class)->parameters([
            'expenses' => 'id'
        ]);
    });
});
