<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $primaryKey = 'loan_type_id';
    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'max_amount',
        'max_term'
    ];
    
    public function applications()
    {
        return $this->hasMany(LoanApplication::class);
    }
}