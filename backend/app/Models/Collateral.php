<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
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