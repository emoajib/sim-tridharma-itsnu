<?php

namespace App\Providers;

use App\Models\Setting;
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
        try {
            $settings = [
                'layout_type' => Setting::get('layout_type', 'navbar'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
                'chat_enabled' => Setting::get('chat_enabled', true),
                'theme_mode' => Setting::get('theme_mode', 'klasik'),
            ];
        } catch (\Exception $e) {
            $settings = [
                'layout_type' => 'navbar',
                'theme_color' => 'indigo',
                'dashboard_default_tab' => 'overview',
                'chat_enabled' => true,
                'theme_mode' => 'klasik',
            ];
        }

        Inertia::share('appSettings', $settings);
    }
}