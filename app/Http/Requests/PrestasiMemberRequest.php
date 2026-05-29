<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrestasiMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestasi_id' => 'required|exists:trx_prestasi,id',
            'mahasiswa_id' => 'required|exists:m_mahasiswa,id',
            'peran' => 'required|in:Ketua,Anggota,Peserta',
            'nominal_reward' => 'nullable|numeric|min:0',
            'status_reward' => 'nullable|in:Belum Diajukan,Diajukan,Disetujui,Cair',
        ];
    }
}
