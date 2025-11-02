<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiDosenPembimbingResource;
use App\Models\DosenPembimbingRekomendasi;
use App\Models\MahasiswaRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotifikasiDosenPembimbingController extends Controller
{
    /**
     * GET /api/dosen_pembimbing/notifikasi
     */
    public function index(Request $request)
    {
        $data = $this->getAllRekomendasiArray($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/dosen_pembimbing/notifikasi/getAll
     * kept for compatibility if needed
     */
    public function getAll(Request $request)
    {
        return $this->index($request);
    }

    /**
     * GET /api/dosen_pembimbing/notifikasi/{id}
     */
    public function show(Request $request, $id)
    {
        $dosenId = $request->user()->id;

        $dosenRekom = DosenPembimbingRekomendasi::with('dosen')
            ->where('id', $id)
            ->where('dosen_id', $dosenId)
            ->first();

        if (! $dosenRekom) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $rekomendasi = \App\Models\RekomendasiLomba::with('lomba.bidang')
            ->where('id', $dosenRekom->rekomendasi_lomba_id)
            ->first();

        $mahasiswa = MahasiswaRekomendasi::with('mahasiswa')
            ->where('rekomendasi_lomba_id', $rekomendasi->id ?? null)
            ->get();

        $dosen = DosenPembimbingRekomendasi::with('dosen')
            ->where('rekomendasi_lomba_id', $rekomendasi->id ?? null)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'dosen_rekom' => $dosenRekom,
                'rekomendasi' => $rekomendasi,
                'mahasiswa' => $mahasiswa,
                'dosen' => $dosen,
            ],
        ]);
    }

    /**
     * POST /api/dosen_pembimbing/notifikasi/markAsRead
     * body: { id }
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $dosenId = $request->user()->id;

        $notif = DosenPembimbingRekomendasi::where('id', $id)
            ->where('dosen_id', $dosenId)
            ->first();

        if (! $notif) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if (! $notif->is_accepted) {
            $notif->is_accepted = true;
            $notif->save();
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi telah ditandai sebagai dibaca.']);
    }

    /**
     * POST /api/dosen_pembimbing/notifikasi/markAllAsRead
     */
    public function markAllAsRead(Request $request)
    {
        $dosenId = $request->user()->id;

        $updated = DosenPembimbingRekomendasi::where('dosen_id', $dosenId)
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        return response()->json([
            'success' => true,
            'updated_count' => $updated,
            'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
        ]);
    }

    /**
     * DELETE /api/dosen_pembimbing/notifikasi/{id}
     */
    public function destroy(Request $request, $id)
    {
        $dosenId = $request->user()->id;

        $notif = DosenPembimbingRekomendasi::where('id', $id)
            ->where('dosen_id', $dosenId)
            ->first();

        if (! $notif) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        try {
            $notif->delete();
            return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/dosen_pembimbing/notifikasi/destroyIsAcceptedMessage
     * Hapus semua notifikasi yang sudah dibaca
     */
    public function destroyIsAcceptedMessage(Request $request)
    {
        $dosenId = $request->user()->id;

        $deleted = DosenPembimbingRekomendasi::where('dosen_id', $dosenId)
            ->where('is_accepted', true)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
            'message' => 'Semua notifikasi yang sudah dibaca berhasil dihapus.',
        ]);
    }

    /**
     * Internal helper: assemble notifications array (used by index/getAll)
     */
    protected function getAllRekomendasiArray(Request $request)
    {
        $dosenId = $request->user()->id;

        $rekomendasi = DosenPembimbingRekomendasi::with(['rekomendasiLomba.lomba.bidang'])
            ->where('dosen_id', $dosenId)
            ->latest()
            ->get()
            ->map(function ($item) {
                $rekom = $item->rekomendasiLomba;
                $lomba = $rekom ? $rekom->lomba : null;

                $isAccepted = (bool) $item->is_accepted;
                $pesan = $isAccepted ? 'Rekomendasi Lomba' : 'Rekomendasi Lomba Terbaru';

                return [
                    'id' => $item->id,
                    'rekomendasi_id' => $rekom->id ?? null,
                    'lomba_id' => $lomba->id ?? null,
                    'judul' => $lomba->judul ?? '-',
                    'tempat' => $lomba->tempat ?? '-',
                    'periode_pendaftaran' => $lomba
                        ? (Carbon::parse($lomba->tanggal_daftar)->format('d M Y') . ' s.d. ' . Carbon::parse($lomba->tanggal_daftar_terakhir)->format('d M Y'))
                        : '-',
                    'tingkat' => $lomba->tingkat ?? '-',
                    'bidang' => $lomba && $lomba->bidang
                        ? $lomba->bidang->map(function ($b) {
                            return [
                                'id' => $b->id,
                                'kode' => $b->kode,
                                'nama' => $b->nama,
                            ];
                        })->values()
                        : [],
                    'pesan' => $pesan,
                    'note' => $item->note,
                    'is_accepted' => $isAccepted,
                    'created_at' => $item->created_at ? Carbon::parse($item->created_at)->toISOString() : null,
                    'created_at_human' => $item->created_at ? Carbon::parse($item->created_at)->diffForHumans() : null,
                    'status' => $lomba && $lomba->is_active ? 'Aktif' : 'Tidak Aktif',
                ];
            });

        return $rekomendasi->toArray();
    }
}