<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
{
    try {
        // Log the request data
        \Log::debug('Login attempt:', [
            'username' => $request->username,
            // Don't log passwords
        ]);
        
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Log successful authentication
            \Log::debug('Authentication successful for user:', [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->role,
            ]);
            
            // Create token for API access
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role,
                ]
            ]);
        }
        
        // Authentication failed
        \Log::warning('Authentication failed for username:', [
            'username' => $request->username,
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid username or password'
        ], 401);
    } catch (\Exception $e) {
        // Log the exception
        \Log::error('Login exception:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred during login. Please try again.',
            'debug' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
    public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed',
    ]);
    
    $user = Auth::user();
    
    // Check if current password is correct
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Current password is incorrect'
        ], 401);
    }
    
    // Update password
    $user->password = Hash::make($request->new_password);
    $user->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        // For web sessions
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}