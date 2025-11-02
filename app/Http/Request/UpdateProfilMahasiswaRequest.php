<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        // route is protected by auth:sanctum + middleware mahasiswa, so allow
        return true;
    }

    public function rules()
    {
        $mahasiswaId = $this->route('id');

        return [
            'nama' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mahasiswas', 'username')->ignore($mahasiswaId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('mahasiswas', 'email')->ignore($mahasiswaId),
            ],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}