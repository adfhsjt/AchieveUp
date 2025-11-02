<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaResource extends JsonResource
{
    public function toArray($request)
    {
        // Sesuaikan fields sesuai model Mahasiswa-mu
        return [
            'id' => $this->id,
            'nim' => $this->nim ?? null,
            'nama' => $this->nama ?? null,
            'username' => $this->username ?? null,
            'email' => $this->email ?? null,
            'program_studi_id' => $this->program_studi_id ?? null,
            // jika ada relasi program_studi yang dimuat, tampilkan nama:
            'program_studi_nama' => isset($this->programStudi) ? ($this->programStudi->nama ?? null) : null,
            'created_at' => $this->created_at,
        ];
    }
}