<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsetOrmawaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ormawa_id' => 'required|exists:m_ormawa,id',
            'nama_aset' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'luas_ruang_m2' => 'nullable|numeric|min:0',
            'kondisi' => 'required|in:Sangat Baik,Baik,Rusak Ringan,Rusak Berat',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:2099',
        ];
    }
}
