<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'audit_log_id';

    protected $fillable = [
        'user_id',
        'employee_id',
        'action_type',
        'model_type',
        'model_id',
        'original_data',
        'updated_data',
        'ip_address',
        'user_agent',
        'description'
    ];

    protected $casts = [
        'original_data' => 'array',
        'updated_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Static method to log actions
    public static function log($actionType, $modelType, $modelId = null, $description = null, $originalData = null, $updatedData = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'employee_id' => auth()->user()?->employee_id,
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'original_data' => $originalData ? json_encode($originalData) : null,
            'updated_data' => $updatedData ? json_encode($updatedData) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description
        ]);
    }
}