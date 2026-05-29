<?php

namespace App\Traits;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasRoleScope
{
    public function applyScope(Builder $query, User $user, string $scopeField = 'prodi_id', array $options = []): Builder
    {
        $role = $user->active_role;
        $modelTable = $query->getModel()->getTable();

        return match ($role) {
            'Super Admin',
            'Rektor',
            'WR 1 Akademik',
            'WR 2 Keuangan & Sarpras',
            'WR 3 Kemahasiswaan',
            'LPM',
            'Kepala LPPM',
            'Staf LPPM',
            'Kepala Lembaga Kerjasama',
            'Staf Kerjasama',
            'Bagian Akademik' => $query,

            'Dekan' => $query->whereIn($scopeField,
                Prodi::where('fakultas_id', $user->prodi->fakultas_id ?? 0)
                    ->pluck('id')
            ),

            'Kaprodi',
            'Staf Prodi' => $query->where($scopeField, $user->prodi_id),

            'Dosen' => $this->applyDosenScope($query, $user, $scopeField, $modelTable),

            'Asesor Tamu' => $query->whereRaw('1=0'),

            default => $query,
        };
    }

    protected function applyDosenScope(Builder $query, User $user, string $scopeField, string $modelTable): Builder
    {
        if (! Schema::hasColumn($modelTable, 'dosen_id')) {
            return $query->whereHas('user.dosen.prodi', fn ($q) => $q->where('id', $user->dosen->prodi_id ?? 0));
        }

        return $query->where('dosen_id', $user->dosen_id);
    }

    public function applyOrmawaScope(Builder $query, User $user): Builder
    {
        $role = $user->active_role;

        return match ($role) {
            'Super Admin',
            'WR 3 Kemahasiswaan' => $query,

            'Kaprodi' => $query->where('prodi_id', $user->prodi_id),

            'Dosen' => $query->whereHas('pembinaOrmawa', fn ($q) => $q->where('dosen_id', $user->dosen_id)),

            default => $query->whereRaw('1=0'),
        };
    }

    public function canApprove(User $user, $prodiId): bool
    {
        $role = $user->active_role;

        return match ($role) {
            'Super Admin', 'LPM', 'Kepala LPPM', 'Kepala Lembaga Kerjasama' => true,
            'Kaprodi' => $user->prodi_id == $prodiId,
            default => false,
        };
    }
}
