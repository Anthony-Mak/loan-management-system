<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
     /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return $this->unauthorizedResponse($request);
        }

        $user = Auth::user();
        
        // Admin gets full access
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            return $this->unauthorizedResponse($request);
        }
        return $next($request); 
    }
    private function unauthorizedResponse($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return redirect()->back()->with('error', 'Unauthorized access');
    }
}