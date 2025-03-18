<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
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
     * Update loan application status
     */
    public function updateApplication(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected,Pending',
            'review_notes' => 'nullable|string|max:500'
        ]);
        
        $loan = LoanApplication::with(['employee', 'processedBy'])
        ->findOrFail($id);
        
        $loan->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
            'processed_by' => Auth::user()->employee_id,
            'processed_date' => now()
        ]);

        
       
        return response()->json([
            'success' => true,
            'loan' => $loan,
            'message' => 'Loan application updated successfully'
        ]);
    
    }
    
    /**
     * Get all employees
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

    /**
     * Generate loan report
     */
    public function loanReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|in:Pending,Approved,Rejected'
        ]);
        
        $query = LoanApplication::with(['employee', 'loanType'])
            ->whereBetween('application_date', [$request->start_date, $request->end_date]);
            
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $loanApplications = $query->get();
        
        $totalAmount = $loanApplications->sum('amount');
        $approvedAmount = $loanApplications->where('status', 'Approved')->sum('amount');
        $rejectedAmount = $loanApplications->where('status', 'Rejected')->sum('amount');
        $pendingAmount = $loanApplications->where('status', 'Pending')->sum('amount');
        
        return response()->json([
            'total_applications' => $loanApplications->count(),
            'approved_applications' => $loanApplications->where('status', 'Approved')->count(),
            'rejected_applications' => $loanApplications->where('status', 'Rejected')->count(),
            'pending_applications' => $loanApplications->where('status', 'Pending')->count(),
            'total_amount' => $totalAmount,
            'approved_amount' => $approvedAmount,
            'rejected_amount' => $rejectedAmount,
            'pending_amount' => $pendingAmount,
            'applications' => $loanApplications
        ]);
    }

}
