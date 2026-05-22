<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'judul_publikasi' => 'required|string',
            'jenis_publikasi' => 'required|string',
            'tingkat' => 'required|string',
            'tahun' => 'required|string|size:4',
        ];
    }
}
