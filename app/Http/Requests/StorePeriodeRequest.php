<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodeRequest extends FormRequest
{
    public function authorize()
    {
        // Route di-protect oleh middleware admin, jadi return true
        return true;
    }

    public function rules()
    {
        return [
            'kode' => ['required','regex:/^\d{4}\-\d{1}$/', 'max:10'],
            'nama' => ['required', 'regex:/^\d{4}\/\d{4}\s+(ganjil|genap)$/i', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'nama.regex' => 'Format nama periode harus berupa "YYYY/YYYY ganjil/genap". Contoh: "2023/2024 genap".',
            'kode.regex' => 'Format kode periode harus berupa "YYYY-N". Contoh: "2023-1".'
        ];
    }
}