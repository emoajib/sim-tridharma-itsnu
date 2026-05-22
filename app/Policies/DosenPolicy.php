<?php

namespace App\Policies;

use App\Models\Dosen;
use App\Models\User;

class DosenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('master-data.view');
    }

    public function view(User $user, Dosen $dosen): bool
    {
        return $user->can('master-data.view');
    }

    public function create(User $user): bool
    {
        return $user->can('master-data.create');
    }

    public function update(User $user, Dosen $dosen): bool
    {
        return $user->can('master-data.edit');
    }

    public function delete(User $user, Dosen $dosen): bool
    {
        return $user->can('master-data.delete');
    }
}
