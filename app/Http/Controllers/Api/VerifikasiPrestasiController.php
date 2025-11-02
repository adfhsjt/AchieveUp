<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovePrestasiRequest;
use App\Http\Requests\RejectPrestasiRequest;
use App\Http\Resources\PrestasiAdminResource;
use App\Models\Prestasi;
use App\Models\PrestasiNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiPrestasiController extends Controller
{
    /**
     * GET /api/admin/prestasi
     * Returns meta message (web uses view) - use /data to fetch list
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Gunakan endpoint /api/admin/prestasi/data untuk mengambil daftar prestasi.',
        ]);
    }

    /**
     * GET /api/admin/prestasi/data
     * Return list of prestasi sorted with pending first
     */
    public function getData()
    {
        $prestasis = Prestasi::with(['dosens', 'mahasiswas'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PrestasiAdminResource::collection($prestasis),
        ]);
    }

    /**
     * POST /api/admin/prestasi/{id}/approve
     * Approve a prestasi. Note is optional.
     */
    public function approve(ApprovePrestasiRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== 'admin') {
            Log::warning('Unauthorized approve attempt', ['user_id' => $user?->id]);
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menyetujui prestasi.'], 403);
        }

        DB::beginTransaction();
        try {
            $prestasi = Prestasi::findOrFail($id);
            $prestasi->status = 'disetujui';
            $prestasi->save();

            $noteText = $request->input('note') ?? 'Selamat! Data prestasi Anda telah terverifikasi dan disetujui. Terus tingkatkan prestasi dan raih pencapaian yang lebih gemilang! 🎉';

            $note = PrestasiNote::create([
                'prestasi_id' => $prestasi->id,
                'dosen_id' => $user->id,
                'status' => 'disetujui',
                'note' => $noteText,
            ]);

            $mahasiswaList = DB::table('prestasi_mahasiswa')->where('prestasi_id', $prestasi->id)->get();

            foreach ($mahasiswaList as $mhs) {
                DB::table('mahasiswa_prestasi_notes')->insert([
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'prestasi_notes_id' => $note->id,
                    'is_accepted' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Prestasi approved', ['prestasi_id' => $prestasi->id, 'dosen_id' => $user->id]);

            return response()->json(['success' => true, 'message' => 'Prestasi berhasil disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving prestasi', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyetujui prestasi.'], 500);
        }
    }

    /**
     * POST /api/admin/prestasi/{id}/reject
     * Reject a prestasi. Note is required.
     */
    public function reject(RejectPrestasiRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== 'admin') {
            Log::warning('Unauthorized reject attempt', ['user_id' => $user?->id]);
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menolak prestasi.'], 403);
        }

        DB::beginTransaction();
        try {
            $prestasi = Prestasi::findOrFail($id);
            $prestasi->status = 'ditolak';
            $prestasi->save();

            $note = PrestasiNote::create([
                'prestasi_id' => $prestasi->id,
                'dosen_id' => $user->id,
                'status' => 'ditolak',
                'note' => $request->input('note'),
            ]);

            $mahasiswaList = DB::table('prestasi_mahasiswa')->where('prestasi_id', $prestasi->id)->get();

            foreach ($mahasiswaList as $mhs) {
                DB::table('mahasiswa_prestasi_notes')->insert([
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'prestasi_notes_id' => $note->id,
                    'is_accepted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Prestasi rejected', ['prestasi_id' => $prestasi->id, 'dosen_id' => $user->id]);

            return response()->json(['success' => true, 'message' => 'Prestasi berhasil ditolak.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting prestasi', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menolak prestasi.'], 500);
        }
    }

    /**
     * GET /api/admin/prestasi/{id}
     * Return prestasi detail (for admin)
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan mengakses data ini.'], 403);
        }

        $prestasi = Prestasi::with(['dosens', 'mahasiswas', 'notes'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => new PrestasiAdminResource($prestasi)]);
    }

    /**
     * GET /api/admin/prestasi/export
     * Keep same behaviour as web: generate PDF for approved prestasi
     */
    public function export(Request $request)
    {
        // we reuse web logic — you may keep it server-side or return a link
        $prestasis = Prestasi::with(['dosens', 'mahasiswas.programStudi', 'bidangs.lomba'])
            ->where('status', 'disetujui')
            ->get();

        // Option: return data instead of PDF generation in API context
        return response()->json([
            'success' => true,
            'data' => PrestasiAdminResource::collection($prestasis),
        ]);
    }
}