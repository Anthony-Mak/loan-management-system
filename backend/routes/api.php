<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Employee routes
Route::middleware(['auth:api', 'role:employee'])->group(function () {
    Route::get('/employee/profile', [EmployeeController::class, 'profile']);
    Route::post('/loan/apply', [LoanApplicationController::class, 'store']);
    Route::get('/loan/history', [LoanApplicationController::class, 'history']);
});

// Admin routes
Route::middleware(['auth:api', 'role:admin,manager'])->group(function () {
    Route::get('/admin/loan-applications', [AdminController::class, 'loanApplications']);
    Route::put('/admin/loan-applications/{id}', [AdminController::class, 'updateApplication']);
    Route::get('/admin/employees', [AdminController::class, 'employees']);
});

//Middleware routes
Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class
])->group(function () {
    // Your protected API routes here
});