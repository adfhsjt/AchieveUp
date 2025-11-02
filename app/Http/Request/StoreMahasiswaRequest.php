<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMahasiswaRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by 'dosen:admin' middleware
        return true;
    }

    public function rules()
    {
        return [
            'nim' => 'required|unique:mahasiswa,nim',
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:100',
            'email' => 'required|email|unique:mahasiswa,email',
            'password' => 'required|min:6|confirmed',
            'program_studi_id' => 'required|exists:program_studi,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}