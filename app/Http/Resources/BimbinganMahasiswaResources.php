<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BimbinganMahasiswaResource extends JsonResource
{
    public function toArray($request)
    {
        // resource menerima object hasil query builder (stdClass)
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'username' => $this->username,
            'email' => $this->email,
            'program_studi' => $this->program_studi,
        ];
    }
}