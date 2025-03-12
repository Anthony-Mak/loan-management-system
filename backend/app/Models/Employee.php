<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'title',
        'full_name',
        'national_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'dependents',
        'physical_address',
        'accommodation_type',
        'postal_address',
        'telephone',
        'cell_phone',
        'email',
        'next_of_kin',
        'next_of_kin_address',
        'next_of_kin_cell',
        'position',
        'department',
        'branch_id',
        'salary_gross',
        'salary_net'
    ];

    public function bankingDetails()
    {
        return $this->hasOne(BankingDetail::class, 'employee_id', 'employee_id');
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplication::class, 'employee_id', 'employee_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'employee_id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}
