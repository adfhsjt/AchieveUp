<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class RekomendasiLombaAdminResource extends JsonResource
{
    public function toArray($request)
    {
        $lomba = $this->lomba ?? null;

        if (! $lomba) {
            return [
                'id' => $this->id,
                'judul' => '-',
                'tempat' => '-',
                'tanggal_daftar' => null,
                'tanggal_daftar_terakhir' => null,
                'periode_pendaftaran' => '-',
                'link' => null,
                'tingkat' => null,
                'tingkat_warna' => '',
                'is_individu' => null,
                'is_active' => null,
                'file_poster' => null,
                'is_akademik' => null,
                'bidang' => [],
            ];
        }

        $warnaTingkat = match ($lomba->tingkat) {
            'internasional' => 'bg-red-100 text-red-800',
            'nasional' => 'bg-blue-100 text-blue-800',
            'regional' => 'bg-green-100 text-green-800',
            'provinsi' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };

        return [
            'id' => $this->id,
            'judul' => $lomba->judul,
            'tempat' => $lomba->tempat,
            'tanggal_daftar' => $lomba->tanggal_daftar,
            'tanggal_daftar_terakhir' => $lomba->tanggal_daftar_terakhir,
            'periode_pendaftaran' => Carbon::parse($lomba->tanggal_daftar)->format('d M Y') . ' s.d. ' . Carbon::parse($lomba->tanggal_daftar_terakhir)->format('d M Y'),
            'link' => $lomba->url,
            'tingkat' => $lomba->tingkat,
            'tingkat_warna' => $warnaTingkat,
            'is_individu' => $lomba->is_individu ? 'Ya' : 'Tidak',
            'is_active' => $lomba->is_active ? 'Ya' : 'Tidak',
            'file_poster' => $lomba->file_poster ? url('storage/'.$lomba->file_poster) : null,
            'is_akademik' => $lomba->is_akademik ? 'Ya' : 'Tidak',
            'bidang' => $lomba->bidang->map(function ($b) {
                return ['id' => $b->id, 'kode' => $b->kode, 'nama' => $b->nama];
            })->values(),
        ];
    }
}