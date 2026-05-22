<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Requests\Rkat;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_kegiatan' => 'required|string|max:255',
            'deskripsi_kegiatan' => 'nullable|string',
            'estimasi_biaya' => 'required|numeric|min:0',
            'iku_id' => 'nullable|exists:m_indikator_iku,id',
            'indikator_akreditasi_id' => 'nullable|exists:m_indikator_akreditasi,id',
        ];
    }
}
