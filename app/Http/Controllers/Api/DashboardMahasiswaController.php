<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrestasiResource;
use App\Models\Prestasi;
use Illuminate\Http\Request;

class DashboardMahasiswaController extends Controller
{
    /**
     * GET /api/mahasiswa/dashboard
     * Protected by auth:sanctum and middleware 'mahasiswa'
     */
    public function index(Request $request)
    {
        // $request->user() should be the authenticated Mahasiswa model (token-authenticated).
        $mahasiswa = $request->user();

        if (! $mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $mahasiswaId = $mahasiswa->id;

        $prestasiTerakhir = Prestasi::whereHas('mahasiswas', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })
            ->with(['dosens'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->take(5)
            ->get();

        $totalPrestasiDiajukan = Prestasi::whereHas('mahasiswas', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })->count();

        $totalPrestasiDisetujui = Prestasi::whereHas('mahasiswas', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })->where('status', 'disetujui')->count();

        $totalPrestasiDitolak = Prestasi::whereHas('mahasiswas', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })->where('status', 'ditolak')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'mahasiswa' => [
                    'id' => $mahasiswa->id,
                    'nama' => $mahasiswa->nama ?? $mahasiswa->name ?? null,
                    'nim' => $mahasiswa->nim ?? null,
                    'username' => $mahasiswa->username ?? null,
                    'email' => $mahasiswa->email ?? null,
                ],
                'prestasiTerakhir' => PrestasiResource::collection($prestasiTerakhir),
                'totalPrestasiDiajukan' => $totalPrestasiDiajukan,
                'totalPrestasiDisetujui' => $totalPrestasiDisetujui,
                'totalPrestasiDitolak' => $totalPrestasiDitolak,
            ],
        ]);
    }
}