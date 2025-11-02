<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfilAdminResource extends JsonResource
{
    public function toArray($request)
    {
        // $this is the Dosen model instance
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'username' => $this->username,
            'nidn' => $this->nidn ?? null,
            'email' => $this->email,
            'role' => $this->role ?? null,
            'foto' => $this->foto ? url('storage/' . $this->foto) : url('img/default-avatar.png'),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}