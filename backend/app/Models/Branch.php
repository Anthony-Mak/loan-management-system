<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasApiTokens, 
    Notifiable, 
    SoftDeletes, 
    AuditLogTrait;

    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_name',
        'location',
        'branch_code'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function employee(){ 
        return $this->hasOne(Employee::class);
    }
}
