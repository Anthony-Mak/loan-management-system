<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoanApplicationPolicy 
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool 
    {
        // Allow employees to view their own loans
        return $user->role === 'employee' || $user->role === 'admin' || $user->role === 'hr';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LoanApplication $loanApplication): bool 
    {
        // Employees can only view their own loans
        // Admins and HR can view all loans
        return $user->role === 'admin' || 
               $user->role === 'hr' || 
               $loanApplication->employee_id === $user->employee_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool 
    {
        // Only employees can create loan applications
        return $user->role === 'employee';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LoanApplication $loanApplication): bool 
    {
        // Only HR and Admin can update loan applications
        // Employees cannot update their own applications
        return $user->role === 'admin' || $user->role === 'hr';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LoanApplication $loanApplication): bool 
    {
        // Typically, loan applications should not be deletable
        return false;
    }
}