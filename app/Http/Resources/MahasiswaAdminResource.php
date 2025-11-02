<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'username' => $this->username,
            'email' => $this->email,
            'program_studi' => $this->whenLoaded('programStudi', function () {
                return [
                    'id' => $this->programStudi->id ?? null,
                    'nama' => $this->programStudi->nama ?? null,
                ];
            }),
            'foto' => $this->foto ? url('storage/'.$this->foto) : null,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}