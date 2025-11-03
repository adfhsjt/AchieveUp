<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrestasiMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'judul' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'tingkat' => 'required|in:nasional,internasional,regional,provinsi',
            'juara' => 'required|in:1,2,3',
            'is_individu' => 'required|boolean',
            'is_akademik' => 'required|boolean',
            'url' => 'nullable|url',
            'nomor_surat_tugas' => 'required|string',
            'tanggal_surat_tugas' => 'required|date',
            'file_surat_tugas' => 'nullable|mimes:pdf|max:2048',
            'file_sertifikat' => 'nullable|mimes:pdf|max:2048',
            'file_poster' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'foto_kegiatan' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'dosen_pembimbing' => 'required|array|min:1|max:3',
            'dosen_pembimbing.*' => 'exists:dosen,id',
            'mahasiswas' => 'required|array',
            'mahasiswas.*' => 'exists:mahasiswa,id',
            'bidang' => 'required|exists:bidang,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // custom checks (e.g. if is_individu then only one mahasiswa)
            $data = $this->all();
            if (isset($data['is_individu']) && $data['is_individu']) {
                if (!empty($data['mahasiswas']) && count($data['mahasiswas']) > 1) {
                    $validator->errors()->add('mahasiswas', 'Pada mode individu hanya satu mahasiswa yang diperbolehkan.');
                }
            }
        });
    }
}