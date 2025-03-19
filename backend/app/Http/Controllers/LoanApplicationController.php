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
use App\Models\Collateral;

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

            // Redirect to the loan policy page with the loan ID
            return redirect()->route('employee.loan.policy', ['loan' => $loanApplication->loan_id]);

            
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

    public function showPolicy(Request $request, $loanId)
    {
        $loan = LoanApplication::findOrFail($loanId);
    
        // Check if this loan belongs to the authenticated user
        if ($loan->employee_id !== Auth::user()->employee_id) {
            abort(403);
        }
        return view('employee.loan.loan_policy', compact('loan'));
    }

    /**
 * Show the pledge form
 */
    public function showPledgeForm(Request $request, $loanId)
    {
        $loan = LoanApplication::with(['employee', 'loanType'])
        ->findOrFail($loanId);
        
        // Check if this loan belongs to the authenticated user
        if ($loan->employee_id !== Auth::user()->employee_id) {
            abort(403);
        }
    
        // Check if the policy has been acknowledged
        if (!$loan->policy_acknowledged) {
            return redirect()->route('employee.loan.policy', ['loan' => $loan->loan_id])
                ->with('error', 'You must acknowledge the loan policy before proceeding to the pledge form.');
            }
            
            return view('employee.loan.pledge_form', compact('loan'));
    }


    public function storePledge(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loan_applications,loan_id',
            'name' => 'required|string|max:100',
            'national_id' => 'required|string|max:30',
            'address' => 'required|string',
            'location' => 'required|string|max:100',
            'signature' => 'required|string|max:100',
            'registration_number' => 'nullable|string|max:20',
            'assets' => 'required|array',
            'assets.*.description' => 'nullable|string',
            'assets.*.value' => 'nullable|numeric'
        ]);
        
        $loan = LoanApplication::findOrFail($request->loan_id);
    
        // Check if this loan belongs to the authenticated user
        if ($loan->employee_id !== Auth::user()->employee_id) {
            abort(403);
        }
        
        DB::beginTransaction();
        try {
            // Update loan with pledge acknowledgment
            $loan->update([
                'pledge_acknowledged' => true,
                'pledge_signature' => $request->signature,
                'pledge_date' => now()
            ]);
        
            // Store the assets as collateral
            foreach ($request->assets as $asset) {
                if (!empty($asset['description']) && !empty($asset['value'])) {
                    Collateral::create([
                        'loan_id' => $loan->loan_id,
                        'asset_description' => $asset['description'],
                        'estimated_value' => $asset['value'],
                        'vehicle_registration_number' => $request->registration_number ?? null,
                        'signature' => $request->signature,
                        'location' => $request->location
                    ]);
                }
            }
            DB::commit();
        
            // Update loan status to "Submitted"
            $loan->update(['status' => 'Submitted']);
            return redirect()->route('employee.dashboard')
                ->with('success', 'Your loan application has been successfully submitted with the pledged assets.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit pledge information: ' . $e->getMessage())->withInput();
        }
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
