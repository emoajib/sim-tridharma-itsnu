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
                'theme_mode' => Setting::get('theme_mode', 'klasik'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'chat_enabled' => Setting::get('chat_enabled', true),
            ];
        } catch (\Exception $e) {
            return [
                'theme_mode' => 'klasik',
                'theme_color' => 'indigo',
                'chat_enabled' => true,
            ];
        }
    }
}
