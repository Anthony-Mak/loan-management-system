<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            User::where('user_id', $user->user_id)->update([
                'last_login' => now()
            ]);
            
            return response()->json([
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role
                ],
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        }
        
        return response()->json([
            'message' => 'Invalid login credentials'
        ], 401);
    }
    
    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
