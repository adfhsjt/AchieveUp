<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Resources\BimbinganMahasiswaResource;
use App\Http\Resources\MahasiswaDetailResource;

class BimbinganController extends Controller
{
    /**
     * GET /api/dosen_pembimbing/bimbingan
     * Returns list of distinct students this dosen mentors.
     */
    public function index(Request $request)
    {
        // $request->user() will be the token-authenticated Dosen model
        $dosen = $request->user();
        $dosenId = $dosen->id;

        $mahasiswaBimbingan = DB::table('pembimbing_prestasi')
            ->join('prestasi', 'pembimbing_prestasi.prestasi_id', '=', 'prestasi.id')
            ->join('prestasi_mahasiswa', 'prestasi.id', '=', 'prestasi_mahasiswa.prestasi_id')
            ->join('mahasiswa', 'prestasi_mahasiswa.mahasiswa_id', '=', 'mahasiswa.id')
            ->join('program_studi', 'mahasiswa.program_studi_id', '=', 'program_studi.id')
            ->where('pembimbing_prestasi.dosen_id', $dosenId)
            ->select(
                'mahasiswa.id',
                'mahasiswa.nim',
                'mahasiswa.nama',
                'mahasiswa.username',
                'mahasiswa.email',
                'program_studi.nama as program_studi'
            )
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => BimbinganMahasiswaResource::collection($mahasiswaBimbingan),
        ]);
    }

    /**
     * GET /api/dosen_pembimbing/bimbingan/{id}
     * Returns mahasiswa detail + prestasi list.
     */
    public function detail(Request $request, $id)
    {
        // Optional: you may validate that the dosen actually mentors this mahasiswa

        $mahasiswa = DB::table('mahasiswa')
            ->join('program_studi', 'mahasiswa.program_studi_id', '=', 'program_studi.id')
            ->where('mahasiswa.id', $id)
            ->select(
                'mahasiswa.id',
                'mahasiswa.nim',
                'mahasiswa.nama',
                'mahasiswa.username',
                'mahasiswa.email',
                'mahasiswa.foto',
                'program_studi.nama as program_studi'
            )
            ->first();

        if (! $mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.'
            ], 404);
        }

        $prestasi = DB::table('prestasi_mahasiswa')
            ->join('prestasi', 'prestasi_mahasiswa.prestasi_id', '=', 'prestasi.id')
            ->where('prestasi_mahasiswa.mahasiswa_id', $id)
            ->select(
                'prestasi.id',
                'prestasi.judul',
                'prestasi.tempat',
                'prestasi.tanggal_mulai',
                'prestasi.tingkat',
                'prestasi.juara'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => new MahasiswaDetailResource((object)[
                'mahasiswa' => $mahasiswa,
                'prestasi' => $prestasi,
            ]),
        ]);
    }
}