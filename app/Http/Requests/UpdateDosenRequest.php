<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDosenRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'nidn' => ['required','string','max:20', Rule::unique('dosen','nidn')->ignore($id)],
            'username' => ['required','string','max:100', Rule::unique('dosen','username')->ignore($id)],
            'nama' => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('dosen','email')->ignore($id)],
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:admin,kajur,dosen pembimbing',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}