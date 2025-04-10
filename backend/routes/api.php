<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AdminReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'api',
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class
    ])->group(function () {

    // Authentication routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Public API endpoints
    Route::post('/log-error', [ApiController::class, 'logError']);
    Route::get('/loan-types', [ApiController::class, 'getLoanTypes']);
    Route::get('/branches', [ApiController::class, 'getBranches']);
    Route::post('/submit-application', [ApiController::class, 'submitLoanApplication']);
    Route::get('/check-status', [ApiController::class, 'checkApplicationStatus']);
    Route::get('/loan-pdf/{id}', [ApiController::class, 'downloadLoanPDF']);
    
    // Protected API routes
    Route::middleware('auth:sanctum')->group(function() {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::get('/check-session', fn() => response()->json(['valid' => true]));
        
        // Loan routes accessible to all authenticated users
        Route::get('/loans/history', [LoanApplicationController::class, 'history']);
        Route::post('/loans', [LoanApplicationController::class, 'store']);
        
        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::post('/loan-types', [AdminController::class, 'createLoanType']);
            Route::put('/loan-types/{id}', [AdminController::class, 'updateLoanType']);
            Route::get('/loan-types/{id}', [AdminController::class, 'getLoanType']);
            Route::post('/branches', [AdminController::class, 'createBranch']);
            Route::put('/branches/{id}', [AdminController::class, 'updateBranch']);
            Route::get('/system-config', [AdminController::class, 'getSystemConfig']);
            Route::post('/employees', [AdminController::class, 'createEmployee']);
            Route::get('/employees', [AdminController::class, 'getEmployees']);
            Route::get('/employees/{id}', [AdminController::class, 'getEmployeeDetails']);
            Route::post('/loan-report', [AdminController::class, 'loanReport']);
            Route::get('/system-report', [AdminReportController::class, 'generateSystemReport']);
            Route::get('/branch-statistics', [AdminReportController::class, 'getBranchStatistics']);
            Route::get('/branches', [AdminReportController::class, 'getBranchStatistics']);
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/audit-logs', [AdminController::class, 'viewAuditLogs']);
            Route::get('/loan-types', [AdminController::class, 'getLoanTypes']);
            Route::get('/branches', [AdminController::class, 'getBranches']);

            // User management
            Route::apiResource('/users', UserController::class);
            Route::put('/users/{userId}/role', [AdminController::class, 'updateUserRole']);
            Route::put('/users/{userId}/status', [AdminController::class, 'updateUserStatus']);
            Route::post('/users', [AdminController::class, 'createUser']);
            Route::put('/users/{userId}/reset-password', [AdminController::class, 'resetUserPassword']);
        });

        Route::middleware('role:hr')->prefix('hr')->group(function () {
            Route::get('/loan-applications', [HrController::class, 'loanApplications']);
            Route::get('/loan-applications/{id}', [HrController::class, 'getLoanDetails']);
            Route::put('/loan-applications/{id}', [HrController::class, 'updateApplication']);
            Route::get('/loans', [HrController::class, 'loans']);
            Route::get('/loans/{id}', [HrController::class, 'getLoanDetails']);
            Route::get('/employees', [HrController::class, 'employees']);
            Route::get('/employees/{id}', [HrController::class, 'employeeDetails']);
            Route::get('/reports', [HrController::class, 'generateReport']);
            Route::get('/departments', [HrController::class, 'getDepartments']);
            Route::get('/employee/loan/{loan}/pdf', [LoanApplicationController::class, 'downloadPDF'])->name('employee.loan.pdf')->middleware(['auth']);
        });
        
        // Employee routes
        Route::middleware('role:employee')->prefix('employee')->group(function () {
            Route::get('/dashboard', [EmployeeController::class, 'getDashboardData']);
            Route::get('/loans', [EmployeeController::class, 'getLoans']);
            Route::get('/loans/{id}', [EmployeeController::class, 'getLoanDetails']);
            Route::post('/loans', [EmployeeController::class, 'submitLoanApplication']);
            Route::get('/profile', [EmployeeController::class, 'show']);
            Route::put('/profile', [EmployeeController::class, 'update']);
        });
    });
    Route::get('/debug/schema', function () {
        $tables = ['employees', 'banking_details', 'loan_applications', 'collaterals', 'loan_types'];
        $schema = [];
        
        foreach ($tables as $table) {
            $columns = DB::select("SHOW COLUMNS FROM {$table}");
            $schema[$table] = $columns;
            
            Log::debug("Schema for table {$table}:", [
                'columns' => collect($columns)->pluck('Field')->toArray(),
                'column_count' => count($columns)
            ]);
        }
        return response()->json($schema);
    })->middleware('auth');
    Log::debug('PHP & environment information:', [
        'php_version' => phpversion(),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ]);
});