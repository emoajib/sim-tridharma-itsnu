<?php

namespace App\Providers;

use App\Models\AgentExecutionLog;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Setting;
use App\Observers\AgentExecutionLogObserver;
use App\Policies\DosenPolicy;
use App\Policies\ProdiPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        AgentExecutionLog::observe(AgentExecutionLogObserver::class);

        // Register authorization gates (policies)
        Gate::policy(Prodi::class, ProdiPolicy::class);
        Gate::policy(Dosen::class, DosenPolicy::class);

        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        try {
            $settings = [
                'layout_type' => Setting::get('layout_type', 'navbar'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
                'chat_enabled' => Setting::get('chat_enabled', true),
                'theme_mode' => Setting::get('theme_mode', 'klasik'),
                'logo_path' => Setting::get('logo_path'),
            ];
        } catch (\Exception $e) {
            $settings = [
                'layout_type' => 'navbar',
                'theme_color' => 'indigo',
                'dashboard_default_tab' => 'overview',
                'chat_enabled' => true,
                'theme_mode' => 'klasik',
                'logo_path' => null,
            ];
        }

        Inertia::share('appSettings', $settings);
    }
}
