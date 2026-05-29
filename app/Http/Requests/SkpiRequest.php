<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mahasiswa_id' => 'required|exists:m_mahasiswa,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'jenis_kegiatan' => 'required|in:Organisasi,Kepanitiaan,Prestasi,Sertifikasi,Lainnya',
            'nama_kegiatan' => 'required|string|max:255',
            'tingkat' => 'nullable|in:Lokal/Wilayah,Nasional,Internasional',
            'peran' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_kompen' => 'nullable|numeric|min:0',
            'poin_skpi' => 'nullable|numeric|min:0',
            'file_sertifikat' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }
}
