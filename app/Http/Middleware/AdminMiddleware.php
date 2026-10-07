<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $isApi = $request->expectsJson() || $request->is('api/*');
        $user = $request->user();

        if (!$user) {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Bearer token required.',
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to access the admin portal.');
        }

        if ($user->role !== 'admin') {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Administrator privileges required.',
                ], 403);
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}
