<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenelitianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'dosen_id' => 'required|exists:m_dosen,id',
                'prodi_id' => 'required|exists:m_prodi,id',
                'periode_id' => 'required|exists:m_periode_akademik,id',
                'judul_penelitian' => 'required',
                'jenis_penelitian' => 'required',
            ];
        }

        return [
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_penelitian' => 'required|string',
            'jenis_penelitian' => 'required|string',
            'sumber_dana' => 'nullable|string',
            'jumlah_dana' => 'nullable|numeric|min:0',
            'tahun_pelaksanaan' => 'required|string|size:4',
        ];
    }

    public function messages(): array
    {
        return [
            'dosen_id.required' => 'Dosen harus dipilih.',
            'prodi_id.required' => 'Program studi harus dipilih.',
            'periode_id.required' => 'Periode akademik harus dipilih.',
            'judul_penelitian.required' => 'Judul penelitian harus diisi.',
            'jenis_penelitian.required' => 'Jenis penelitian harus diisi.',
            'tahun_pelaksanaan.required' => 'Tahun pelaksanaan harus diisi.',
            'tahun_pelaksanaan.size' => 'Tahun pelaksanaan harus 4 digit.',
            'jumlah_dana.min' => 'Jumlah dana tidak boleh negatif.',
        ];
    }
}
