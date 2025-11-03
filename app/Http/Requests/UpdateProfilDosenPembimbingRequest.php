<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilDosenPembimbingRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by middleware 'dosen:dosen pembimbing' + auth:sanctum, so allow
        return true;
    }

    public function rules()
    {
        $dosenId = $this->route('id');

        return [
            'nama' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dosen', 'username')->ignore($dosenId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('dosen', 'email')->ignore($dosenId),
            ],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}