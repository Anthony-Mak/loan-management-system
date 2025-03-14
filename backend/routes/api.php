<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Controllers\ApiController;



// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth');
    
// Error logging
Route::post('/log-error', [ApiController::class, 'logError']);
    
// Public API endpoints
Route::get('/loan-types', [ApiController::class, 'getLoanTypes']);
Route::get('/branches', [ApiController::class, 'getBranches']);
Route::post('/submit-application', [ApiController::class, 'submitLoanApplication']);
Route::get('/check-status', [ApiController::class, 'checkApplicationStatus']);
Route::get('/loan-pdf/{id}', [ApiController::class, 'downloadLoanPDF']);

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/loan-applications', [AdminController::class, 'loanApplications']);
    Route::put('/loan-applications/{id}', [AdminController::class, 'updateApplication']);
    Route::get('/employees', [AdminController::class, 'employees']);
    Route::get('/employees/{id}', [AdminController::class, 'employeeDetails']);
    Route::post('/loan-report', [AdminController::class, 'loanReport']);

    // User management
    Route::apiResource('/users', UserController::class);
});

// HR routes
Route::middleware(['auth:sanctum', 'role:hr,admin'])->prefix('hr')->group(function () {
    Route::get('/loan-applications', [HrController::class, 'loanApplications']);
    Route::put('/loan-applications/{id}', [HrController::class, 'updateApplication']);
    Route::get('/employees', [HrController::class, 'employees']);
    Route::get('/employees/{id}', [HrController::class, 'employeeDetails']);
});

// Employee routes
Route::middleware(['auth:sanctum', 'role:employee,hr,admin'])->group(function () {
    Route::get('/loans/history', [LoanApplicationController::class, 'history']);
    Route::post('/loans', [LoanApplicationController::class, 'store']);
    Route::get('/profile', [EmployeeController::class, 'show']);
    Route::put('/profile', [EmployeeController::class, 'update']);
});

// Stateful API routes
Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class
])->group(function () {
    // Your protected API routes here
});
