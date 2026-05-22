<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenunjangRequest extends FormRequest
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
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
            'tahun' => 'required|string|size:4',
        ];
    }
}
