<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRekomendasiLombaRequest;
use App\Http\Resources\RekomendasiLombaAdminResource;
use App\Models\Dosen;
use App\Models\DosenPembimbingRekomendasi;
use App\Models\Mahasiswa;
use App\Models\MahasiswaRekomendasi;
use App\Models\RekomendasiLomba;
use App\Models\Lomba;
use App\Services\Aras;
use App\Services\Electre;
use App\Services\Entrophy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RekomendasiLombaController extends Controller
{
    protected $aras;
    protected $electre;
    protected $entrophy;

    public function __construct(Aras $aras, Electre $electre, Entrophy $entrophy)
    {
        $this->aras = $aras;
        $this->electre = $electre;
        $this->entrophy = $entrophy;
    }

    /**
     * GET /api/admin/rekomendasi
     * return data needed for admin rekomendasi page (lomba aktif, rankings, mahasiswa, dosen)
     */
    public function index(Request $request)
    {
        $lombaAktif = Lomba::with('bidang')
            ->where('is_active', true)
            ->get();

        $rankAras = $this->aras->getRanking();
        $rankElectre = $this->electre->getRanking();

        $mahasiswa = Mahasiswa::select('id', 'nama', 'nim')->get();
        $dosen = Dosen::where('role', 'dosen_pembimbing')->select('id', 'nama', 'nidn')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'lombaAktif' => $lombaAktif,
                'rankAras' => $rankAras,
                'rankElectre' => $rankElectre,
                'mahasiswa' => $mahasiswa,
                'dosen' => $dosen,
            ],
        ]);
    }

    /**
     * POST /api/admin/rekomendasi
     * create rekomendasi + create mahasiswa_rekomendasi and dosen_pembimbing_rekomendasi rows
     */
    public function store(StoreRekomendasiLombaRequest $request)
    {
        $validated = $request->validated();

        $rekomendasi = RekomendasiLomba::create([
            'lomba_id' => $validated['lomba_id'],
        ]);

        // create mahasiswa rekomendasi rows
        foreach ($validated['mahasiswa_id'] as $mhs_id) {
            MahasiswaRekomendasi::create([
                'rekomendasi_lomba_id' => $rekomendasi->id,
                'mahasiswa_id' => $mhs_id,
                'note' => 'Anda mendapatkan rekomendasi untuk mengikuti Lomba : ' . optional($rekomendasi->lomba)->judul,
            ]);
        }

        // create dosen pembimbing rekomendasi rows
        foreach ($validated['dosen_id'] as $dsn_id) {
            DosenPembimbingRekomendasi::create([
                'rekomendasi_lomba_id' => $rekomendasi->id,
                'dosen_id' => $dsn_id,
                'note' => 'Anda mendapatkan rekomendasi untuk menjadi Dosen Pembimbing dalam Lomba ' . optional($rekomendasi->lomba)->judul,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi berhasil disimpan.',
            'data' => new RekomendasiLombaAdminResource($rekomendasi->load('lomba.bidang')),
        ], 201);
    }

    /**
     * GET /api/admin/rekomendasi/all
     * return list of rekomendasi (only those with active lomba)
     */
    public function getAll()
    {
        $rekomendasi = RekomendasiLomba::with('lomba.bidang')
            ->whereHas('lomba', function ($q) {
                $q->where('is_active', true);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => RekomendasiLombaAdminResource::collection($rekomendasi),
        ]);
    }

    /**
     * GET /api/admin/rekomendasi/{id}
     * show rekomendasi detail including mahasiswa and dosen rekomendasi
     */
    public function show($id)
    {
        $rekomendasi = RekomendasiLomba::with('lomba.bidang')
            ->where('id', $id)
            ->first();

        if (! $rekomendasi) {
            return response()->json(['success' => false, 'message' => 'Rekomendasi tidak ditemukan.'], 404);
        }

        $mahasiswa = MahasiswaRekomendasi::with('mahasiswa')
            ->where('rekomendasi_lomba_id', $rekomendasi->id)
            ->get();

        $dosen = DosenPembimbingRekomendasi::with('dosen')
            ->where('rekomendasi_lomba_id', $rekomendasi->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rekomendasi' => new RekomendasiLombaAdminResource($rekomendasi),
                'mahasiswa' => $mahasiswa,
                'dosen' => $dosen,
            ],
        ]);
    }

    /**
     * DELETE /api/admin/rekomendasi/{id}
     */
    public function destroy($id)
    {
        try {
            $rekomendasi = RekomendasiLomba::findOrFail($id);

            MahasiswaRekomendasi::where('rekomendasi_lomba_id', $rekomendasi->id)->delete();
            DosenPembimbingRekomendasi::where('rekomendasi_lomba_id', $rekomendasi->id)->delete();
            $rekomendasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rekomendasi lomba berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus rekomendasi lomba: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/admin/rekomendasi/lomba/{id}
     * show lomba detail (reuse view helper in web controller)
     */
    public function showLomba($id)
    {
        $lomba = Lomba::with('bidang')->find($id);
        if (! $lomba) {
            return response()->json(['success' => false, 'message' => 'Lomba tidak ditemukan.'], 404);
        }

        // mimic getLomba helper formatting from web controller
        $warnaTingkat = match ($lomba->tingkat) {
            'internasional' => 'bg-red-100 text-red-800',
            'nasional'      => 'bg-blue-100 text-blue-800',
            'regional'      => 'bg-green-100 text-green-800',
            'provinsi'      => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };

        $data = [
            'id' => $lomba->id,
            'judul' => $lomba->judul,
            'tempat' => $lomba->tempat,
            'tanggal_daftar' => $lomba->tanggal_daftar,
            'tanggal_daftar_terakhir' => $lomba->tanggal_daftar_terakhir,
            'periode_pendaftaran' => Carbon::parse($lomba->tanggal_daftar)->format('d M Y') . ' s.d. ' . Carbon::parse($lomba->tanggal_daftar_terakhir)->format('d M Y'),
            'link' => $lomba->url,
            'tingkat' => $lomba->tingkat,
            'tingkat_warna' => $warnaTingkat,
            'is_individu' => (bool) $lomba->is_individu,
            'is_active' => (bool) $lomba->is_active,
            'file_poster' => $lomba->file_poster ? url('storage/'.$lomba->file_poster) : null,
            'is_akademik' => (bool) $lomba->is_akademik,
            'bidang' => $lomba->bidang->map(function ($b) {
                return ['id' => $b->id, 'kode' => $b->kode, 'nama' => $b->nama];
            })->values(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }
}