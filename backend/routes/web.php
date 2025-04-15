<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrController;
use Illuminate\Support\Facades\Storage;


/* |-------------------------------------------------------------------------- 
| Web Routes 
|-------------------------------------------------------------------------- */

// Public routes
Route::get('/', function () {
    return view('welcome');
});

//Test route for GCS
Route::get('/test-gcs-service', function() {
    try {
        $gcs = app(App\Services\GoogleCloudStorageService::class);
        
        $fileName = 'service-test-' . time() . '.txt';
        $content = 'Test file content ' . date('Y-m-d H:i:s');
        
        $success = $gcs->put($fileName, $content);
        
        return [
            'success' => $success,
            'file_name' => $fileName,
            'file_exists' => $gcs->exists($fileName),
            'file_url' => $gcs->url($fileName),
            'signed_url' => $gcs->signedUrl($fileName),
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

Route::get('/diagnose-gcs', function() {
    try {
        // Normalize path with forward slashes
        $cacertPath = str_replace('\\', '/', storage_path('app/cacert.pem'));
        
        // Check if cert exists, create if not
        if (!file_exists($cacertPath)) {
            $certDir = dirname($cacertPath);
            if (!is_dir($certDir)) {
                mkdir($certDir, 0755, true);
            }

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            
            $cacert = @file_get_contents('https://curl.se/ca/cacert.pem', false, $context);
            if ($cacert === false) {
                return [
                    'error' => 'Failed to download certificate',
                    'php_error' => error_get_last()
                ];
            }
            file_put_contents($cacertPath, $cacert);
        }
        
        // Check disk configuration
        $diskConfig = config('filesystems.disks.gcs');
        $keyFilePath = str_replace('\\', '/', $diskConfig['key_file']);
        
        // Check if cert is readable
        $certReadable = is_readable($cacertPath);
        $certSize = $certReadable ? filesize($cacertPath) : 0;
        
        // Verify key file
        $keyFileExists = file_exists($keyFilePath);
        $keyFileReadable = $keyFileExists ? is_readable($keyFilePath) : false;
        
        return [
            'certificate' => [
                'path' => $cacertPath,
                'exists' => file_exists($cacertPath),
                'readable' => $certReadable,
                'size' => $certSize,
                'content_start' => $certReadable ? substr(file_get_contents($cacertPath), 0, 100) . '...' : 'not readable'
            ],
            'key_file' => [
                'path' => $keyFilePath,
                'exists' => $keyFileExists,
                'readable' => $keyFileReadable,
                'content_type' => $keyFileReadable ? (json_decode(file_get_contents($keyFilePath)) ? 'valid json' : 'invalid json') : 'not readable'
            ],
            'gcs_config' => [
                'project_id' => $diskConfig['project_id'],
                'bucket' => $diskConfig['bucket']
            ],
            'php_info' => [
                'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'curl not available',
                'openssl_version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'openssl info not available'
            ]
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});
//ENDS HERE

// Authentication routes
Route::get('/login', function () {return view('auth.login'); })->name('login');
Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change.form');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Protected routes
Route::middleware(['auth'])->group(function() {
    // Common authenticated routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change.auth');

    // Employee portal routes
    Route::middleware(['role:employee'])->prefix('employee')->group(function () {

        //The employee view which is located at backend\resources\views\employee\dashboard.blade.php
        Route::get('/dashboard', function () {return view('employee.dashboard');})->name('employee.dashboard');

        Route::get('/apply', [LoanApplicationController::class, 'create'])->name('employee.apply');
        Route::get('/history', function () {return view('employee .history');})->name('employee.history');

        Route::post('/loans', [LoanApplicationController::class, 'store'])->name('employee.loan.store');
        Route::post('/loan/policy', [LoanApplicationController::class, 'storePolicyAcknowledgment'])->name('employee.loan.policy.store');
        Route::get('/loan/{loan}/policy', [LoanApplicationController::class, 'showPolicy'])->name('employee.loan.policy');
        Route::get('/loan/{loan}/pledge', [LoanApplicationController::class, 'showPledgeForm'])->name('employee.loan.pledge');
        Route::post('/loan/pledge', [LoanApplicationController::class, 'storePledge'])->name('employee.loan.pledge.store');
        Route::post('/employee/loan/policy-acknowledge', [LoanApplicationController::class, 'acknowledgePolicyRoute'])->name('employee.loan.acknowledge_policy');
    });

    // HR portal routes
    Route::middleware(['role:hr'])->prefix('hr')->group(function () {
        // HR Dashboard route - pointing to the correct HR-specific view
        Route::get('/dashboard', function () {return view('hr.dashboard');})->name('hr.dashboard');
        
        // Loan application management routes
        Route::get('/loan-applications', function () {return view('hr.dashboard');})->name('hr.loan-applications'); 
        Route::get('/loan-applications/{id}', function ($id) {return view('hr.dashboard', ['loanId' => $id]);})->name('hr.loan-application.detail');
        // Employee management routes
        Route::get('/employees', function () {return view('hr.dashboard');})->name('hr.employees');
        Route::get('/employees/{id}', function ($id) {return view('hr.dashboard', ['employeeId' => $id]);})->name('hr.employee.detail');
        
        // HR Reports route
        Route::get('/reports', [HrController::class, 'showHrReports'])->name('hr.reports'); 
        // Additional HR-specific routes
        Route::get('/departments', function () {return view('hr.dashboard', ['section' => 'departments']);})->name('hr.departments');
    });

    // Admin portal routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        Route::get('/dashboard', function () {return view('admin.dashboard');})->name('admin.dashboard');
        Route::get('/employees', function () {return view('admin.dashboard');})->name('admin.employees');
        Route::get('/reports', function () {return view('admin.reports.admin_reports', ['reportType' => 'admin']);})->name('admin.reports');
        Route::get('/admin/reports', [AdminController::class, 'showAdminReports']);
        
    });
});