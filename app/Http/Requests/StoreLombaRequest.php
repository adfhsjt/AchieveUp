<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLombaRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by middleware (dosen:admin), jadi return true
        return true;
    }

    public function rules()
    {
        return [
            'judul' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'tanggal_daftar' => 'required|date',
            'tanggal_daftar_terakhir' => 'required|date|after_or_equal:tanggal_daftar',
            'url' => 'nullable|url',
            'tingkat' => 'required|string|in:internasional,nasional,regional,provinsi',
            'is_individu' => 'required|boolean',
            'is_akademik' => 'required|boolean',
            'file_poster' => 'nullable|image|max:2048',
            'bidang' => 'required|array|min:1',
            'bidang.*' => 'exists:bidang,id',
        ];
    }

    public function messages()
    {
        return [
            'judul.required' => 'Judul lomba wajib diisi.',
            'tempat.required' => 'Tempat lomba wajib diisi.',
            'tanggal_daftar.required' => 'Tanggal daftar wajib diisi.',
            'tanggal_daftar_terakhir.required' => 'Tanggal daftar terakhir wajib diisi.',
            'tingkat.required' => 'Tingkat lomba wajib dipilih.',
            'is_individu.required' => 'Tipe lomba (individu/kelompok) wajib dipilih.',
            'is_akademik.required' => 'Jenis lomba (akademik/non-akademik) wajib dipilih.',
            'bidang.required' => 'Minimal satu bidang lomba harus dipilih.',
        ];
    }
}