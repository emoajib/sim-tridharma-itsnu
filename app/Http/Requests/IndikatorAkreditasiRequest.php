<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class IndikatorAkreditasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('indikator_akreditasi')?->id;

        return [
            'instrumen_id' => 'required|exists:m_instrumen_akreditasi,id',
            'kode_indikator' => [
                'required',
                'string',
                Rule::unique('m_indikator_akreditasi', 'kode_indikator')->ignore($id)
            ],
            'nama_indikator' => 'required|string',
            'kriteria' => 'required|string',
            'bobot' => 'required|numeric',
            'target' => 'nullable|string',
            'jenis_akreditasi' => 'required|string',
        ];
    }
}
