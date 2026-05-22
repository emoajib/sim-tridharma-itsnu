<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PkmRequest extends FormRequest
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
            'judul_pkm' => 'required',
            'jenis_pkm' => 'required',
            'tahun_pelaksanaan' => 'required|string|size:4',
        ];
    }
}
