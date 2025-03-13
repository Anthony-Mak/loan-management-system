<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\BankingDetail;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\Branch;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ApiController extends Controller
{
    protected $pdfService;
    
    public function __construct(PDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }
    
    /**
     * Get all loan types
     */
    public function getLoanTypes()
    {
        $loanTypes = LoanType::all();
        return response()->json($loanTypes);
    }
    
    /**
     * Get all branches
     */
    public function getBranches()
    {
        $branches = Branch::all();
        return response()->json($branches);
    }
    
    /**
     * Submit loan application form without authentication
     * (For direct form submission from frontend)
     */
    public function submitLoanApplication(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
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
            'branch_id' => 'required|exists:branches,branch_id',
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
        ]);
        
        try {
            // Create or update employee
            $employee = Employee::updateOrCreate(
                ['national_id' => $request->national_id],
                [
                    'title' => $request->title,
                    'full_name' => $request->full_name,
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
                    'branch_id' => $request->branch_id,
                    'salary_gross' => $request->salary_gross,
                    'salary_net' => $request->salary_net
                ]
            );
            
            // Create or update banking details
            $bankingDetails = BankingDetail::updateOrCreate(
                ['employee_id' => $employee->employee_id],
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
            
            // Create loan application
            $loanApplication = LoanApplication::create([
                'employee_id' => $employee->employee_id,
                'loan_type_id' => $request->loan_type_id,
                'amount' => $request->loan_amount,
                'term_months' => $request->term_months,
                'purpose' => $request->purpose,
                'status' => 'Pending'
            ]);
            
            // Generate PDF for HR download
            $pdfPath = $this->pdfService->savePDF($loanApplication);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Loan application submitted successfully',
                'loan_id' => $loanApplication->loan_id,
                'pdf_url' => route('api.loan.pdf.download', $loanApplication->loan_id)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit loan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download loan application PDF
     */
    public function downloadLoanPDF($loanId)
{
    try {
        $loanApplication = LoanApplication::where('loan_id', $loanId)->firstOrFail();
        $pdf = $this->pdfService->generateLoanApplicationPDF($loanApplication);
        
        return $pdf->download('loan_application_' . $loanId . '.pdf');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Loan application not found'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to generate PDF',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Check application status
     */
    public function checkApplicationStatus(Request $request)
    {
        $request->validate([
            'national_id' => 'required|string',
            'loan_id' => 'required|integer'
        ]);
        
        $employee = Employee::where('national_id', $request->national_id)->first();
        
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'No employee found with that ID'
            ], 404);
        }
        
        $loanApplication = LoanApplication::where('loan_id', $request->loan_id)
            ->where('employee_id', $employee->employee_id)
            ->first();
            
        if (!$loanApplication) {
            return response()->json([
                'status' => 'error',
                'message' => 'No loan application found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'loan_id' => $loanApplication->loan_id,
            'application_status' => $loanApplication->status,
            'application_date' => $loanApplication->application_date,
            'processed_date' => $loanApplication->processed_date
        ]);
    }
}