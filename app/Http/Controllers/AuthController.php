<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Cek login untuk mahasiswa
        if (
            Auth::guard('mahasiswa')->attempt(['NIM' => $username, 'password' => $password]) ||
            Auth::guard('mahasiswa')->attempt(['username' => $username, 'password' => $password])
        ) {
            $request->session()->regenerate();
            $request->session()->put('login_time', now());
            return redirect()->route('mahasiswa.dashboard.index')
                ->with('user', Auth::guard('mahasiswa')->user());
        }

        // Cek login untuk dosen
        if (
            Auth::guard('dosen')->attempt(['NIDN' => $username, 'password' => $password]) ||
            Auth::guard('dosen')->attempt(['username' => $username, 'password' => $password])
        ) {
            $request->session()->regenerate();
            $request->session()->put('login_time', now());
            $user = Auth::guard('dosen')->user();

            if ($user->role === 'admin') {
                Log::info('Admin logged in', ['user_id' => $user->id]);
                return redirect()->route('admin.dashboard.index')
                    ->with('user', $user);
            } elseif (in_array($user->role, ['dosen', 'dosen pembimbing'])) {
                return redirect()->route('dosen.dashboard.index')
                    ->with('user', $user);
            } else {
                Auth::guard('dosen')->logout();
                return back()->with('error', 'Role dosen tidak valid.');
            }
        }

        return back()->with('error', 'Username atau password tidak cocok.');
    }

    public function logout(Request $request)
    {
        Auth::guard('mahasiswa')->logout();
        Auth::guard('dosen')->logout();
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
