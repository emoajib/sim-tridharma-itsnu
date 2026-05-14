<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Session;

#[Fillable(['name', 'email', 'password', 'dosen_id', 'prodi_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function activeRole(): ?string
    {
        $role = Session::get('active_role');
        if ($role && $this->hasRole($role)) {
            return $role;
        }
        $first = $this->roles->first();
        return $first?->name;
    }

    public function setActiveRole(string $roleName): void
    {
        if ($this->hasRole($roleName)) {
            Session::put('active_role', $roleName);
        }
    }

    public function getActiveRoleAttribute(): ?string
    {
        return $this->activeRole();
    }

    public function roleList(): array
    {
        return $this->roles->pluck('name')->toArray();
    }
}
