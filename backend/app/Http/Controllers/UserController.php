<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $users = User::with('employee')->paginate(10);
        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Comprehensive validation rules
        $request->validate([
            'employee_id' => 'nullable|string',
            'national_id' => 'required_without:employee_id|string|unique:employees,national_id',
            'full_name' => 'required_without:employee_id|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,hr,employee',
        ]);
        
        try {
            DB::beginTransaction();
            
            // First, check for existing employee
            $employee = null;
            $isNewEmployee = false;
            
            // Check if employee exists by employee_id or national_id
            if ($request->has('employee_id')) {
                $employee = Employee::where('employee_id', $request->employee_id)->first();
                
                if (!$employee) {
                    throw new \Exception('Employee not found with the provided ID');
                }
            } elseif ($request->has('national_id')) {
                $employee = Employee::where('national_id', $request->national_id)->first();
                
                if (!$employee) {
                    // Create new employee if not found
                    $employee = Employee::create([
                        'full_name' => $request->full_name,
                        'national_id' => $request->national_id,
                    ]);
                    $isNewEmployee = true;
                }
            } else {
                throw new \Exception('Either employee_id or national_id must be provided');
            }
            
            // Check if employee already has a user account
            if ($employee->user) {
                throw new \Exception('Employee already has a user account');
            }
            
            // Create user
            $user = User::create([
                'login_id' => Str::uuid(),
                'employee_id' => $employee->employee_id,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'password_change_required' => true,
            ]);
            
            // Audit Log using AuditLog model
            AuditLog::log(
                'user_created', 
                'App\Models\User', 
                $user->user_id, 
                'New user account created', 
                null, 
                [
                    'username' => $user->username,
                    'role' => $user->role,
                    'is_new_employee' => $isNewEmployee,
                    'created_by' => auth()->id()
                ]
            );
            
            DB::commit();
            
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'employee' => $employee
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error in audit trail
            AuditLog::log(
                'user_creation_failed', 
                'App\Models\User', 
                null, 
                'Failed to create user', 
                null, 
                [
                    'error_message' => $e->getMessage(),
                    'request_data' => json_encode($request->all()),
                    'created_by' => auth()->id()
                ]
            );
            
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified user
     */
    public function show($login_id)
    {
        $user = User::with('employee')->where('login_id', $login_id)->firstOrFail();
        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $login_id)
    {
        $user = User::where('login_id', $login_id)->firstOrFail();
        
        $request->validate([
            'username' => 'sometimes|string|unique:users,username,' . $user->user_id,
            'role' => 'sometimes|in:admin,hr,employee',
        ]);
        
        $originalData = $user->toArray();
        $user->update($request->only(['username', 'role']));
        
        // Log update
        AuditLog::log(
            'user_updated', 
            'App\Models\User', 
            $user->user_id, 
            'User details updated', 
            $originalData, 
            $user->toArray()
        );
        
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $login_id)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);
        
        $user = User::where('login_id', $login_id)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();
        
        // Log password change
        AuditLog::log(
            'password_changed', 
            'App\Models\User', 
            $user->user_id, 
            'User password changed', 
            null, 
            ['username' => $user->username]
        );
        
        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Admin reset password
     */
    public function adminResetPassword(Request $request, $login_id)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);
        
        $user = User::where('login_id', $login_id)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->password_change_required = true;
        $user->save();
        
        // Log admin password reset
        AuditLog::log(
            'password_reset_by_admin', 
            'App\Models\User', 
            $user->user_id, 
            'Password reset by administrator', 
            null, 
            [
                'username' => $user->username,
                'reset_by' => auth()->id()
            ]
        );
        
        return response()->json([
            'message' => 'Password reset successfully, user will be required to change password on next login'
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy($login_id)
    {
        $user = User::where('login_id', $login_id)->firstOrFail();
        
        // Prevent deletion of last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() === 1) {
            abort(403, 'Cannot delete last admin');
        }

        // Store user data before deletion for audit log
        $userData = $user->toArray();
        
        $user->delete();
        
        // Log user deletion
        AuditLog::log(
            'user_deleted', 
            'App\Models\User', 
            $user->user_id, 
            'User account deleted', 
            $userData, 
            null
        );
        
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}