<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLombaRequest;
use App\Http\Resources\LombaResource;
use App\Models\Bidang;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class LombaDosenController extends Controller
{
    /**
     * GET /api/dosen_pembimbing/lomba
     * Returns list of lomba (same format as web getAll)
     */
    public function getAll(Request $request)
    {
        $lombas = Lomba::with('bidang')->get();

        $data = $lombas->map(function ($lomba) {
            $warnaTingkat = match ($lomba->tingkat) {
                'internasional' => 'bg-red-100 text-red-800',
                'nasional'      => 'bg-blue-100 text-blue-800',
                'regional'      => 'bg-green-100 text-green-800',
                'provinsi'      => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-800',
            };

            return [
                'id' => $lomba->id,
                'judul' => $lomba->judul,
                'tempat' => $lomba->tempat,
                'tanggal_daftar' => $lomba->tanggal_daftar,
                'tanggal_daftar_terakhir' => $lomba->tanggal_daftar_terakhir,
                'periode_pendaftaran' => Carbon::parse($lomba->tanggal_daftar)->format('d M Y') .
                    ' s.d. ' .
                    Carbon::parse($lomba->tanggal_daftar_terakhir)->format('d M Y'),
                'link' => $lomba->url,
                'tingkat' => $lomba->tingkat,
                'tingkat_warna' => $warnaTingkat,
                'is_individu' => $lomba->is_individu ? 'Ya' : 'Tidak',
                'is_active' => $lomba->is_active ? 'Ya' : 'Tidak',
                'file_poster' => $lomba->file_poster,
                'is_akademik' => $lomba->is_akademik ? 'Ya' : 'Tidak',
                'bidang' => $lomba->bidang->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'kode' => $b->kode,
                        'nama' => $b->nama
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/dosen_pembimbing/lomba
     * body: multipart/form-data (supports file_poster)
     */
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
            'is_active' => $validated['is_active'] ?? 0,
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

    /**
     * GET /api/dosen_pembimbing/lomba/{id}
     */
    public function show($id)
    {
        $lomba = Lomba::with('bidang')->find($id);

        if (! $lomba) {
            return response()->json([
                'success' => false,
                'message' => 'Lomba tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new LombaResource($lomba),
        ]);
    }
}