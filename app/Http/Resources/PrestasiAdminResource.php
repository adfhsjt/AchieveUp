<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PrestasiAdminResource extends JsonResource
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
                return $this->dosens->map(fn($d) => ['id' => $d->id, 'nama' => $d->nama, 'nidn' => $d->nidn])->values();
            }),
            'mahasiswas' => $this->whenLoaded('mahasiswas', function () {
                return $this->mahasiswas->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama, 'nim' => $m->nim])->values();
            }),
            'notes' => $this->whenLoaded('notes', function () {
                return $this->notes->map(fn($n) => [
                    'id' => $n->id,
                    'dosen_id' => $n->dosen_id,
                    'status' => $n->status,
                    'note' => $n->note,
                    'created_at' => optional($n->created_at)->toISOString(),
                ])->values();
            }),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}