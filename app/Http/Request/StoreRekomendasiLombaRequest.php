<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRekomendasiLombaRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by middleware (admin), so allow
        return true;
    }

    public function rules()
    {
        return [
            'lomba_id' => 'required|exists:lomba,id',
            'mahasiswa_id' => 'required|array|min:1',
            'mahasiswa_id.*' => 'exists:mahasiswa,id',
            'dosen_id' => 'required|array|min:1',
            'dosen_id.*' => 'exists:dosen,id',
        ];
    }

    public function messages()
    {
        return [
            'lomba_id.required' => 'Lomba wajib dipilih.',
            'mahasiswa_id.required' => 'Minimal satu mahasiswa harus dipilih.',
            'dosen_id.required' => 'Minimal satu dosen pembimbing harus dipilih.',
        ];
    }
}