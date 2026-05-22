<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KegiatanPendidikanRequest extends FormRequest
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
                'nama_kegiatan' => 'required',
                'jenis_kegiatan' => 'required',
            ];
        }

        return [
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_kegiatan' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'sks' => 'required|integer|min:1|max:12',
            'mata_kuliah_id' => 'nullable|exists:m_mata_kuliah,id',
        ];
    }

    public function messages(): array
    {
        return [
            'dosen_id.required' => 'Dosen harus dipilih.',
            'prodi_id.required' => 'Program studi harus dipilih.',
            'periode_id.required' => 'Periode akademik harus dipilih.',
            'nama_kegiatan.required' => 'Nama kegiatan harus diisi.',
            'jenis_kegiatan.required' => 'Jenis kegiatan harus diisi.',
            'sks.required' => 'SKS harus diisi.',
            'sks.integer' => 'SKS harus berupa angka.',
            'sks.min' => 'SKS minimal 1.',
            'sks.max' => 'SKS maksimal 12.',
        ];
    }
}
