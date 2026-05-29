<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LayananMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'jenis_layanan' => 'required|in:Bimbingan Karir,Kewirausahaan,Kesehatan,Beasiswa',
            'nama_program' => 'required|string|max:255',
            'tanggal_pelaksanaan' => 'required|date',
            'jumlah_peserta' => 'required|integer|min:0',
            'file_surat_tugas' => 'nullable|file|mimes:pdf|max:5120',
            'file_laporan' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }
}
