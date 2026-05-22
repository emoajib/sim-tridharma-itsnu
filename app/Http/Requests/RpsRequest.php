<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'mata_kuliah_id' => 'required|exists:m_mata_kuliah,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'kode_rps' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];

        if ($this->isMethod('PUT')) {
            $rules['status'] = 'required|string|in:draft,selesai';
        }

        return $rules;
    }
}
