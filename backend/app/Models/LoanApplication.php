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
    public function scopePeriod($query, $periodRange)
{
    return $query->whereBetween('application_date', $periodRange);
}

public function scopeApproved($query)
{
    return $query->where('status', 'Approved');
}

public function scopeRejected($query)
{
    return $query->where('status', 'Rejected');
}

public function scopePending($query)
{
    return $query->where('status', 'Pending');
}
public function processedBy()
{
    return $this->belongsTo(User::class, 'processed_by', 'user_id');
}
}