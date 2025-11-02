<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBidangRequest extends FormRequest
{
    public function authorize()
    {
        // Jika ingin cek role, bisa tambahkan gate/middleware. Untuk sekarang return true (route di-protect oleh middleware)
        return true;
    }

    public function rules()
    {
        return [
            'kode' => 'required|string|max:10|unique:bidang,kode',
            'nama' => 'required|string|max:255|unique:bidang,nama',
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