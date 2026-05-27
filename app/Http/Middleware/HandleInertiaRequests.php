<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'active_role' => $user->activeRole(),
                    'role_list' => $user->roleList(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ]) : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'appSettings' => $this->getAppSettings(),
        ];
    }

    private function getAppSettings(): array
    {
        try {
            $settings = Setting::getAllCached();

            return [
                'theme_mode' => $settings['theme_mode'] ?? 'theme3',
                'theme_color' => $settings['theme_color'] ?? 'indigo',
                'chat_enabled' => $settings['chat_enabled'] ?? true,
                'layout_type' => $settings['layout_type'] ?? 'navbar',
                'dashboard_default_tab' => $settings['dashboard_default_tab'] ?? 'overview',
                'logo_path' => $settings['logo_path'] ?? null,
                'favicon_path' => $settings['favicon_path'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'theme_mode' => 'theme3',
                'theme_color' => 'indigo',
                'chat_enabled' => true,
                'layout_type' => 'navbar',
                'dashboard_default_tab' => 'overview',
                'logo_path' => null,
                'favicon_path' => null,
            ];
        }
    }
}
