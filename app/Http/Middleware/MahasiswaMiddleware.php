<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware works for both token-based (Sanctum) and session-based authentication.
     * For API requests (Accept: application/json or path starting with /api/) it returns JSON
     * responses (401/403) instead of HTML redirects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Prefer $request->user() so it works with Sanctum token auth and session auth
        $user = $request->user();

        // Fallback: check specific 'mahasiswa' guard if your app uses a dedicated guard
        if (! $user && Auth::guard('mahasiswa')->check()) {
            $user = Auth::guard('mahasiswa')->user();
        }

        // Detect API-like requests: expectsJson, Accept header, or path /api/*
        $isApi = $request->expectsJson() || $request->is('api/*') || str_contains((string) $request->header('accept'), 'application/json');

        // If not authenticated
        if (! $user) {
            if ($isApi) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        // If authenticated but not via mahasiswa guard/role, optionally check role property if present.
        // If your Mahasiswa model doesn't have a 'role' attribute, you can remove this block.
        if (isset($user->role) && $user->role !== 'mahasiswa') {
            if ($isApi) {
                return response()->json(['success' => false, 'message' => 'Forbidden. Hanya untuk Mahasiswa.'], 403);
            }
            abort(403, 'Hanya untuk Mahasiswa.');
        }

        // Session timeout logic: only apply for web (not API token calls)
        if (! $isApi) {
            $timeout = 20000; // seconds (existing value)
            if ($request->session()->has('login_time')) {
                $lastLogin = strtotime($request->session()->get('login_time'));
                if ((time() - $lastLogin) > $timeout) {
                    try {
                        Auth::guard('mahasiswa')->logout();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/login')->with('error', 'Sesi Anda telah habis, silakan login kembali.');
                }
            }
        }

        return $next($request);
    }
}