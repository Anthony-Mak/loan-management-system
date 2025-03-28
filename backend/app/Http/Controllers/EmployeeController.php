<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;


class EmployeeController extends Controller
{
    /**
     * Get the authenticated employee's profile
     */
    public function show()
    {
        $user = Auth::user();
        $employee = Employee::with(['bankingDetails', 'branch'])
            ->where('employee_id', $user->employee_id)
            ->firstOrFail();
            
        return response()->json($employee);
    }

    /**
     * Update the authenticated employee's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('employee_id', $user->employee_id)->firstOrFail();
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:10',
            'full_name' => 'sometimes|string|max:100',
            'physical_address' => 'sometimes|string',
            'postal_address' => 'sometimes|nullable|string',
            'telephone' => 'sometimes|nullable|string|max:20',
            'cell_phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|nullable|email|max:100',
            'next_of_kin' => 'sometimes|string|max:100',
            'next_of_kin_address' => 'sometimes|string',
            'next_of_kin_cell' => 'sometimes|string|max:20',
        ]);
        
        $employee->update($validated);
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'employee' => $employee
        ]);
    }
    public function getLoans(Request $request)
    {
        $loans = LoanApplication::where('employee_id', Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->get();
        $stats = [
            'total' => $loans->count(),
            'pending' => $loans->where('status', 'Pending')->count(),
            'approved' => $loans->where('status', 'Approved')->count(),
            'rejected' => $loans->where('status', 'Rejected')->count(),
        ];
        return response()->json([
            'loans' => $loans,
            'stats' => $stats
        ]);
    }
    public function getLoanDetails($id)
    {
        $loan = LoanApplication::with(['employee', 'processedBy'])
        ->where('employee_id', Auth::user()->employee_id)
        ->findOrFail($id);
        return response()->json($loan);
    }

    
}