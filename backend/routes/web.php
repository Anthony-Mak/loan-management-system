<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrController;

/* |-------------------------------------------------------------------------- 
| Web Routes 
|-------------------------------------------------------------------------- */

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Protected routes
Route::middleware(['auth'])->group(function() {
    // Common authenticated routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change.auth');

    // Employee portal routes
    Route::middleware(['role:employee'])->prefix('employee')->group(function () {
        //The employee view which is located at backend\resources\views\employee\dashboard.blade.php
        Route::get('/dashboard', function () {
            return view('employee.dashboard');
        })->name('employee.dashboard');

        Route::get('/apply', [LoanApplicationController::class, 'create'])->name('employee.apply');
        Route::get('/history', function () {
            return view('employee.history');
        })->name('employee.history');

        Route::post('/loans', [LoanApplicationController::class, 'store'])->name('employee.loan.store');
        Route::post('/loan/policy', [LoanApplicationController::class, 'storePolicyAcknowledgment'])->name('employee.loan.policy.store');
        Route::get('/loan/{loan}/policy', [LoanApplicationController::class, 'showPolicy'])->name('employee.loan.policy');
        Route::get('/loan/{loan}/pledge', [LoanApplicationController::class, 'showPledgeForm'])->name('employee.loan.pledge');
        Route::post('/loan/pledge', [LoanApplicationController::class, 'storePledge'])->name('employee.loan.pledge.store');
        Route::post('/employee/loan/policy-acknowledge', [LoanApplicationController::class, 'acknowledgePolicyRoute'])->name('employee.loan.acknowledge_policy');
    });

    // HR portal routes
    Route::middleware(['role:hr'])->prefix('hr')->group(function () {
        //Using single dynamic dashboard for HR and Admin at backend\resources\views\admin\dashboard.blade.php
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('hr.dashboard');

        Route::get('/loan-applications', function () {
            return view('admin.dashboard');})->name('hr.loan-applications');

        Route::get('/employees', function () {
            return view('admin.dashboard');})->name('hr.employees');

        // Redirecting to HR-specific reports blade
        Route::get('/reports', function () {
            return view('admin.reports.hr_reports', [
                'reportType' => 'hr']);})->name('hr.reports');
        Route::get('/hr/reports', [HrController::class, 'showHrReports']);
    });

    // Admin portal routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
        
        Route::get('/employees', function () {
            return view('admin.dashboard');})->name('admin.employees');

            Route::get('/reports', function () {
                return view('admin.reports.admin_reports', [
                    'reportType' => 'admin']);})->name('admin.reports');

        Route::get('/admin/reports', [AdminController::class, 'showAdminReports']);
        
    });
});