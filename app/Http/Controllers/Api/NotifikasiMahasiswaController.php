<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiMahasiswaResource;
use App\Models\MahasiswaRekomendasi;
use App\Models\MahasiswaPrestasiNote;
use App\Models\PengajuanLombaNote;
use App\Models\RekomendasiLomba;
use App\Models\DosenPembimbingRekomendasi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotifikasiMahasiswaController extends Controller
{
    /**
     * GET /api/mahasiswa/notifikasi
     */
    public function index(Request $request)
    {
        $data = $this->getAllNotifikasiArray($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/mahasiswa/notifikasi/getAll
     * compatibility
     */
    public function getAll(Request $request)
    {
        return $this->index($request);
    }

    /**
     * GET /api/mahasiswa/notifikasi/{type}/{id}
     */
    public function show(Request $request, $type, $id)
    {
        $mahasiswaId = $request->user()->id;

        if ($type === 'rekomendasi') {
            $mahasiswaRekom = MahasiswaRekomendasi::with('mahasiswa')
                ->where('id', $id)
                ->where('mahasiswa_id', $mahasiswaId)
                ->first();

            if (! $mahasiswaRekom) {
                return response()->json(['success' => false, 'message' => 'Notifikasi rekomendasi tidak ditemukan.'], 404);
            }

            $rekomendasi = RekomendasiLomba::with('lomba.bidang')
                ->where('id', $mahasiswaRekom->rekomendasi_lomba_id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'mahasiswa_rekom' => $mahasiswaRekom,
                    'rekomendasi' => $rekomendasi,
                ],
            ]);
        }

        if ($type === 'verifikasi') {
            $note = DB::table('mahasiswa_prestasi_notes')
                ->join('prestasi_notes', 'mahasiswa_prestasi_notes.prestasi_notes_id', '=', 'prestasi_notes.id')
                ->join('prestasi', 'prestasi_notes.prestasi_id', '=', 'prestasi.id')
                ->where('mahasiswa_prestasi_notes.mahasiswa_id', $mahasiswaId)
                ->where('mahasiswa_prestasi_notes.id', $id)
                ->select(
                    'mahasiswa_prestasi_notes.id',
                    'prestasi.judul',
                    'prestasi.id as prestasi_id',
                    'prestasi_notes.status',
                    'prestasi_notes.note',
                    'prestasi_notes.created_at',
                    'mahasiswa_prestasi_notes.is_accepted'
                )
                ->first();

            if (! $note) {
                return response()->json(['success' => false, 'message' => 'Notifikasi verifikasi tidak ditemukan.'], 404);
            }

            return response()->json(['success' => true, 'data' => $note]);
        }

        if ($type === 'pengajuan_lomba') {
            $note = PengajuanLombaNote::with(['pengajuanLombaMahasiswa.lomba'])
                ->where('id', $id)
                ->whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
                    $q->where('mahasiswa_id', $mahasiswaId);
                })
                ->first();

            if (! $note) {
                return response()->json(['success' => false, 'message' => 'Notifikasi pengajuan lomba tidak ditemukan.'], 404);
            }

            $lomba = optional(optional($note->pengajuanLombaMahasiswa)->lomba);
            $pengajuan = $note->pengajuanLombaMahasiswa;

            return response()->json([
                'success' => true,
                'data' => [
                    'note' => $note,
                    'lomba' => $lomba,
                    'pengajuan' => $pengajuan,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tipe notifikasi tidak valid.'], 400);
    }

    /**
     * POST /api/mahasiswa/notifikasi/markAsRead
     * body: { id, type }
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $mahasiswaId = $request->user()->id;

        if ($type === 'rekomendasi') {
            $notif = MahasiswaRekomendasi::where('id', $id)->where('mahasiswa_id', $mahasiswaId)->first();
        } elseif ($type === 'verifikasi') {
            $notif = MahasiswaPrestasiNote::where('id', $id)->where('mahasiswa_id', $mahasiswaId)->first();
        } elseif ($type === 'pengajuan_lomba') {
            $notif = PengajuanLombaNote::where('id', $id)
                ->whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
                    $q->where('mahasiswa_id', $mahasiswaId);
                })
                ->first();
        } else {
            return response()->json(['success' => false, 'message' => 'Tipe notifikasi tidak valid.'], 400);
        }

        if (! $notif) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if (! $notif->is_accepted) {
            $notif->is_accepted = true;
            $notif->save();
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi ditandai sebagai dibaca.']);
    }

    /**
     * POST /api/mahasiswa/notifikasi/markAllAsRead
     */
    public function markAllAsRead(Request $request)
    {
        $mahasiswaId = $request->user()->id;

        $countRekom = MahasiswaRekomendasi::where('mahasiswa_id', $mahasiswaId)
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        $countPrestasi = MahasiswaPrestasiNote::where('mahasiswa_id', $mahasiswaId)
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        $countPengajuan = PengajuanLombaNote::whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
            $q->where('mahasiswa_id', $mahasiswaId);
        })
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai dibaca.',
            'rekomendasi_updated' => $countRekom,
            'verifikasi_updated' => $countPrestasi,
            'pengajuan_lomba_updated' => $countPengajuan,
        ]);
    }

    /**
     * DELETE /api/mahasiswa/notifikasi/{type}/{id}
     */
    public function destroy(Request $request, $type, $id)
    {
        $mahasiswaId = $request->user()->id;

        if ($type === 'rekomendasi') {
            $deleted = MahasiswaRekomendasi::where('id', $id)->where('mahasiswa_id', $mahasiswaId)->delete();
        } elseif ($type === 'verifikasi') {
            $deleted = MahasiswaPrestasiNote::where('id', $id)->where('mahasiswa_id', $mahasiswaId)->delete();
        } elseif ($type === 'pengajuan_lomba') {
            $deleted = PengajuanLombaNote::where('id', $id)
                ->whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
                    $q->where('mahasiswa_id', $mahasiswaId);
                })
                ->delete();
        } else {
            return response()->json(['success' => false, 'message' => 'Tipe tidak valid.'], 400);
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted ? 'Notifikasi berhasil dihapus.' : 'Notifikasi tidak ditemukan.'
        ]);
    }

    /**
     * POST /api/mahasiswa/notifikasi/destroyAccepted
     */
    public function destroyIsAcceptedMessage(Request $request)
    {
        $mahasiswaId = $request->user()->id;

        $delRekom = MahasiswaRekomendasi::where('mahasiswa_id', $mahasiswaId)
            ->where('is_accepted', true)
            ->delete();

        $delPrestasi = MahasiswaPrestasiNote::where('mahasiswa_id', $mahasiswaId)
            ->where('is_accepted', true)
            ->delete();

        $delPengajuan = PengajuanLombaNote::where('is_accepted', true)
            ->whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_rekomendasi' => $delRekom,
            'deleted_verifikasi' => $delPrestasi,
            'deleted_pengajuan_lomba' => $delPengajuan,
            'message' => 'Semua notifikasi yang sudah dibaca telah dihapus.'
        ]);
    }

    /**
     * helper to assemble notifications array
     */
    protected function getAllNotifikasiArray(Request $request)
    {
        $mahasiswaId = $request->user()->id;

        // rekomendasi
        $rekomendasi = MahasiswaRekomendasi::with('rekomendasiLomba.lomba.bidang')
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->map(function ($item) {
                $lomba = optional($item->rekomendasiLomba)->lomba;
                return [
                    'id' => $item->id,
                    'type' => 'rekomendasi',
                    'judul' => $lomba->judul ?? '-',
                    'deskripsi' => 'Rekomendasi lomba: ' . ($lomba->judul ?? '-'),
                    'created_at' => $item->created_at,
                    'is_accepted' => (bool) $item->is_accepted,
                ];
            });

        // verifikasi (raw query)
        $prestasi = DB::table('mahasiswa_prestasi_notes')
            ->join('prestasi_notes', 'mahasiswa_prestasi_notes.prestasi_notes_id', '=', 'prestasi_notes.id')
            ->join('prestasi', 'prestasi_notes.prestasi_id', '=', 'prestasi.id')
            ->where('mahasiswa_prestasi_notes.mahasiswa_id', $mahasiswaId)
            ->select(
                'mahasiswa_prestasi_notes.id',
                'mahasiswa_prestasi_notes.is_accepted',
                'prestasi.id as prestasi_id',
                'prestasi.judul as judul',
                'prestasi_notes.status',
                'prestasi_notes.note',
                'prestasi_notes.created_at as created_at'
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'verifikasi',
                    'judul' => $item->judul,
                    'prestasi_id' => $item->prestasi_id,
                    'status' => $item->status,
                    'deskripsi' => 'Prestasi ' . $item->status . ($item->note ? ': ' . $item->note : ''),
                    'created_at' => $item->created_at,
                    'is_accepted' => (bool) $item->is_accepted,
                ];
            });

        // pengajuan lomba
        $pengajuanLomba = PengajuanLombaNote::with(['pengajuanLombaMahasiswa.lomba'])
            ->whereHas('pengajuanLombaMahasiswa', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->get()
            ->map(function ($item) {
                $lomba = optional(optional($item->pengajuanLombaMahasiswa)->lomba);
                $pengajuan = $item->pengajuanLombaMahasiswa;
                return [
                    'id' => $item->id,
                    'type' => 'pengajuan_lomba',
                    'judul' => $lomba->judul ?? '-',
                    'status' => $pengajuan->status,
                    'note' => $pengajuan->note,
                    'deskripsi' => 'Pengajuan lomba: ' . ($lomba->judul ?? '-'),
                    'created_at' => $item->created_at,
                    'is_accepted' => (bool) $item->is_accepted,
                ];
            });

        $notifikasi = $rekomendasi->concat($prestasi)->concat($pengajuanLomba)
            ->sortByDesc(function ($item) {
                return strtotime($item['created_at']);
            })
            ->values()
            ->map(function ($item) {
                $item['created_at'] = Carbon::parse($item['created_at'])->diffForHumans();
                return $item;
            });

        return $notifikasi->toArray();
    }
}