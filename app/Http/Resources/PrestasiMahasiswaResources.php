<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrestasiMahasiswaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'judul' => $this->judul,
            'tempat' => $this->tempat,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'tingkat' => $this->tingkat,
            'juara' => $this->juara,
            'is_individu' => (bool) $this->is_individu,
            'is_akademik' => (bool) $this->is_akademik,
            'url' => $this->url,
            'nomor_surat_tugas' => $this->nomor_surat_tugas,
            'tanggal_surat_tugas' => $this->tanggal_surat_tugas,
            'file_surat_tugas' => $this->file_surat_tugas ? url('storage/'.$this->file_surat_tugas) : null,
            'file_sertifikat' => $this->file_sertifikat ? url('storage/'.$this->file_sertifikat) : null,
            'file_poster' => $this->file_poster ? url('storage/'.$this->file_poster) : null,
            'foto_kegiatan' => $this->foto_kegiatan ? url('storage/'.$this->foto_kegiatan) : null,
            'status' => $this->status,
            'dosens' => $this->whenLoaded('dosens', function () {
                return $this->dosens->map(function ($d) {
                    return ['id' => $d->id, 'nama' => $d->nama, 'nidn' => $d->nidn];
                })->values();
            }),
            'mahasiswas' => $this->whenLoaded('mahasiswas', function () {
                return $this->mahasiswas->map(function ($m) {
                    return ['id' => $m->id, 'nama' => $m->nama, 'nim' => $m->nim];
                })->values();
            }),
            'bidangs' => $this->whenLoaded('bidangs', function () {
                return $this->bidangs->map(function ($b) {
                    return ['id' => $b->id, 'nama' => $b->nama, 'kode' => $b->kode];
                })->values();
            }),
        ];
    }
}