<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankingDetail extends Model
{
    protected $primaryKey = 'banking_id';
    protected $fillable = [
        'employee_id',
        'has_zwmb_account',
        'years_with_zwmb',
        'branch',
        'account_number',
        'had_previous_loan',
        'previous_loan_amount',
        'current_balance',
        'loan_date',
        'maturity_date',
        'monthly_repayment',
        'other_bank',
        'other_account_type'
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}