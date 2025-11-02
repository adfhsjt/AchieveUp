<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLombaRequest;
use App\Http\Requests\UpdateLombaRequest;
use App\Http\Resources\LombaResource;
use App\Http\Resources\PengajuanLombaResource;
use App\Models\Lomba;
use App\Models\PengajuanLombaMahasiswa;
use App\Models\PengajuanLombaNote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LombaController extends Controller
{
    // GET /api/admin/lomba
    public function index(Request $request)
    {
        $lombas = Lomba::with('bidang')->orderBy('id', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => LombaResource::collection($lombas),
        ]);
    }

    // GET /api/admin/lomba/{id}
    public function show($id)
    {
        $lomba = Lomba::with('bidang')->find($id);
        if (! $lomba) {
            return response()->json(['success' => false, 'message' => 'Lomba tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => new LombaResource($lomba)]);
    }

    // POST /api/admin/lomba
    public function store(StoreLombaRequest $request)
    {
        $validated = $request->validated();

        $posterPath = null;
        if ($request->hasFile('file_poster')) {
            $posterPath = $request->file('file_poster')->store('posters', 'public');
        }

        $lomba = Lomba::create([
            'judul' => $validated['judul'],
            'tempat' => $validated['tempat'],
            'tanggal_daftar' => $validated['tanggal_daftar'],
            'tanggal_daftar_terakhir' => $validated['tanggal_daftar_terakhir'],
            'url' => $validated['url'] ?? null,
            'tingkat' => $validated['tingkat'],
            'is_individu' => $validated['is_individu'],
            'is_active' => 1,
            'is_akademik' => $validated['is_akademik'],
            'file_poster' => $posterPath,
        ]);

        if (! empty($validated['bidang'])) {
            $lomba->bidang()->sync($validated['bidang']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data lomba berhasil disimpan.',
            'data' => new LombaResource($lomba->fresh('bidang')),
        ], 201);
    }

    // PUT /api/admin/lomba/{id}
    public function update(UpdateLombaRequest $request, $id)
    {
        $lomba = Lomba::find($id);
        if (! $lomba) {
            return response()->json(['success' => false, 'message' => 'Lomba tidak ditemukan.'], 404);
        }

        $validated = $request->validated();

        if ($request->hasFile('file_poster')) {
            if ($lomba->file_poster) {
                Storage::disk('public')->delete($lomba->file_poster);
            }
            $posterPath = $request->file('file_poster')->store('posters', 'public');
        } else {
            $posterPath = $lomba->file_poster;
        }

        $lomba->update([
            'judul' => $validated['judul'],
            'tempat' => $validated['tempat'],
            'tanggal_daftar' => $validated['tanggal_daftar'],
            'tanggal_daftar_terakhir' => $validated['tanggal_daftar_terakhir'],
            'url' => $validated['url'] ?? null,
            'tingkat' => $validated['tingkat'],
            'is_individu' => $validated['is_individu'],
            'is_active' => $validated['is_active'],
            'is_akademik' => $validated['is_akademik'],
            'file_poster' => $posterPath,
        ]);

        $lomba->bidang()->sync($validated['bidang']);

        return response()->json([
            'success' => true,
            'message' => 'Data lomba berhasil diperbarui.',
            'data' => new LombaResource($lomba->fresh('bidang')),
        ]);
    }

    // DELETE /api/admin/lomba/{id}
    public function destroy($id)
    {
        try {
            $lomba = Lomba::findOrFail($id);
            if ($lomba->file_poster) {
                Storage::disk('public')->delete($lomba->file_poster);
            }
            $lomba->delete();

            return response()->json(['success' => true, 'message' => 'Data lomba berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting lomba: '.$e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data.'], 500);
        }
    }

    // GET /api/admin/lomba/pengajuan
    public function getPengajuan()
    {
        $pengajuans = PengajuanLombaMahasiswa::with(['lomba.bidang', 'admin', 'mahasiswa'])->get();
        return response()->json([
            'success' => true,
            'data' => PengajuanLombaResource::collection($pengajuans),
        ]);
    }

    // GET /api/admin/lomba/pengajuan/{id}
    public function showPengajuan($id)
    {
        $pengajuan = PengajuanLombaMahasiswa::with(['lomba.bidang', 'admin', 'mahasiswa'])->find($id);
        if (! $pengajuan) {
            return response()->json(['success' => false, 'message' => 'Pengajuan lomba tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => new PengajuanLombaResource($pengajuan)]);
    }

    // POST /api/admin/lomba/pengajuan/{id}/approve
    public function approvePengajuan($id, Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menyetujui pengajuan.'], 403);
        }

        $pengajuan = PengajuanLombaMahasiswa::findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses.'], 400);
        }

        $lomba = $pengajuan->lomba;
        $lomba->update(['is_active' => true]);

        $pengajuan->update([
            'status' => 'approved',
            'admin_id' => $user->id,
            'notes' => $request->input('notes'),
        ]);

        PengajuanLombaNote::create([
            'pengajuan_lomba_mahasiswa_id' => $pengajuan->id,
            'note' => $request->input('notes') ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Pengajuan berhasil disetujui.']);
    }

    // POST /api/admin/lomba/pengajuan/{id}/reject
    public function rejectPengajuan($id, Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menolak pengajuan.'], 403);
        }

        $pengajuan = PengajuanLombaMahasiswa::findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses.'], 400);
        }

        $pengajuan->update([
            'status' => 'rejected',
            'admin_id' => $user->id,
            'notes' => $request->input('notes'),
        ]);

        PengajuanLombaNote::create([
            'pengajuan_lomba_mahasiswa_id' => $pengajuan->id,
            'note' => $request->input('notes') ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Pengajuan berhasil ditolak.']);
    }
}