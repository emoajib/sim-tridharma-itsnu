<?php

namespace App\Policies;

use App\Models\Prodi;
use App\Models\User;

class ProdiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('master-data.view');
    }

    public function view(User $user, Prodi $prodi): bool
    {
        return $user->can('master-data.view');
    }

    public function create(User $user): bool
    {
        return $user->can('master-data.create');
    }

    public function update(User $user, Prodi $prodi): bool
    {
        return $user->can('master-data.edit');
    }

    public function delete(User $user, Prodi $prodi): bool
    {
        return $user->can('master-data.delete');
    }
}
