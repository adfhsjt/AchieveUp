<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('dosen:admin')
     *   ->middleware('dosen:dosen pembimbing')
     *   ->middleware('dosen:admin|kajur')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Prefer $request->user() so this works with token-auth (Sanctum) and session-auth.
        $user = $request->user();

        // Fallback to specific guards (some apps use a dedicated 'dosen' or 'admin' guard)
        if (! $user) {
            if (Auth::guard('dosen')->check()) {
                $user = Auth::guard('dosen')->user();
            } elseif (Auth::guard('admin')->check()) {
                $user = Auth::guard('admin')->user();
            }
        }

        // Helper: determine whether this is an API request that expects JSON
        $isApi = $request->expectsJson() || $request->is('api/*') || str_contains((string) $request->header('accept'), 'application/json');

        // If not authenticated
        if (! $user) {
            if ($isApi) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            // web: redirect to login
            return redirect()->guest(route('login'));
        }

        // If roles were provided, check them.
        if (!empty($roles)) {
            // Roles can come as multiple variadic args, but some usages pass a single string with separators.
            // Normalize: split by '|' or ',' and trim spaces.
            $allowed = [];
            foreach ($roles as $r) {
                $parts = preg_split('/[|,]/', $r);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        $allowed[] = $p;
                    }
                }
            }

            // If user's role is not in allowed list -> forbidden
            if (! in_array($user->role, $allowed)) {
                if ($isApi) {
                    return response()->json(['success' => false, 'message' => 'Forbidden. Anda tidak memiliki izin.'], 403);
                }
                abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
            }
        }

        // Session timeout logic: only apply for web/session requests (not API token calls)
        // Keep existing behavior but guarded by !isApi
        if (! $isApi) {
            $timeout = 20000; // seconds (existing value)
            if ($request->session()->has('login_time')) {
                $lastLogin = strtotime($request->session()->get('login_time'));
                if ((time() - $lastLogin) > $timeout) {
                    // Logout from guards if used
                    try {
                        Auth::guard('dosen')->logout();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    try {
                        Auth::guard('admin')->logout();
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