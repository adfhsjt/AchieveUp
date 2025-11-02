<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePrestasiRequest extends FormRequest
{
    public function authorize()
    {
        // route protected by auth:sanctum and middleware dosen:admin — allow
        return true;
    }

    public function rules()
    {
        return [
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'note.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}