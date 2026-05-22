<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KeuanganRequest extends FormRequest
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
            'jenis_dana' => 'required|string|max:50',
            'sumber_dana' => 'nullable|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|max:30',
        ];
    }
}
