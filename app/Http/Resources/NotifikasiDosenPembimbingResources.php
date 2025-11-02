<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class NotifikasiDosenPembimbingResource extends JsonResource
{
    public function toArray($request)
    {
        // Accept array/stdClass or Eloquent model; normalize into $item
        $item = is_array($this->resource) ? (object) $this->resource : $this->resource;

        $createdAt = $item->created_at ?? null;
        $createdIso = $createdAt ? Carbon::parse($createdAt)->toISOString() : null;
        $createdHuman = $createdAt ? Carbon::parse($createdAt)->diffForHumans() : null;

        return [
            'id' => $item->id ?? null,
            'rekomendasi_id' => $item->rekomendasi_id ?? null,
            'lomba_id' => $item->lomba_id ?? null,
            'judul' => $item->judul ?? null,
            'tempat' => $item->tempat ?? null,
            'periode_pendaftaran' => $item->periode_pendaftaran ?? null,
            'tingkat' => $item->tingkat ?? null,
            'bidang' => $item->bidang ?? [],
            'pesan' => $item->pesan ?? null,
            'note' => $item->note ?? null,
            'is_accepted' => (bool) ($item->is_accepted ?? false),
            'created_at' => $createdIso,
            'created_at_human' => $createdHuman,
            'status' => $item->status ?? null,
        ];
    }
}