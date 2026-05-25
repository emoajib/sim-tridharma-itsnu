<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dosenId = $this->route('dosen')?->id;

        return [
            'nidn' => ['required', 'string', Rule::unique('m_dosen', 'nidn')->ignore($dosenId)],
            'nip' => ['nullable', 'string', Rule::unique('m_dosen', 'nip')->ignore($dosenId)],
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'nullable|string|max:255',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:50',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'prodi_id' => 'required|exists:m_prodi,id',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'jabatan_fungsional' => 'nullable|string|max:100',
            'email' => ['nullable', 'email', Rule::unique('m_dosen', 'email')->ignore($dosenId)],
            'telepon' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nidn.required' => 'NIDN harus diisi.',
            'nidn.unique' => 'NIDN sudah terdaftar.',
            'nama_depan.required' => 'Nama depan harus diisi.',
            'prodi_id.required' => 'Program studi harus dipilih.',
            'prodi_id.exists' => 'Program studi tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
        ];
    }
}
