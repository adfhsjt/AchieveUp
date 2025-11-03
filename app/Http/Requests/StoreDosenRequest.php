<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDosenRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nidn' => 'required|unique:dosen,nidn',
            'username' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:dosen,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,kajur,dosen pembimbing',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}