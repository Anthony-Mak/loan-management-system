<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrController extends Controller
{
    /**
     * Get all loan applications
     */
    public function loanApplications(Request $request)
    {
        $query = LoanApplication::with(['employee', 'loanType'])
            ->orderBy('application_date', 'desc');
            
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $loanApplications = $query->paginate(10);
        
        return response()->json($loanApplications);
    }

    /**
     * Update loan application status - HR can only approve or reject
     */
    public function updateApplication(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'review_notes' => 'nullable|string'
        ]);
        
        $loanApplication = LoanApplication::findOrFail($id);
        
        $loanApplication->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
            'processed_date' => now(),
            'processed_by' => Auth::user()->employee_id
        ]);
        
        return response()->json([
            'message' => 'Loan application updated successfully',
            'loan_application' => $loanApplication
        ]);
    }

    /**
     * Get employees under HR's supervision
     */
    public function employees(Request $request)
    {
        $query = Employee::with('branch');
        
        // Filter by department if provided
        if ($request->has('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }
        
        $employees = $query->paginate(10);
        
        return response()->json($employees);
    }

    /**
     * Get employee details
     */
    public function employeeDetails($id)
    {
        $employee = Employee::with(['bankingDetails', 'loanApplications', 'branch'])
            ->findOrFail($id);
            
        return response()->json($employee);
    }
}