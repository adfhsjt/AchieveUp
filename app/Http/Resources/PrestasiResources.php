<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrestasiResource extends JsonResource
{
    public function toArray($request)
    {
        // Support both Eloquent models and stdClass from joins (but here we use Eloquent)
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'tempat' => $this->tempat,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'tanggal_mulai' => $this->tanggal_mulai ?? null,
            'tingkat' => $this->tingkat ?? null,
            'juara' => $this->juara ?? null,
            'status' => $this->status ?? null,
            'dosens' => $this->whenLoaded('dosens', function () {
                return $this->dosens->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'nama' => $d->nama ?? $d->name ?? null,
                        'nidn' => $d->nidn ?? null,
                        'username' => $d->username ?? null,
                    ];
                })->values();
            }),
        ];
    }
}   