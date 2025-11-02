<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'nim' => ['required','string','max:20', Rule::unique('mahasiswa','nim')->ignore($id)],
            'nama' => 'required|string|max:255',
            'username' => ['required','string','max:100', Rule::unique('mahasiswa','username')->ignore($id)],
            'email' => ['required','email','max:255', Rule::unique('mahasiswa','email')->ignore($id)],
            'password' => 'nullable|min:6|confirmed',
            'program_studi_id' => 'required|exists:program_studi,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}