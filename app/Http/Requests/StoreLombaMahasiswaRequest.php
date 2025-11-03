<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLombaMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        // Route harus dilindungi oleh middleware 'mahasiswa' sehingga cukup return true
        return true;
    }

    public function rules()
    {
        return [
            'judul' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'tanggal_daftar' => 'required|date|after_or_equal:today',
            'tanggal_daftar_terakhir' => 'required|date|after_or_equal:tanggal_daftar',
            'url' => 'nullable|url|max:255',
            'tingkat' => 'required|in:nasional,internasional,regional,provinsi',
            'is_individu' => 'required|boolean',
            'is_akademik' => 'required|boolean',
            'file_poster' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'bidang' => 'required|array|min:1',
            'bidang.*' => 'exists:bidang,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'judul.required' => 'Judul lomba wajib diisi.',
            'tempat.required' => 'Tempat lomba wajib diisi.',
            'tanggal_daftar.required' => 'Tanggal daftar wajib diisi.',
            'tanggal_daftar.after_or_equal' => 'Tanggal daftar harus hari ini atau setelahnya.',
            'tanggal_daftar_terakhir.required' => 'Tanggal daftar terakhir wajib diisi.',
            'tanggal_daftar_terakhir.after_or_equal' => 'Tanggal daftar terakhir harus setelah atau sama dengan tanggal daftar.',
            'url.url' => 'URL pendaftaran harus berupa URL yang valid.',
            'tingkat.required' => 'Tingkat lomba wajib dipilih.',
            'is_individu.required' => 'Jenis peserta wajib dipilih.',
            'is_akademik.required' => 'Jenis kompetisi wajib dipilih.',
            'file_poster.image' => 'File poster harus berupa gambar.',
            'file_poster.mimes' => 'File poster harus berformat JPG atau PNG.',
            'file_poster.max' => 'Ukuran file poster maksimal 5MB.',
            'bidang.required' => 'Minimal satu bidang harus dipilih.',
            'bidang.*.exists' => 'Bidang yang dipilih tidak valid.',
        ];
    }
}