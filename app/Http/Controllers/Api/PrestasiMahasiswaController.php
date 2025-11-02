<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrestasiMahasiswaRequest;
use App\Http\Requests\UpdatePrestasiMahasiswaRequest;
use App\Http\Resources\PrestasiMahasiswaResource;
use App\Models\Dosen;
use App\Models\Prestasi;
use App\Models\PrestasiMahasiswa;
use App\Models\PengajuanPrestasiAdminNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrestasiMahasiswaController extends Controller
{
    // GET /api/mahasiswa/prestasi (returns view-equivalent list url; here return success for client)
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Use GET /api/mahasiswa/prestasi/data to fetch prestasi list'
        ]);
    }

    // GET /api/mahasiswa/prestasi/data
    public function getData(Request $request)
    {
        $mahasiswa = $request->user();
        if (! $mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $prestasis = Prestasi::whereHas('mahasiswas', function ($query) use ($mahasiswa) {
            $query->where('mahasiswa_id', $mahasiswa->id);
        })->with(['dosens'])->get();

        return response()->json([
            'success' => true,
            'data' => PrestasiMahasiswaResource::collection($prestasis),
        ]);
    }

    // POST /api/mahasiswa/prestasi
    public function store(StorePrestasiMahasiswaRequest $request)
    {
        $validated = $request->validated();
        $mahasiswa = $request->user();

        // store uploaded files
        $suratTugasPath = $request->file('file_surat_tugas')->store('asset_prestasi', 'public');
        $sertifikatPath = $request->file('file_sertifikat')->store('asset_prestasi', 'public');
        $posterPath = $request->hasFile('file_poster') ? $request->file('file_poster')->store('asset_prestasi', 'public') : null;
        $fotoKegiatanPath = $request->hasFile('foto_kegiatan') ? $request->file('foto_kegiatan')->store('asset_prestasi', 'public') : null;

        $prestasi = Prestasi::create([
            'tanggal_pengajuan' => now()->format('Y-m-d'),
            'judul' => $validated['judul'],
            'tempat' => $validated['tempat'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'tingkat' => $validated['tingkat'],
            'juara' => $validated['juara'],
            'is_individu' => $validated['is_individu'],
            'is_akademik' => $validated['is_akademik'],
            'url' => $validated['url'] ?? null,
            'nomor_surat_tugas' => $validated['nomor_surat_tugas'],
            'tanggal_surat_tugas' => $validated['tanggal_surat_tugas'],
            'file_surat_tugas' => $suratTugasPath,
            'file_sertifikat' => $sertifikatPath,
            'file_poster' => $posterPath,
            'foto_kegiatan' => $fotoKegiatanPath,
            'status' => 'pending',
        ]);

        // notify admins
        $adminList = Dosen::where('role', 'admin')->get();
        $data = [];
        foreach ($adminList as $admin) {
            $data[] = [
                'prestasi_id' => $prestasi->id,
                'dosen_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($data)) {
            PengajuanPrestasiAdminNote::insert($data);
        }

        // relations
        $prestasi->bidangs()->sync([$validated['bidang']]);
        $prestasi->dosens()->sync($validated['dosen_pembimbing']);

        if (!empty($validated['mahasiswas'])) {
            $prestasi->mahasiswas()->sync($validated['mahasiswas']);
        } else {
            $prestasi->mahasiswas()->sync([$mahasiswa->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prestasi berhasil diajukan.',
            'data' => new PrestasiMahasiswaResource($prestasi->load('dosens','mahasiswas','bidangs')),
        ], 201);
    }

    // DELETE /api/mahasiswa/prestasi/{id}
    public function destroy(Request $request, $id)
    {
        $prestasi = Prestasi::findOrFail($id);
        $mahasiswa = $request->user();

        if (! $prestasi->mahasiswas->contains($mahasiswa->id)) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan menghapus data ini.'], 403);
        }

        // detach relations and delete files
        $prestasi->dosens()->detach();
        $prestasi->mahasiswas()->detach();

        Storage::disk('public')->delete([$prestasi->file_surat_tugas, $prestasi->file_sertifikat, $prestasi->file_poster, $prestasi->foto_kegiatan]);

        $prestasi->delete();

        return response()->json(['success' => true, 'message' => 'Prestasi berhasil dihapus.']);
    }

    // GET /api/mahasiswa/prestasi/{id}
    public function show(Request $request, $id)
    {
        $prestasi = Prestasi::with(['dosens','mahasiswas','bidangs','notes'])->findOrFail($id);
        $mahasiswa = $request->user();

        if (! $prestasi->mahasiswas->contains($mahasiswa->id)) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan mengakses data ini.'], 403);
        }

        return response()->json(['success' => true, 'data' => new PrestasiMahasiswaResource($prestasi)]);
    }

    // PUT /api/mahasiswa/prestasi/{id}
    public function update(UpdatePrestasiMahasiswaRequest $request, $id)
    {
        $prestasi = Prestasi::with(['mahasiswas','dosens'])->findOrFail($id);
        $mahasiswa = $request->user();

        if (! $prestasi->mahasiswas->contains($mahasiswa->id)) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan mengubah data ini.'], 403);
        }

        $validated = $request->validated();

        // update fields
        $prestasi->judul = $validated['judul'];
        $prestasi->tempat = $validated['tempat'];
        $prestasi->tanggal_mulai = $validated['tanggal_mulai'];
        $prestasi->tanggal_selesai = $validated['tanggal_selesai'];
        $prestasi->tingkat = $validated['tingkat'];
        $prestasi->juara = $validated['juara'];
        $prestasi->is_individu = $validated['is_individu'];
        $prestasi->is_akademik = $validated['is_akademik'];
        $prestasi->url = $validated['url'] ?? null;
        $prestasi->nomor_surat_tugas = $validated['nomor_surat_tugas'];
        $prestasi->tanggal_surat_tugas = $validated['tanggal_surat_tugas'];

        if ($request->hasFile('file_surat_tugas')) {
            if ($prestasi->file_surat_tugas) {
                Storage::disk('public')->delete($prestasi->file_surat_tugas);
            }
            $prestasi->file_surat_tugas = $request->file('file_surat_tugas')->store('asset_prestasi', 'public');
        }
        if ($request->hasFile('file_sertifikat')) {
            if ($prestasi->file_sertifikat) {
                Storage::disk('public')->delete($prestasi->file_sertifikat);
            }
            $prestasi->file_sertifikat = $request->file('file_sertifikat')->store('asset_prestasi', 'public');
        }
        if ($request->hasFile('file_poster')) {
            if ($prestasi->file_poster) {
                Storage::disk('public')->delete($prestasi->file_poster);
            }
            $prestasi->file_poster = $request->file('file_poster')->store('asset_prestasi', 'public');
        }
        if ($request->hasFile('foto_kegiatan')) {
            if ($prestasi->foto_kegiatan) {
                Storage::disk('public')->delete($prestasi->foto_kegiatan);
            }
            $prestasi->foto_kegiatan = $request->file('foto_kegiatan')->store('asset_prestasi', 'public');
        }

        // set status back to pending and save
        $prestasi->status = 'pending';
        $prestasi->save();

        // sync relations
        $prestasi->bidangs()->sync([$validated['bidang']]);
        $prestasi->dosens()->sync($validated['dosen_pembimbing']);

        if (!empty($validated['mahasiswas'])) {
            $prestasi->mahasiswas()->sync($validated['mahasiswas']);
        } else {
            $prestasi->mahasiswas()->sync([$mahasiswa->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prestasi berhasil diperbarui dan menunggu verifikasi ulang.',
            'data' => new PrestasiMahasiswaResource($prestasi->fresh('dosens','mahasiswas','bidangs')),
        ]);
    }
}