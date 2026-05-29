<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrmawaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:100',
            'kategori' => 'required|in:BEM,DPM,HIMA,UKM',
            'prodi_id' => 'nullable|exists:m_prodi,id',
            'visi_misi' => 'nullable|string',
            'file_ad_art' => 'nullable|file|mimes:pdf|max:2048',
        ];
    }
}
