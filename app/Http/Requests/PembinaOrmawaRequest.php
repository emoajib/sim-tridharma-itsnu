<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembinaOrmawaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ormawa_id' => 'required|exists:m_ormawa,id',
            'dosen_id' => 'required|exists:m_dosen,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'sk_pembina' => 'nullable|file|mimes:pdf|max:2048',
        ];
    }
}
