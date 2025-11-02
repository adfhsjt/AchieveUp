<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectPrestasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'note' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'note.required' => 'Catatan/alas penolakan wajib diisi.',
            'note.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}