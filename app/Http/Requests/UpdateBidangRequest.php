<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBidangRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $bidangId = $this->route('id') ?? $this->route('bidang');

        return [
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('bidang', 'kode')->ignore($bidangId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bidang', 'nama')->ignore($bidangId),
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