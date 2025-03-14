<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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