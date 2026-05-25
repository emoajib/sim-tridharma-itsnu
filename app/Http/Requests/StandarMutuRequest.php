<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StandarMutuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'kategori' => 'required|string|max:100',
            'kode_standar' => 'required|string|max:50',
            'nama_standar' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'sumber' => 'nullable|string|max:255',
            'referensi_regulasi' => 'nullable|string|max:255',
            'target_nilai' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $id = $this->route('standarMutu');
            $rules['kode_standar'] = 'required|string|max:50|unique:m_standar_mutu,kode_standar,' . $id;
        } else {
            $rules['kode_standar'] = 'required|string|max:50|unique:m_standar_mutu,kode_standar';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'kategori.required' => 'Kategori standar wajib diisi.',
            'kode_standar.required' => 'Kode standar wajib diisi.',
            'kode_standar.unique' => 'Kode standar sudah digunakan.',
            'nama_standar.required' => 'Nama standar wajib diisi.',
            'target_nilai.numeric' => 'Target nilai harus berupa angka.',
        ];
    }
}
