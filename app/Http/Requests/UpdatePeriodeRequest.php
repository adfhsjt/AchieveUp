<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'kode' => ['required','string','max:10'],
            'nama' => ['required','string','max:255'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}