<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use \Log;

class AuthController extends Controller
{
    public function login(Request $request)
{
    try {
        // Log the request data
        Log::debug('Login attempt:', [
            'username' => $request->username,
            // Don't log passwords
        ]);
        
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Log successful authentication
            Log::debug('Authentication successful for user:', [
                'login_id' => $user->login_id,
                'username' => $user->username,
                'role' => $user->role,
            ]);

            $request->session()->regenerate();
            
            // Check if password change is required
            if ($user->password_change_required) {
                // For API requests, return the token
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'password_change_required' => true,
                        'token' => $user->createToken('auth_token')->plainTextToken,
                        'user' => [
                            'username' => $user->username,
                            'role' => $user->role
                        ]
                    ]);
                }
                
                // For web requests, redirect to password change form
                $token = $user->createToken('auth_token')->plainTextToken;
                return redirect()->route('password.change.form', [
                    'username' => $user->username,
                    'token' => $token,
                    'role' => $user->role
                ]);
            }

            // Handle successful login without password change requirement
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'user' => [
                        'username' => $user->username,
                        'role' => $user->role,
                    ],
                    'token' => $user->createToken('auth_token')->plainTextToken
                ]);
            }

            // Web login redirect
            return redirect()->intended($this->getRedirectPath($user->role));
        }
        
        // Authentication failed
        Log::warning('Authentication failed for username:', [
            'username' => $request->username,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password'
            ], 401);
        }
        
        return back()->withErrors([
            'username' => 'Invalid username or password',
        ]);
        
    } catch (\Exception $e) {
        // Log the exception
        Log::error('Login exception:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
        
        return back()->withErrors([
            'error' => 'An error occurred during login. Please try again.',
        ]);
    }
}

// Helper method to get redirect path based on role
private function getRedirectPath($role)
{
    return match($role) {
        'employee' => '/employee/dashboard',
        'admin' => '/admin/dashboard',
        'hr' => '/hr/dashboard',
        default => '/'
    };
}

public function changePassword(Request $request)
{
    try {
        // Log request information for debugging
        Log::debug('Change password request info:', [
            'has_bearer' => $request->bearerToken() ? 'yes' : 'no',
            'is_api' => $request->expectsJson(),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
        ]);
        
        // Validate request
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|same:new_password',
        ]);
        
        // Get user from token, session, or from username parameter for first-time login
        $user = $request->user();
        $tokenModel = null; 
        
        // If no authenticated user found but username and token provided (first-time form)
        if (!$user && $request->has('username') && ($request->has('token') || $request->bearerToken())) {
            // Extract token value (remove "Bearer " if present)
            $tokenValue = $request->input('token');

            if (!$tokenValue) {
                $tokenValue = $request->bearerToken();
            }
            $tokenValue = str_replace('Bearer ', '', $tokenValue);
            $tokenValue = urldecode($tokenValue);
            
            // Find the token in the database
            $tokenModel = PersonalAccessToken::findToken($tokenValue);
            
            if ($tokenModel) {
                $user = User::find($tokenModel->tokenable_id);
                Log::debug('User found via token for password change', [
                    'username' => $user->username ?? 'not found',
                ]);
            }
        }
        
        if (!$user) {
            Log::warning('No authenticated user found for password change');
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            return redirect()->route('login')
                ->withErrors(['auth' => 'Authentication required']);
        }
        
        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            Log::warning('Invalid current password for user', ['login_id' => $user->id]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            
            return back()->withErrors([
                'current_password' => 'Current password is incorrect'
            ]);
        }
        
        // Update password and set password_change_required to false
        $updates = [
            'password' => Hash::make($validated['new_password']),
            'password_change_required' => false
        ];
        
        // Log before update
        Log::info('Before updating user password change', [
            'login_id' => $user->id,
            'username' => $user->username,
            'password_change_required_before' => $user->password_change_required
        ]);
        
        // Update using a single update call
        $user->update($updates);
        
        // Check if update was successful
        $user->refresh();
        
        // Log after update to verify
        Log::info('After updating user password change', [
            'login_id' => $user->id,
            'username' => $user->username,
            'password_change_required_after' => $user->password_change_required
        ]);
        
        // Generate a fresh token for the user if using token auth
        $token = null;
        if ($request->bearerToken()) {
            // Revoke the current token
            if ($tokenModel) {
                $tokenModel->delete();
            } else {
                $user->tokens()->where('token', hash('sha256', $request->bearerToken()))->delete();
            }
            
            // Generate new token
            $token = $user->createToken('auth_token')->plainTextToken;
        }
        
        // Different responses based on request type
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role,
                    'password_change_required' => $user->password_change_required
                ]
            ]);
        }
        
        // For web requests, redirect to the appropriate dashboard
        $redirectPath = $this->getRedirectPath($user->role);
        return redirect($redirectPath)->with('success', 'Password changed successfully');
      
    } catch (ValidationException $e) {
        Log::error('Password change validation error:', [
            'errors' => $e->errors(),
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }
        
        return back()->withErrors($e->errors());
        
    } catch (\Exception $e) {
        Log::error('Password change exception:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['current_password', 'new_password', 'new_password_confirmation']),
            'bearer_token_present' => $request->bearerToken() ? 'yes' : 'no'
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing password',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
        
        return back()->withErrors([
            'error' => 'An error occurred while changing password. Please try again.'
        ]);
    }
}

        public function showChangePasswordForm(Request $request)
{
    // Validate token if passed as parameter
    $username = $request->username;
    $token = $request->token;
    $role = $request->role;
    
    // Return the view with the necessary data
    return view('auth.change-password', compact('username', 'token', 'role'));
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