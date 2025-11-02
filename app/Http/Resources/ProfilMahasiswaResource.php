<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfilMahasiswaResource extends JsonResource
{
    public function toArray($request)
    {
        // $this is the Mahasiswa model instance
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'username' => $this->username,
            'nim' => $this->nim ?? null,
            'email' => $this->email,
            'prodi_id' => $this->prodi_id ?? null,
            'role' => 'mahasiswa',
            'foto' => $this->foto ? url('storage/' . $this->foto) : url('img/default-avatar.png'),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}