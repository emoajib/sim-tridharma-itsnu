<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KerjasamaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mitra_id' => 'required|exists:m_mitra,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'jenis_kerjasama' => 'required|string|max:50',
            'nomor_mou' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ];
    }
}
