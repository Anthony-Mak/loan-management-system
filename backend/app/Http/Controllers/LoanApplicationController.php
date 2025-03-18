<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\BankingDetail;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Http\Requests\LoanApplicationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanApplicationController extends Controller
{
    /**
     * Display a listing of the user's loan applications
     */
    public function history()
    {
        $user = Auth::user();
        $loans = LoanApplication::where('employee_id', $user->employee_id)
            ->with('loanType')
            ->orderBy('application_date', 'desc')
            ->get();
        
        return response()->json($loans);
    }

    /**
     * Store a new loan application
     */
    public function store(LoanApplicationRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            
            // Update or create employee details
            $employee = Employee::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'title' => $request->title,
                    'full_name' => $request->full_name,
                    'national_id' => $request->national_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'marital_status' => $request->marital_status,
                    'dependents' => $request->dependents,
                    'physical_address' => $request->physical_address,
                    'accommodation_type' => $request->accommodation_type,
                    'postal_address' => $request->postal_address,
                    'telephone' => $request->telephone,
                    'cell_phone' => $request->cell_phone,
                    'email' => $request->email,
                    'next_of_kin' => $request->next_of_kin,
                    'next_of_kin_address' => $request->next_of_kin_address,
                    'next_of_kin_cell' => $request->next_of_kin_cell,
                    'position' => $request->position,
                    'department' => $request->department,
                    'salary_gross' => $request->salary_gross,
                    'salary_net' => $request->salary_net
                ]
            );
            
            // Update or create banking details
            BankingDetail::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'has_zwmb_account' => $request->has_zwmb_account ? true : false,
                    'years_with_zwmb' => $request->years_with_zwmb,
                    'branch' => $request->bank_branch,
                    'account_number' => $request->account_number,
                    'had_previous_loan' => $request->had_previous_loan ? true : false,
                    'previous_loan_amount' => $request->previous_loan_amount,
                    'current_balance' => $request->current_balance,
                    'loan_date' => $request->loan_date,
                    'maturity_date' => $request->maturity_date,
                    'monthly_repayment' => $request->monthly_repayment,
                    'other_bank' => $request->other_bank,
                    'other_account_type' => $request->other_account_type
                ]
            );
            
            // Create new loan application
            $loanApplication = LoanApplication::create([
                'employee_id' => $employeeId,
                'loan_type_id' => $request->loan_type_id,
                'amount' => $request->loan_amount,
                'term_months' => $request->term_months,
                'purpose' => $request->purpose,
                'status' => 'Pending'
            ]);
            
            DB::commit();
            
            // Generate PDF for HR download
            $pdf = $this->generatePDF($loanApplication->loan_id);
            
            return response()->json([
                'message' => 'Loan application submitted successfully',
                'loan_id' => $loanApplication->loan_id,
                'pdf_url' => route('loan.pdf.download', $loanApplication->loan_id)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit loan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function create()
    {
        $loanTypes = LoanType::all();
        $employee = Employee::where('employee_id', Auth::user()->employee_id)
        ->with('bankingDetails')
        ->first();
        return view('employee.loan.create', [
            'loanTypes' => $loanTypes,
            'employee' => $employee,
            'bankingDetails' => $employee->bankingDetails ?? null
        ]);
    }
    
    /**
     * Generate PDF for HR download
     */
    private function generatePDF($loanId)
    {
        // Implement PDF generation logic here
        // You can use packages like barryvdh/laravel-dompdf
        return true;
    }

    /**
     * PDF download route
     */
    public function downloadPDF($loanId)
    {
        $loan = LoanApplication::with(['employee', 'employee.bankingDetails', 'loanType'])
            ->findOrFail($loanId);
            
        // Implement PDF generation and download logic
        // Return file download response
    }
}
