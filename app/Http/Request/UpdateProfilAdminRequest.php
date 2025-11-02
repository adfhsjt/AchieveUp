<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilAdminRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by auth:sanctum + middleware, so allow here
        return true;
    }

    public function rules()
    {
        // route param 'id' is the admin id being updated
        $adminId = $this->route('id');

        return [
            'nama' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dosen', 'username')->ignore($adminId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('dosen', 'email')->ignore($adminId),
            ],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}