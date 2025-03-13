<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;    

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Login routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// API routes for frontend integration
Route::prefix('api')->group(function () {
    // Public API endpoints
    Route::get('/loan-types', [ApiController::class, 'getLoanTypes']);
    Route::get('/branches', [ApiController::class, 'getBranches']);
    Route::post('/submit-application', [ApiController::class, 'submitLoanApplication'])->name('api.submit.application');
    Route::get('/check-status', [ApiController::class, 'checkApplicationStatus']);
    Route::get('/loan-pdf/{id}', [ApiController::class, 'downloadLoanPDF'])->name('api.loan.pdf.download');
    
    // Protected API endpoints
    Route::middleware(['auth'])->group(function () {
        // Employee routes
        Route::middleware(['role:employee'])->group(function () {
            Route::get('/employee/profile', [EmployeeController::class, 'profile']);
            Route::get('/employee/loans', [LoanApplicationController::class, 'history']);
        });
        
        // Admin routes
        Route::middleware(['role:admin,manager'])->group(function () {
            Route::get('/admin/applications', [AdminController::class, 'loanApplications']);
            Route::put('/admin/applications/{id}', [AdminController::class, 'updateApplication']);
            Route::get('/admin/employees', [AdminController::class, 'employees']);
            Route::get('/admin/employee/{id}', [AdminController::class, 'employeeDetails']);
            Route::get('/admin/report', [AdminController::class, 'loanReport']);
        });
    });
});

// Employee portal routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->group(function () {
    Route::get('/dashboard', function () {
        return view('employee.dashboard');
    })->name('employee.dashboard');
    
    Route::get('/apply', function () {
        return view('employee.apply');
    })->name('employee.apply');
    
    Route::get('/history', function () {
        return view('employee.history');
    })->name('employee.history');
});

// Admin portal routes
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::get('/applications', function () {
        return view('admin.applications');
    })->name('admin.applications');
    
    
    Route::get('/employees', function () {
        return view('admin.employees');
    })->name('admin.employees');
    
    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('admin.reports');
});
