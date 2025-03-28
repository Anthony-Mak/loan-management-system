<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditLogTrait
{
    // Log model creation
    public static function bootAuditLogTrait()
    {
        static::created(function ($model) {
            AuditLog::log(
                'create',
                get_class($model),
                $model->getKey(),
                "New record created",
                null,
                $model->toArray()
            );
        });

        // Log model updates
        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            AuditLog::log(
                'update',
                get_class($model),
                $model->getKey(),
                "Record updated",
                $original,
                $changes
            );
        });

        // Log model deletions
        static::deleted(function ($model) {
            AuditLog::log(
                'delete',
                get_class($model),
                $model->getKey(),
                "Record deleted",
                $model->toArray(),
                null
            );
        });
    }

    // Manual logging method
    public function logCustomAction($actionType, $description, $data = null)
    {
        AuditLog::log(
            $actionType,
            get_class($this),
            $this->getKey(),
            $description,
            null,
            $data
        );
    }
}