<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\LoanType;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Traits\AuditLogTrait;
use App\Models\AuditLog;

class AdminController extends Controller
{
    use AuditLogTrait;
    
    /**
     * Create a new user in the system
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'username' => 'required|unique:users,username',
            'role' => ['required', Rule::in(['admin', 'hr', 'employee'])],
            'temporary_password' => 'required|min:8'
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'employee_id' => $request->employee_id,
                'username' => $request->username,
                'password' => Hash::make($request->temporary_password),
                'role' => $request->role,
                'password_change_required' => true
            ]);

            // Log user creation using the AuditLog model
            $user->logCustomAction(
                'create_user', 
                "Created new user: {$user->username}", 
                [
                    'username' => $user->username,
                    'role' => $user->role,
                    'employee_id' => $user->employee_id
                ]
            );

            return $user;
        });

        return response()->json($user, 201);
    }

    /**
     * Create a new loan type
     */
    public function createLoanType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:loan_types,name',
            'interest_rate' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'max_term' => 'required|integer|min:1'
        ]);

        $loanType = LoanType::create($validated);

        // Log loan type creation
        $loanType->logCustomAction(
            'create_loan_type', 
            "Created new loan type: {$loanType->name}", 
            $validated
        );

        return response()->json($loanType, 201);
    }

    /**
     * Update branch information
     */
    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'branch_name' => 'required',
            'location' => 'required'
        ]);

        // Capture original data before update
        $originalData = $branch->toArray();

        $branch->update($validated);

        // Log branch update
        $branch->logCustomAction(
            'update_branch', 
            "Updated branch: {$branch->branch_name}", 
            [
                'original_data' => $originalData,
                'updated_data' => $validated
            ]
        );

        return response()->json($branch);
    }

    /**
     * Get system configuration details
     */
    public function getSystemConfig()
    {
        // Log system configuration access
        $this->logCustomAction(
            'access_system_config', 
            'Accessed system configuration',
            [
                'loan_types_count' => LoanType::count(),
                'branches_count' => Branch::count()
            ]
        );

        return response()->json([
            'loan_types' => LoanType::all(),
            'branches' => Branch::all(),
            'user_roles' => ['admin', 'hr', 'employee']
        ]);
    }

    /**
     * Get all employees with optional filtering
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
     * Get detailed employee information
     */
    public function employeeDetails($id)
    {
        $employee = Employee::with(['bankingDetails', 'branch'])
            ->findOrFail($id);
        
        return response()->json($employee);
    }

    /**
     * View system audit logs (admin-specific feature)
     */
    public function viewAuditLogs(Request $request)
    {
        $query = AuditLog::query();

        // Optional filtering
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 50));

        // Log the audit log access
        $this->logCustomAction(
            'view_audit_logs',
            'Accessed system audit logs',
            [
                'log_count' => $logs->total(),
                'filter_params' => $request->only(['action_type', 'start_date', 'end_date'])
            ]
        );

        return response()->json($logs);
    }
}