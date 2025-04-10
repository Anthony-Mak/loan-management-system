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
use \Log;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanApplicationController extends Controller
{
    /**
     * Display a listing of the user's loan applications
     */
    public function __construct()
{
    Log::debug('Database configuration:', [
        'connection' => config('database.default'),
        'database' => config('database.connections.' . config('database.default') . '.database'),
        'strict' => config('database.connections.' . config('database.default') . '.strict')
    ]);
}
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

            Log::debug('Processing loan application:', [
                'employee_id' => $user->employee_id,
                'request_data' => $request->all()
            ]);
             // Update or create employee details
        Log::debug('Attempting to update employee record:', [
            'employee_id' => $user->employee_id
        ]);
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
            Log::debug('Employee record processed successfully:', [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name
            ]);

            Log::debug('Attempting to update banking details:', [
                'employee_id' => $user->employee_id
            ]);
            
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
            
            Log::debug('Creating new loan application:', [
                'loan_type_id' => $request->loan_type_id,
                'amount' => $request->loan_amount,
                'term_months' => $request->term_months
            ]);
            
            
            // Create new loan application
            $loanApplication = LoanApplication::create([
                'employee_id' => $employeeId,
                'loan_type_id' => $request->loan_type_id,
                'amount' => $request->loan_amount,
                'term_months' => $request->term_months,
                'purpose' => $request->purpose,
                'status' => 'Pending'
            ]);

            Log::debug('Loan application created successfully:', [
                'loan_id' => $loanApplication->loan_id,
                'amount' => $loanApplication->amount,
                'status' => $loanApplication->status
            ]);
            
            DB::commit();
            Log::info('Loan application created successfully', [
                'loan_id' =>$loanApplication->loan_id]);

                Log::debug('Preparing to redirect user:', [
                    'loan_id' => $loanApplication->loan_id,
                    'redirect_path' => route('employee.loan.policy', ['loan' => $loanApplication->loan_id])
                ]);
            // Redirect to the loan policy page with the loan ID
            return redirect()->route('employee.loan.policy', ['loan' => $loanApplication->loan_id])
            ->with('success', 'Loan application submitted successfully.');

            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create loan application:', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
            return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to submit loan application: ' . $e->getMessage());
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

public function storePolicyAcknowledgment(Request $request)
{
    Log::info('Policy Acknowledgment Method Called', [
        'request_data' => $request->all(),
        'user_id' => Auth::id(),
        'employee_id' => Auth::user()->employee_id
    ]);

    try {
        $request->validate([
            'loan_id' => 'required|exists:loan_applications,loan_id',
            'signature' => 'required|string|max:100'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation Failed in Policy Acknowledgment', [
            'errors' => $e->errors(),
            'input' => $request->all()
        ]);
        return back()->withErrors($e->validator)->withInput();
    }
    
    $loan = LoanApplication::findOrFail($request->loan_id);
    
    Log::info('Loan Details Found', [
        'loan_id' => $loan->loan_id,
        'employee_id' => $loan->employee_id,
        'current_user_employee_id' => Auth::user()->employee_id
    ]);

    // Check if this loan belongs to the authenticated user
    if ($loan->employee_id !== Auth::user()->employee_id) {
        Log::error('Unauthorized policy acknowledgment attempt', [
            'loan_id' => $request->loan_id,
            'user_id' => Auth::user()->id,
            'employee_id' => Auth::user()->employee_id
        ]);
        abort(403);
    }
    
    Log::info('Redirecting to Pledge Form', [
        'loan_id' => $loan->loan_id,
        'route' => route('employee.loan.pledge', ['loan' => $loan->loan_id])
    ]);
    
    return redirect()->route('employee.loan.pledge', ['loan' => $loan->loan_id])
        ->with('success', 'Proceeding to pledge form.');
}
 
public function showPledgeForm(Request $request, $loanId)
{
    Log::info('Pledge Form Method Called', [
        'loan_id' => $loanId,
        'current_user_id' => Auth::id(),
        'current_employee_id' => Auth::user()->employee_id
    ]);

    try {
        $loan = LoanApplication::with(['employee', 'loanType'])
            ->findOrFail($loanId);
        
        Log::info('Loan Retrieved for Pledge Form', [
            'loan_details' => [
                'id' => $loan->loan_id,
                'employee_id' => $loan->employee_id,
                'amount' => $loan->amount
            ]
        ]);
        
        // Check if this loan belongs to the authenticated user
        if ($loan->employee_id !== Auth::user()->employee_id) {
            Log::warning('Unauthorized access attempt to pledge form', [
                'loan_id' => $loanId,
                'requesting_user_id' => Auth::id(),
                'loan_owner_id' => $loan->employee_id
            ]);
            abort(403, 'Unauthorized access');
        }

        return view('employee.loan.pledge_form', compact('loan'));
    } catch (\Exception $e) {
        Log::error('Error in Pledge Form Method', [
            'error_message' => $e->getMessage(),
            'loan_id' => $loanId
        ]);
        
        return redirect()->route('employee.dashboard')
            ->with('error', 'Unable to load pledge form: ' . $e->getMessage());
    }
}

public function storePledge(Request $request)
{
    $validated = $request->validate([
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
        abort(403, 'Unauthorized access');
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
        $hasValidAsset = false;
        foreach ($request->assets as $asset) {
            if (!empty($asset['description']) && !empty($asset['value'])) {
                Collateral::create([
                    'loan_id' => $loan->loan_id,
                    'asset_description' => $asset['description'],
                    'estimated_value' => $asset['value'],
                    'vehicle_registration_number' => $request->registration_number,
                    'signature' => $request->signature,
                    'location' => $request->location
                ]);
                $hasValidAsset = true;
            }
        }

        if (!$hasValidAsset) {
            throw new \Exception('At least one asset must be pledged.');
        }

        // Update loan status to "Submitted"
        $loan->update(['status' => 'pending']);

        DB::commit();

        Log::info('Pledge Submission Successful', [
            'loan_id' => $loan->loan_id,
            'assets_count' => count($request->assets),
            'redirect_route' => 'employee.dashboard'
        ]);

        return redirect()->route('employee.dashboard')
            ->with('success', 'Loan application has been successfully submitted with pledged assets.');
    } catch (\Exception $e) {
        Log::error('Pledge Submission Failed', [
            'error' => $e->getMessage(),
            'loan_id' => $request->loan_id
        ]);
        DB::rollBack();
        return back()
            ->withInput()
            ->with('error', 'Failed to submit pledge information: ' . $e->getMessage());
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
    $loan = LoanApplication::with([
        'employee', 
        'employee.bankingDetails', 
        'loanType'
    ])->findOrFail($loanId);
    
    // Authorization check remains the same
    if (Auth::user()->employee_id !== $loan->employee_id && Auth::user()->role !== 'hr') {
        abort(403, 'Unauthorized access');
    }

    if (!$loan->employee) {
        return back()->with('error', 'Employee data not found for this loan application.');
    }
    
    $collaterals = Collateral::where('loan_id', $loanId)->get();
    
    //Check if employee exists before accessing bankingDetails
    $data = [
        'loan' => $loan,
        'employee' => $loan->employee,
        'bankingDetails' => $loan->employee ? $loan->employee->bankingDetails : null,
        'collaterals' => $collaterals
    ];
    
    $pdf = Pdf::loadView('pdf.loan-application', $data);
    
    return $pdf->download('loan_application_'.$loanId.'.pdf');
}
}
