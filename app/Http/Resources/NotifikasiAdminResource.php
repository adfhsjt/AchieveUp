<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class NotifikasiAdminResource extends JsonResource
{
    public function toArray($request)
    {
        // $this represents a stdClass-like array item as built in controller
        return [
            'id' => $this['id'] ?? null,
            'type' => $this['type'] ?? null,
            'judul' => $this['judul'] ?? null,
            'status' => $this['status'] ?? null,
            'note' => $this['note'] ?? null,
            'deskripsi' => $this['deskripsi'] ?? null,
            'created_at' => $this['created_at'] ?? null,
            'created_at_human' => $this['created_at_human'] ?? (isset($this['created_at']) ? Carbon::parse($this['created_at'])->diffForHumans() : null),
            'is_accepted' => (bool) ($this['is_accepted'] ?? false),
        ];
    }
}