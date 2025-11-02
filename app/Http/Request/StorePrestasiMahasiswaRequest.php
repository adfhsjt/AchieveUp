<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestasiMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        // route should be protected by 'mahasiswa' middleware
        return true;
    }

    public function rules()
    {
        return [
            'tanggal_pengajuan' => 'nullable|date',
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
            'file_surat_tugas' => 'required|mimes:pdf|max:2048',
            'file_sertifikat' => 'required|mimes:pdf|max:2048',
            'file_poster' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'foto_kegiatan' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'dosen_pembimbing' => 'required|array|min:1',
            'dosen_pembimbing.*' => 'exists:dosen,id',
            'mahasiswas' => 'nullable|array',
            'mahasiswas.*' => 'exists:mahasiswa,id',
            'bidang' => 'required|exists:bidang,id',
        ];
    }

    public function messages()
    {
        return [
            'file_surat_tugas.required' => 'File surat tugas diperlukan.',
            'file_sertifikat.required' => 'File sertifikat diperlukan.',
        ];
    }
}