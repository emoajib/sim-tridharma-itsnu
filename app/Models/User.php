<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dosen_id',
        'prodi_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}
