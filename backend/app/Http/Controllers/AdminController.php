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
use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    /**
     * Constructor to ensure admin authorization
     */
   
    
    /**
     * Display admin dashboard with key metrics
     */
    public function dashboard()
    {
        $metrics = [
            'total_employees' => Employee::count(),
            'total_users' => User::count(),
            'total_branches' => Branch::count(),
            'total_loan_types' => LoanType::count(),
            'recent_users' => User::with('employee')->orderBy('created_at', 'desc')->take(5)->get(),
            'recent_audit_logs' => AuditLog::orderBy('created_at', 'desc')->take(10)->get(),
        ];
        
        return response()->json($metrics);
    }

    /**
     * Create a new employee record
     */
    public function createEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|unique:employees,employee_id',
            'title' => 'nullable|string|max:10',
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:employees,national_id',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'required|string',
            'dependents' => 'nullable|integer|min:0',
            'physical_address' => 'required|string',
            'accommodation_type' => 'nullable|string',
            'postal_address' => 'nullable|string',
            'cell_phone' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'next_of_kin' => 'required|string',
            'next_of_kin_address' => 'nullable|string',
            'next_of_kin_cell' => 'required|string',
            'branch_id' => 'required|exists:branches,branch_id',
            'salary_gross' => 'required|numeric|min:0',
            'salary_net' => 'required|numeric|min:0',
            'department' => 'required|string|max:50',
            'position' => 'required|string|max:50',
            'hire_date' => 'required|date',
        ]);

        $employee = DB::transaction(function () use ($request) {
            $employee = Employee::create($request->all());
            
            // Log employee creation
            AuditLog::log(
                'create_employee',
                Employee::class,
                $employee->employee_id,
                "Created new employee: {$employee->employee_id}",
                null,
                [
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                    'email' => $employee->email
                ]
            );
            
            return $employee;
        });

        return response()->json($employee, 201);
    }

    /**
     * Create a new user in the system
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',  
            'username' => 'required|unique:users,username',
            'role' => ['required', Rule::in(['admin', 'hr', 'employee'])],
            'password' => 'required|min:8',
            'is_active' => 'sometimes|boolean'
        ]);
        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return response()->json(['message' => 'Invalid employee selected'], 422);
        }

        // Check if employee already has a user account
        $existingUser = User::where('employee_id', $request->employee_id)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'This employee already has a user account',
                'user' => $existingUser
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $userData = [
                'employee_id' => $request->employee_id,
                'username' => $request->username,
                'login_id' => $request->login_id ?? Str::uuid(),
                'password' => Hash::make($request->password ?? 'Pass@123'),
                'role' => $request->role,
                'password_change_required' => true,
                'is_active' => $request->has('is_active') ? $request->is_active : true
            ];
            
            $user = User::create($userData);

            // Log user creation using the AuditLog model
            AuditLog::log(
                'create_user',
                User::class,
                $user->user_id,
                "Created new user: {$user->username}",
                null,
                [
                    'username' => $user->username,
                    'role' => $user->role,
                    'employee_id' => $user->employee_id,
                    'is_active' => $user->is_active
                ]
            );

            return $user;
        });

        return response()->json($user, 201);
    }
    
    /**
     * Reset user password
     */
    public function resetUserPassword($userId)
    {
        $user = User::findOrFail($userId);
        
        // Use default password Pass@123 instead of random string
        $newPassword = 'Pass@123';
        
        $user->password = Hash::make($newPassword);
        $user->password_change_required = true;
        $user->save();
        
        // Log password reset
        AuditLog::log(
            'reset_password',
            User::class,
            $user->user_id,
            "Admin reset password for user: {$user->username}",
            null,
            [
                'username' => $user->username,
                'reset_by' => auth()->id()
            ]
        );
        
        return response()->json([
            'message' => 'Password reset successful',
            'temporary_password' => $newPassword
        ]);
    }
    
    /**
     * Update user status (enable/disable)
     */
    public function updateUserStatus(Request $request, $userId)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);
        
        $user = User::findOrFail($userId);
        $previousStatus = $user->is_active;
        
        $user->is_active = $request->is_active;
        $user->save();
        
        // Log status change
        $statusAction = $request->is_active ? 'enabled' : 'disabled';
        AuditLog::log(
            'update_user_status',
            User::class,
            $user->user_id,
            "Admin {$statusAction} user: {$user->username}",
            ['is_active' => $previousStatus],
            [
                'username' => $user->username,
                'previous_status' => $previousStatus,
                'new_status' => $user->is_active,
                'updated_by' => auth()->id()
            ]
        );
        
        return response()->json([
            'message' => "User {$statusAction} successfully",
            'user' => $user
        ]);
    }
    
    /**
     * Update user role
     */
    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'hr', 'employee'])]
        ]);
        
        $user = User::findOrFail($userId);
        $previousRole = $user->role;
        
        $user->role = $request->role;
        $user->save();
        
        // Log role change
        AuditLog::log(
            'update_user_role',
            User::class,
            $user->user_id,
            "Changed user role for: {$user->username}",
            ['role' => $previousRole],
            [
                'username' => $user->username,
                'previous_role' => $previousRole,
                'new_role' => $user->role,
                'updated_by' => auth()->id()
            ]
        );
        
        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }

    /**
     * List all users with pagination and optional filtering
     */
    public function getUsers(Request $request)
    {
        $query = User::with('employee');
        
        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Filter by username if search term provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $users = $query->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
                       ->paginate($request->get('per_page', 15));
        
        return response()->json($users);
    }

    /**
     * Create a new loan type
     */
    public function createLoanType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:loan_types,name',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'max_amount' => 'required|numeric|min:0',
            'max_term' => 'required|integer|min:1'
        ]);

        $loanType = LoanType::create($validated);

        // Log loan type creation
        AuditLog::log(
            'create_loan_type',
            LoanType::class,
            $loanType->loan_type_id,
            "Created new loan type: {$loanType->name}",
            null,
            $validated
        );

        return response()->json($loanType, 201);
    }
    
    /**
     * Update loan type details
     */
    public function updateLoanType(Request $request, $id)
    {
        $loanType = LoanType::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', Rule::unique('loan_types', 'name')->ignore($loanType->loan_type_id, 'loan_type_id')],
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'max_amount' => 'required|numeric|min:0',
            'max_term' => 'required|integer|min:1'
        ]);
        
        // Capture original data before update
        $originalData = $loanType->toArray();
        
        $loanType->update($validated);
        
        // Log loan type update
        AuditLog::log(
            'update_loan_type',
            LoanType::class,
            $loanType->loan_type_id,
            "Updated loan type: {$loanType->name}",
            $originalData,
            $validated
        );
        
        return response()->json($loanType);
    }
    
    /**
     * List all loan types
     */
    public function getLoanTypes()
    {
        $loanTypes = LoanType::all();
        return response()->json($loanTypes);
    }

    /**
     * Create a new branch
     */
    public function createBranch(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|unique:branches,branch_name',
            'location' => 'required|string',
            'branch_code' => 'nullable|string|unique:branches,branch_code'
        ]);
        
        $branch = Branch::create($validated);
        
        // Log branch creation
        AuditLog::log(
            'create_branch',
            Branch::class,
            $branch->branch_id,
            "Created new branch: {$branch->branch_name}",
            null,
            $validated
        );
        
        return response()->json($branch, 201);
    }

    /**
     * Update branch information
     */
    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'branch_name' => ['required', Rule::unique('branches', 'branch_name')->ignore($branch->branch_id, 'branch_id')],
            'location' => 'required|string',
            'branch_code' => ['nullable', 'string', Rule::unique('branches', 'branch_code')->ignore($branch->branch_id, 'branch_id')]
        ]);

        // Capture original data before update
        $originalData = $branch->toArray();

        $branch->update($validated);

        // Log branch update
        AuditLog::log(
            'update_branch',
            Branch::class,
            $branch->branch_id,
            "Updated branch: {$branch->branch_name}",
            $originalData,
            $validated
        );

        return response()->json($branch);
    }
    
    /**
     * List all branches
     */
    public function getBranches()
    {
        $branches = Branch::all();
        return response()->json($branches);
    }

    /**
     * Get system configuration details
     */
    public function getSystemConfig()
    {
        // Log system configuration access
        AuditLog::log(
            'access_system_config',
            'AdminController',
            null,
            'Accessed system configuration',
            null,
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
    public function getEmployees(Request $request)
    {
        $query = Employee::with('branch');
        
        // Filter by department if provided
        if ($request->has('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }
        
        // Filter by branch if provided
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Filter by search term in name or employee_id
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->orderBy($request->get('sort_by', 'full_name'), $request->get('sort_order', 'asc'))
                          ->paginate($request->get('per_page', 15));
        
        return response()->json($employees);
    }
    
    public function getLoanType($id)
    {
        $loanType = LoanType::findOrFail($id);
        return response()->json($loanType);
    }

    /**
     * Get detailed employee information
     */
    public function getEmployeeDetails($id)
    {
        $employee = Employee::with([
                'branch', 
                'user' => function($query) {
                    $query->select('user_id', 'employee_id', 'username', 'role', 'is_active', 'last_login');
                }
            ])
            ->findOrFail($id);
        
        // Log employee details access
        AuditLog::log(
            'view_employee_details',
            Employee::class,
            $employee->employee_id,
            "Viewed employee details: {$employee->full_name}",
            null,
            ['employee_id' => $employee->employee_id]
        );
        
        return response()->json($employee);
    }
    
    /**
     * Update employee information
     */
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'nullable|string|max:10',
            'full_name' => 'required|string|max:255',
            'national_id' => ['required', 'string', Rule::unique('employees', 'national_id')->ignore($employee->employee_id, 'employee_id')],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'required|string',
            'dependents' => 'nullable|integer|min:0',
            'physical_address' => 'required|string',
            'accommodation_type' => 'nullable|string',
            'postal_address' => 'nullable|string',
            'cell_phone' => 'required|string',
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($employee->employee_id, 'employee_id')],
            'next_of_kin' => 'required|string',
            'next_of_kin_address' => 'nullable|string',
            'next_of_kin_cell' => 'required|string',
            'branch_id' => 'required|exists:branches,branch_id',
            'salary_gross' => 'required|numeric|min:0',
            'salary_net' => 'required|numeric|min:0',
            'department' => 'required|string|max:50',
            'position' => 'required|string|max:50',
            'hire_date' => 'required|date',
        ]);
        
        // Capture original data before update
        $originalData = $employee->toArray();
        
        $employee->update($validated);
        
        // Log employee update
        AuditLog::log(
            'update_employee',
            Employee::class,
            $employee->employee_id,
            "Updated employee: {$employee->full_name}",
            $originalData,
            [
                'employee_id' => $employee->employee_id,
                'updated_fields' => array_keys($validated)
            ]
        );
        
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

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
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
        AuditLog::log(
            'view_audit_logs',
            'AdminController',
            null,
            'Accessed system audit logs',
            null,
            [
                'log_count' => $logs->total(),
                'filter_params' => $request->only(['action_type', 'entity_type', 'user_id', 'start_date', 'end_date'])
            ]
        );

        return response()->json($logs);
    }
    
    /**
     * Get available audit log action types for filtering
     */
    public function getAuditLogActionTypes()
    {
        $actionTypes = AuditLog::distinct()->pluck('action_type');
        return response()->json($actionTypes);
    }
    
    /**
     * Get available audit log entity types for filtering
     */
    public function getAuditLogEntityTypes()
    {
        $entityTypes = AuditLog::distinct()->pluck('entity_type');
        return response()->json($entityTypes);
    }
}