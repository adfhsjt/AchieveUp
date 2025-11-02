<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class LombaResource extends JsonResource
{
    public function toArray($request)
    {
        $warnaTingkat = match ($this->tingkat) {
            'internasional' => 'bg-red-100 text-red-800',
            'nasional' => 'bg-blue-100 text-blue-800',
            'regional' => 'bg-green-100 text-green-800',
            'provinsi' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'tempat' => $this->tempat,
            'tanggal_daftar' => $this->tanggal_daftar,
            'tanggal_daftar_terakhir' => $this->tanggal_daftar_terakhir,
            'periode_pendaftaran' => Carbon::parse($this->tanggal_daftar)->format('d M Y') . ' s.d. ' . Carbon::parse($this->tanggal_daftar_terakhir)->format('d M Y'),
            'link' => $this->url,
            'tingkat' => $this->tingkat,
            'tingkat_warna' => $warnaTingkat,
            'is_individu' => (bool)$this->is_individu,
            'is_active' => (bool)$this->is_active,
            'file_poster' => $this->file_poster,
            'is_akademik' => (bool)$this->is_akademik,
            'bidang' => $this->whenLoaded('bidang', function () {
                return $this->bidang->map(function ($b) {
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