<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
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
}