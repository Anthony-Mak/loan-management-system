<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '/login',
        '/api/login',
        '/api/*',
        'sanctum/csrf-cookie',
        '/api/change-password',
        '/api/log-error',
        '/employee/*',
    ];
}