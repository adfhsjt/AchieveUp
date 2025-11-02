<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardDosenController extends Controller
{
    /**
     * GET /api/dosen_pembimbing/dashboard
     * Returns minimal dashboard payload for dosen.
     */
    public function index(Request $request)
    {
        // Jika perlu, gunakan $request->user() untuk mengambil data dosen
        $user = $request->user();

        $breadcrumb = (object)[
            'title' => 'Dashboard',
            'list' => ['Home', 'Dashboard'],
        ];

        $page = (object)[
            'title' => 'Selamat datang di Dashboard',
        ];

        $activeMenu = 'dashboard';

        return response()->json([
            'success' => true,
            'data' => [
                'breadcrumb' => $breadcrumb,
                'page' => $page,
                'activeMenu' => $activeMenu,
                // optionally include basic user info
                'user' => $user ? [
                    'id' => $user->id,
                    'nama' => $user->nama ?? $user->name ?? null,
                    'username' => $user->username ?? null,
                    'email' => $user->email ?? null,
                    'role' => $user->role ?? null,
                ] : null,
            ],
        ]);
    }
}