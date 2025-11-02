<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLombaMahasiswaRequest;
use App\Http\Resources\LombaResource;
use App\Http\Resources\PengajuanLombaResource;
use App\Models\Bidang;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\PengajuanLombaAdminNote;
use App\Models\PengajuanLombaMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class LombaMahasiswaController extends Controller
{
    // GET /api/mahasiswa/lomba
    public function getAll(Request $request)
    {
        $lombas = Lomba::with('bidang')
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => LombaResource::collection($lombas),
        ]);
    }

    // GET /api/mahasiswa/lomba/{id}
    public function show(Request $request, $id)
    {
        $lomba = Lomba::with('bidang')->find($id);
        if (! $lomba) {
            return response()->json(['success' => false, 'message' => 'Lomba tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => new LombaResource($lomba)]);
    }

    // POST /api/mahasiswa/lomba (submit new lomba + pengajuan)
    public function store(StoreLombaMahasiswaRequest $request)
    {
        $validated = $request->validated();

        $filePosterPath = null;
        if ($request->hasFile('file_poster')) {
            $filePosterPath = $request->file('file_poster')->store('posters', 'public');
        }

        $lomba = Lomba::create([
            'judul' => $validated['judul'],
            'tempat' => $validated['tempat'],
            'tanggal_daftar' => $validated['tanggal_daftar'],
            'tanggal_daftar_terakhir' => $validated['tanggal_daftar_terakhir'],
            'url' => $validated['url'] ?? null,
            'tingkat' => $validated['tingkat'],
            'is_individu' => $validated['is_individu'],
            'is_active' => false, // new pengajuan tidak aktif sampai disetujui admin
            'file_poster' => $filePosterPath,
            'is_akademik' => $validated['is_akademik'],
        ]);

        if (! empty($validated['bidang'])) {
            $lomba->bidang()->sync($validated['bidang']);
        }

        $mahasiswa = $request->user();
        $pengajuan = PengajuanLombaMahasiswa::create([
            'lomba_id' => $lomba->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // create notification rows for admins (same logic as web)
        $adminList = Dosen::where('role', 'admin')->get();
        $data = [];
        foreach ($adminList as $admin) {
            $data[] = [
                'pengajuan_lomba_mahasiswa_id' => $pengajuan->id,
                'dosen_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (! empty($data)) {
            PengajuanLombaAdminNote::insert($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lomba berhasil dikirim dan menunggu persetujuan admin.',
            'data' => new PengajuanLombaResource($pengajuan->load('lomba.bidang', 'admin')),
        ], 201);
    }

    // GET /api/mahasiswa/pengajuan
    public function getPengajuan(Request $request)
    {
        $mahasiswa = $request->user();
        $pengajuans = PengajuanLombaMahasiswa::with(['lomba.bidang', 'admin'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PengajuanLombaResource::collection($pengajuans),
        ]);
    }

    // DELETE /api/mahasiswa/pengajuan/{id}
    public function destroyPengajuan(Request $request, $id)
    {
        $mahasiswa = $request->user();
        $pengajuan = PengajuanLombaMahasiswa::findOrFail($id);

        if ($pengajuan->mahasiswa_id !== $mahasiswa->id) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menghapus pengajuan ini'], 403);
        }

        if ($pengajuan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Hanya pengajuan berstatus pending yang dapat dihapus'], 400);
        }

        $pengajuan->delete();

        return response()->json(['success' => true, 'message' => 'Pengajuan berhasil dihapus']);
    }

    // GET /api/mahasiswa/pengajuan/{id}
    public function showPengajuan(Request $request, $id)
    {
        $mahasiswa = $request->user();
        $pengajuan = PengajuanLombaMahasiswa::with(['lomba.bidang', 'admin'])
            ->where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if (! $pengajuan) {
            return response()->json(['success' => false, 'message' => 'Pengajuan lomba tidak ditemukan atau bukan milik Anda'], 404);
        }

        return response()->json(['success' => true, 'data' => new PengajuanLombaResource($pengajuan)]);
    }
}