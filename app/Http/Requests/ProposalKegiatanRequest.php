<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposalKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_proposal' => 'required|in:Ormawa,HIMA',
            'ormawa_id' => 'required|exists:m_ormawa,id',
            'prodi_id' => 'nullable|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_kegiatan' => 'required|string|max:255',
            'latar_belakang' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'rab_diajukan' => 'required|numeric|min:0',
            'file_proposal' => 'nullable|file|mimes:pdf|max:5120',
            'file_lpj' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }
}
