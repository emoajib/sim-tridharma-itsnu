<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\DosenPolicy;
use App\Policies\ProdiPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Dosen::class => DosenPolicy::class,
        \App\Models\Prodi::class => ProdiPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin.view', fn (User $user) => $user->can('admin.view'));
        Gate::define('data-import.upload', fn (User $user) => $user->can('data-import.upload'));
        Gate::define('data-import.download-template', fn (User $user) => $user->can('data-import.download-template'));
        Gate::define('reconciliation.view', fn (User $user) => $user->can('reconciliation.view'));
        Gate::define('reconciliation.approve', fn (User $user) => $user->can('reconciliation.approve'));
        Gate::define('rkat.view', fn (User $user) => $user->can('rkat.view'));
        Gate::define('iku.view', fn (User $user) => $user->can('iku.view'));
        Gate::define('users.view', fn (User $user) => $user->can('users.view'));
    }
}
