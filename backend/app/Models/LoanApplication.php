<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditLogTrait;
use Carbon\Carbon;

class LoanApplication extends Model
{
    use AuditLogTrait;
    
    protected $primaryKey = 'loan_id';
    
    protected $fillable = [
        'employee_id',
        'loan_type_id',
        'amount',
        'term_months',
        'purpose',
        'status',
        'review_notes',
        'processed_by',
        'pledge_signature',
        'processed_date'
    ];
    
    protected $casts = [
        'application_date' => 'datetime',
        'processed_date' => 'datetime',
        'amount' => 'decimal:2'
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    
    public function loanType()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id', 'loan_type_id');
    }
    
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }
    
    public function scopePeriod($query, $periodRange)
    {
        return $query->whereBetween('application_date', $periodRange);
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'Recommended');
    }
    
    public function scopeRejected($query)
    {
        return $query->where('status', 'Not Recommended');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'Pending Recommendation');
    }
}