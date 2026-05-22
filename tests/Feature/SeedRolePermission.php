<?php

namespace Tests\Feature;

use App\Models\User;

trait SeedRolePermission
{
    protected function seedPermissions(): void
    {
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@itsnu.ac.id')->firstOrFail();
    }

    protected function kaprodi(): User
    {
        return User::where('email', 'kaprodi@itsnu.ac.id')->firstOrFail();
    }

    protected function dosen(): User
    {
        return User::where('email', 'dosen@itsnu.ac.id')->firstOrFail();
    }
}
