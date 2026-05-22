<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DokumenBuktiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'dosen_id' => 'nullable|exists:m_dosen,id',
            'prodi_id' => 'nullable|exists:m_prodi,id',
            'nama_dokumen' => 'required|string',
            'keterangan' => 'nullable|string',
        ];

        if ($this->isMethod('PUT')) {
            $rules['file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240';
        } else {
            $rules['file'] = 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240';
        }

        return $rules;
    }
}
