<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PengajuanLombaResource extends JsonResource
{
    public function toArray($request)
    {
        $lomba = $this->lomba;
        $warnaTingkat = $lomba ? match ($lomba->tingkat) {
            'internasional' => 'bg-red-100 text-red-800',
            'nasional' => 'bg-blue-100 text-blue-800',
            'regional' => 'bg-green-100 text-green-800',
            'provinsi' => 'bg-yellow-100 text-yellow-800',
            default => '',
        } : '';

        $warnaStatus = match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => '',
        };

        return [
            'id' => $this->id,
            'mahasiswa' => $this->when($this->mahasiswa, function () {
                return [
                    'id' => $this->mahasiswa->id ?? null,
                    'nama' => $this->mahasiswa->nama ?? null,
                    'nim' => $this->mahasiswa->nim ?? null,
                ];
            }),
            'lomba_id' => $lomba->id ?? null,
            'judul' => $lomba->judul ?? null,
            'tempat' => $lomba->tempat ?? null,
            'tanggal_daftar' => $lomba->tanggal_daftar ?? null,
            'tanggal_daftar_terakhir' => $lomba->tanggal_daftar_terakhir ?? null,
            'periode_pendaftaran' => $lomba ? Carbon::parse($lomba->tanggal_daftar)->format('d M Y') . ' s.d. ' . Carbon::parse($lomba->tanggal_daftar_terakhir)->format('d M Y') : null,
            'link' => $lomba->url ?? null,
            'tingkat' => $lomba->tingkat ?? null,
            'tingkat_warna' => $warnaTingkat,
            'is_individu' => $lomba ? ($lomba->is_individu ? 'Ya' : 'Tidak') : null,
            'is_active' => $lomba ? ($lomba->is_active ? 'Ya' : 'Tidak') : null,
            'file_poster' => $lomba->file_poster ?? null,
            'is_akademik' => $lomba ? ($lomba->is_akademik ? 'Ya' : 'Tidak') : null,
            'status' => $this->status,
            'status_warna' => $warnaStatus,
            'notes' => $this->notes,
            'admin' => $this->when($this->admin, function () {
                return [
                    'id' => $this->admin->id,
                    'nama' => $this->admin->nama ?? null,
                ];
            }),
            'bidang' => $this->when($lomba && $lomba->bidang, function () use ($lomba) {
                return $lomba->bidang->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'kode' => $b->kode,
                        'nama' => $b->nama,
                    ];
                })->values();
            }),
        ];
    }
}