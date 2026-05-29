<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SertifikatOstamaruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('sertifikat_ostamaru')?->id;

        return [
            'mahasiswa_id' => 'required|exists:m_mahasiswa,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'jenis_sertifikat' => 'required|in:OSTAMARU,PK2,Diksar,Lainnya',
            'nomor_sertifikat' => [
                'required',
                'string',
                'max:100',
                Rule::unique('trx_sertifikat_ostamaru', 'nomor_sertifikat')->ignore($id),
            ],
            'tanggal_terbit' => 'required|date',
            'file_sertifikat' => 'required|file|mimes:pdf|max:5120',
            'is_downloadable' => 'nullable|boolean',
        ];
    }
}
