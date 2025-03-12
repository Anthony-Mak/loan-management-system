<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'title' => 'required|string|max:10',
            'full_name' => 'required|string|max:100',
            'national_id' => 'required|string|max:30',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|string|max:10',
            'marital_status' => 'required|in:Married,Single,Divorced,Widowed',
            'dependents' => 'required|integer|min:0',
            'physical_address' => 'required|string',
            'accommodation_type' => 'required|in:Owned,Rented,Employer Provided,Staying with Parents',
            'postal_address' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'cell_phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'next_of_kin' => 'required|string|max:100',
            'next_of_kin_address' => 'required|string',
            'next_of_kin_cell' => 'required|string|max:20',
            
            // Employment Details
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'salary_gross' => 'required|numeric|min:0',
            'salary_net' => 'required|numeric|min:0|lte:salary_gross',
            
            // Banking Details
            'has_zwmb_account' => 'nullable|boolean',
            'years_with_zwmb' => 'nullable|integer|min:0',
            'bank_branch' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'had_previous_loan' => 'nullable|boolean',
            'previous_loan_amount' => 'nullable|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'loan_date' => 'nullable|date',
            'maturity_date' => 'nullable|date|after:loan_date',
            'monthly_repayment' => 'nullable|numeric|min:0',
            'other_bank' => 'nullable|string|max:100',
            'other_account_type' => 'nullable|string|max:50',
            
            // Loan Details
            'loan_type_id' => 'required|exists:loan_types,loan_type_id',
            'loan_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string'
        ];
    }
}
