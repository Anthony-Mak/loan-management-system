<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
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
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,hr,employee',
        ]);
        
        $user = User::create([
            'login_id' => Str::uuid(),
            'employee_id' => $request->employee_id,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
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
        
        $user->update($request->only(['username', 'role']));
        
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
        
        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy($login_id)
    {
        $user = User::where('login_id', $login_id)->firstOrFail();
        $user->delete();
        
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}