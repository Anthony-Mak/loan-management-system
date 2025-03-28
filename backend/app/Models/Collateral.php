<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditLogTrait;

class Collateral extends Model
{
    use AuditLogTrait;
    protected $fillable = [
        'loan_id',
        'asset_description',
        'estimated_value',
        'vehicle_registration_number',
        'signature',
        'location'
    ];

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_id');
    }
}