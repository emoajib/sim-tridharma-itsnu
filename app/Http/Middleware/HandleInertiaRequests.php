<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
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
            return [
                'theme_mode' => Setting::get('theme_mode', 'theme3'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'chat_enabled' => Setting::get('chat_enabled', true),
                'layout_type' => Setting::get('layout_type', 'navbar'),
                'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
                'logo_path' => Setting::get('logo_path'),
                'favicon_path' => Setting::get('favicon_path'),
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
