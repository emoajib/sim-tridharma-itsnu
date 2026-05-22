<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaranaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prodi_id' => 'required|exists:m_prodi,id',
            'nama_sarana' => 'required|string|max:255',
            'jenis_sarana' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'required|string|in:baik,sedang,rusak',
            'tanggal_kalibrasi' => 'nullable|date',
            'tanggal_kalibrasi_berikut' => 'nullable|date',
        ];
    }
}
