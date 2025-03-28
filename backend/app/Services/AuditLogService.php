<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    // Log authentication events
    public function logLogin($user)
    {
        AuditLog::log(
            'login',
            'App\Models\User',
            $user->user_id,
            "User logged in",
            null,
            [
                'username' => $user->username,
                'role' => $user->role
            ]
        );
    }

    public function logLogout($user)
    {
        AuditLog::log(
            'logout',
            'App\Models\User',
            $user->user_id,
            "User logged out",
            null,
            [
                'username' => $user->username,
                'role' => $user->role
            ]
        );
    }

    // Log password-related events
    public function logPasswordChange($user)
    {
        AuditLog::log(
            'password_change',
            'App\Models\User',
            $user->user_id,
            "Password changed",
            null,
            ['username' => $user->username]
        );
    }

    public function logPasswordReset($user)
    {
        AuditLog::log(
            'password_reset',
            'App\Models\User',
            $user->user_id,
            "Password reset by admin",
            null,
            ['username' => $user->username]
        );
    }

    // Generic method for logging custom events
    public function logCustomEvent($actionType, $modelType, $modelId, $description, $data = null)
    {
        AuditLog::log(
            $actionType,
            $modelType,
            $modelId,
            $description,
            null,
            $data
        );
    }
}