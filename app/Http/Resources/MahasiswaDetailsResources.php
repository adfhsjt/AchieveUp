<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaDetailResource extends JsonResource
{
    public function toArray($request)
    {
        // $this->mahasiswa adalah stdClass dari query; $this->prestasi adalah Collection
        $m = $this->mahasiswa;
        return [
            'mahasiswa' => [
                'id' => $m->id,
                'nim' => $m->nim,
                'nama' => $m->nama,
                'username' => $m->username,
                'email' => $m->email,
                'foto' => $m->foto ?? null,
                'program_studi' => $m->program_studi,
            ],
            'prestasi' => $this->prestasi->map(function ($p) {
                return [
                    'id' => $p->id,
                    'judul' => $p->judul,
                    'tempat' => $p->tempat,
                    'tanggal_mulai' => $p->tanggal_mulai,
                    'tingkat' => $p->tingkat,
                    'juara' => $p->juara,
                ];
            })->values(),
        ];
    }
}   