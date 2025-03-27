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
        
            Log::debug('Preparing to redirect user:', [
                'username' => $user->username,
                'role' => $user->role,
                'redirect_path' => match($user->role) {
                    'employee' => '/employee/dashboard',
                    'admin' => '/admin/dashboard',
                    'hr' => '/hr/dashboard',
                    default => '/'
                }
            ]);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role,
                    'password_change_required' => false
                ],
                'token' => $user->createToken('auth_token')->plainTextToken
            ]);
        }
        
        // Authentication failed
        Log::warning('Authentication failed for username:', [
            'username' => $request->username,
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid username or password'
        ], 401);
    } catch (\Exception $e) {
        // Log the exception
        Log::error('Login exception:', [
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
        try {
            // Log the request details for debugging
            Log::debug('Password change request headers:', $request->headers->all());
            Log::debug('Password change request method:', ['method' => $request->method()]);
            Log::debug('Password change request format:', ['format' => $request->format()]);
            
            // Validate request
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password',
            ]);
            
            // Get the authenticated user
            $user = null;
            
            // Try session auth first
            if (Auth::check()) {
                $user = Auth::user();
                Log::debug('User found via session auth', ['login_id' => $user->id]);
            } 
            // Then try token auth
            elseif ($request->bearerToken()) {
                $token = PersonalAccessToken::findToken($request->bearerToken());
                if ($token) {
                    $user = $token->tokenable;
                    Log::debug('User found via token auth', ['login_id' => $user->id]);
                }
            }
            
            if (!$user) {
                Log::warning('No authenticated user found for password change');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            // Check if current password is correct
            if (!Hash::check($validated['current_password'], $user->password)) {
                Log::warning('Invalid current password for user', ['login_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            
            // Update password
            $user->password = Hash::make($validated['new_password']);
            
            // If this is a first-time login, update the flag
            if ($request->has('first_time_login') && $request->input('first_time_login') === true) {
                $user->password_change_required = false;
            }
            
            $user->save();
            
            Log::info('Password changed successfully for user', ['login_id' => $user->id]);
            
            // Generate a fresh token for the user if using token auth
            $token = null;
            if ($request->bearerToken()) {
                // Revoke the current token
                $currentToken = PersonalAccessToken::findToken($request->bearerToken());
                if ($currentToken) {
                    $currentToken->delete();
                }
                
                // Create a new token
                $token = $user->createToken('auth_token')->plainTextToken;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'token' => $token
            ]);
            
        } catch (ValidationException $e) {
            Log::error('Password change validation error:', [
                'errors' => $e->errors(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Password change exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing password',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }}

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