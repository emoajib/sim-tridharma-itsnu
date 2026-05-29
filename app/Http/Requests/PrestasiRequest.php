<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrestasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:m_kategori_prestasi,id',
            'nama_kompetisi' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'tanggal_pelaksanaan' => 'required|date',
            'tingkat' => 'required|in:Lokal/Wilayah,Nasional,Internasional',
            'peringkat' => 'required|string|max:50',
            'bukti_url' => 'nullable|url',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan_reviewer' => 'nullable|string',
        ];
    }
}
