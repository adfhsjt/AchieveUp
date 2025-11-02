<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $prodiId = $this->route('id');

        return [
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('program_studi', 'kode')->ignore($prodiId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_studi', 'nama')->ignore($prodiId),
            ],
        ];
    }

    public function messages()
    {
        return [
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.unique' => 'Nama sudah digunakan.',
        ];
    }
}