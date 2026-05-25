<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Requests\Rkat;

use App\Models\Prodi;
use Illuminate\Foundation\Http\FormRequest;

class StoreUsulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $role = $user->activeRole();
        $prodiId = $this->input('prodi_id');

        if (! $prodiId) {
            return false;
        }

        $user->loadMissing(['dosen', 'prodi']);

        return match ($role) {
            'Dosen' => $user->dosen_id && $user->dosen && $user->dosen->prodi_id == $prodiId,
            'Kaprodi', 'Staf Prodi' => $user->prodi_id == $prodiId,
            'Dekan' => $user->prodi &&
                $user->prodi->fakultas_id &&
                Prodi::where('fakultas_id', $user->prodi->fakultas_id)
                    ->where('id', $prodiId)
                    ->exists(),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_kegiatan' => 'required|string|max:255',
            'deskripsi_kegiatan' => 'nullable|string',
            'estimasi_biaya' => 'required|numeric|min:0',
            'iku_id' => 'nullable|exists:m_indikator_iku,id',
            'indikator_akreditasi_id' => 'nullable|exists:m_indikator_akreditasi,id',
        ];
    }
}
