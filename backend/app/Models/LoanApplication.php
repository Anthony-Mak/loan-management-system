<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    protected $primaryKey = 'loan_id'; // Specify the primary key column name
    
    protected $fillable = [
        'employee_id',
        'loan_type_id',
        'amount',
        'term_months',
        'purpose',
        'status',
        'review_notes',
        'processed_by',
        'processed_date'
        
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }
}