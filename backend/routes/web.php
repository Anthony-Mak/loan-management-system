<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/employee/loan/{loan}/policy', [LoanApplicationController::class, 'showPolicy'])->name('employee.loan.policy');
    Route::post('/employee/loan/pledge', [LoanApplicationController::class, 'storePledge'])->name('employee.loan.pledge');

    // Employee portal routes
    Route::middleware(['role:employee'])->prefix('employee')->group(function () {
        Route::get('/dashboard', function () {
            return view('employee.dashboard');
        })->name('employee.dashboard');
        
        Route::get('/apply', [LoanApplicationController::class, 'create'])->name('employee.apply');
        Route::get('/history', function () {
            return view('employee.history');
        })->name('employee.history');
        
        Route::post('/loans', [LoanApplicationController::class, 'store'])->name('employee.loan.store');
        
        Route::get('/loan/{loan}/pledge', [LoanApplicationController::class, 'showPledgeForm'])
            ->name('employee.loan.pledge_form');
        
        Route::post('/loan/store-pledge', [LoanApplicationController::class, 'storePledge'])
            ->name('employee.loan.store_pledge');
    });

    // Admin portal routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
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
});