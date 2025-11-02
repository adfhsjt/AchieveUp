<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiAdminResource;
use App\Models\PengajuanLombaAdminNote;
use App\Models\PengajuanPrestasiAdminNote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotifikasiAdminController extends Controller
{
    /**
     * GET /api/admin/notifikasi
     * Return view-equivalent data (list) in JSON.
     */
    public function index(Request $request)
    {
        // reuse getAllNotifikasi implementation but return structured JSON
        $data = $this->getAllNotifikasiArray($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/admin/notifikasi/getAll
     * Return raw notifications array (keperluan compatibility).
     */
    public function getAllNotifikasi(Request $request)
    {
        $data = $this->getAllNotifikasiArray($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Helper: gather notifications as array
     */
    protected function getAllNotifikasiArray(Request $request)
    {
        $adminId = $request->user()->id;

        // pengajuan lomba
        $pengajuanLomba = PengajuanLombaAdminNote::with(['pengajuanLombaMahasiswa.lomba'])
            ->where('dosen_id', $adminId)
            ->get()
            ->map(function ($item) {
                $lomba = optional(optional($item->pengajuanLombaMahasiswa)->lomba);
                $pengajuan = $item->pengajuanLombaMahasiswa;
                return [
                    'id' => $item->id,
                    'type' => 'pengajuan_lomba',
                    'judul' => $lomba->judul ?? '-',
                    'status' => $pengajuan->status ?? '-',
                    'note' => $pengajuan->notes ?? null,
                    'deskripsi' => 'Pengajuan lomba: ' . ($lomba->judul ?? '-'),
                    'created_at' => $item->created_at,
                    'is_accepted' => (bool) $item->is_accepted,
                ];
            });

        // pengajuan prestasi
        $pengajuanPrestasi = PengajuanPrestasiAdminNote::with(['prestasi'])
            ->where('dosen_id', $adminId)
            ->get()
            ->map(function ($item) {
                $prestasi = optional($item->prestasi);
                return [
                    'id' => $item->id,
                    'type' => 'pengajuan_prestasi',
                    'judul' => $prestasi->judul ?? '-',
                    'status' => $item->status ?? '-',
                    'note' => $item->note ?? null,
                    'deskripsi' => 'Pengajuan prestasi: ' . ($prestasi->judul ?? '-'),
                    'created_at' => $item->created_at,
                    'is_accepted' => (bool) $item->is_accepted,
                ];
            });

        $notifikasi = $pengajuanLomba->concat($pengajuanPrestasi)
            ->sortByDesc(function ($item) {
                return strtotime($item['created_at']);
            })
            ->values()
            ->map(function ($item) {
                $item['created_at_human'] = Carbon::parse($item['created_at'])->diffForHumans();
                // keep raw created_at as ISO string also
                $item['created_at'] = Carbon::parse($item['created_at'])->toISOString();
                return $item;
            });

        return $notifikasi->toArray();
    }

    /**
     * GET /api/admin/notifikasi/{type}/{id}
     * Show notification detail (pengajuan_lomba / pengajuan_prestasi)
     */
    public function show(Request $request, $type, $id)
    {
        $adminId = $request->user()->id;

        if ($type === 'pengajuan_lomba') {
            $note = PengajuanLombaAdminNote::with(['pengajuanLombaMahasiswa.lomba'])
                ->where('id', $id)
                ->where('dosen_id', $adminId)
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

        if ($type === 'pengajuan_prestasi') {
            $note = PengajuanPrestasiAdminNote::with(['prestasi'])
                ->where('id', $id)
                ->where('dosen_id', $adminId)
                ->first();

            if (! $note) {
                return response()->json(['success' => false, 'message' => 'Notifikasi pengajuan prestasi tidak ditemukan.'], 404);
            }

            $prestasi = $note->prestasi;

            return response()->json([
                'success' => true,
                'data' => [
                    'note' => $note,
                    'prestasi' => $prestasi,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tipe notifikasi tidak valid.'], 400);
    }

    /**
     * POST /api/admin/notifikasi/markAsRead
     * body: { id, type }
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $adminId = $request->user()->id;

        if ($type === 'pengajuan_lomba') {
            $notif = PengajuanLombaAdminNote::where('id', $id)->where('dosen_id', $adminId)->first();
        } elseif ($type === 'pengajuan_prestasi') {
            $notif = PengajuanPrestasiAdminNote::where('id', $id)->where('dosen_id', $adminId)->first();
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
     * POST /api/admin/notifikasi/markAllAsRead
     */
    public function markAllAsRead(Request $request)
    {
        $adminId = $request->user()->id;

        $countLomba = PengajuanLombaAdminNote::where('dosen_id', $adminId)
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        $countPrestasi = PengajuanPrestasiAdminNote::where('dosen_id', $adminId)
            ->where('is_accepted', false)
            ->update(['is_accepted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai dibaca.',
            'pengajuan_lomba_updated' => $countLomba,
            'pengajuan_prestasi_updated' => $countPrestasi,
        ]);
    }

    /**
     * DELETE /api/admin/notifikasi/{type}/{id}
     */
    public function destroy(Request $request, $type, $id)
    {
        $adminId = $request->user()->id;

        if ($type === 'pengajuan_lomba') {
            $deleted = PengajuanLombaAdminNote::where('id', $id)->where('dosen_id', $adminId)->delete();
        } elseif ($type === 'pengajuan_prestasi') {
            $deleted = PengajuanPrestasiAdminNote::where('id', $id)->where('dosen_id', $adminId)->delete();
        } else {
            return response()->json(['success' => false, 'message' => 'Tipe tidak valid.'], 400);
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted ? 'Notifikasi berhasil dihapus.' : 'Notifikasi tidak ditemukan.'
        ]);
    }

    /**
     * POST /api/admin/notifikasi/destroyAccepted
     * Hapus semua notifikasi yang sudah dibaca (is_accepted = true)
     */
    public function destroyIsAcceptedMessege(Request $request)
    {
        $adminId = $request->user()->id;

        $delLomba = PengajuanLombaAdminNote::where('dosen_id', $adminId)
            ->where('is_accepted', true)
            ->delete();

        $delPrestasi = PengajuanPrestasiAdminNote::where('dosen_id', $adminId)
            ->where('is_accepted', true)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_pengajuan_lomba' => $delLomba,
            'deleted_pengajuan_prestasi' => $delPrestasi,
            'message' => 'Semua notifikasi yang sudah dibaca telah dihapus.'
        ]);
    }
}