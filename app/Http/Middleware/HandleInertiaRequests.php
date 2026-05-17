<?php

namespace App\Http\Middleware;

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
            'appSettings' => [
                'theme_mode' => \App\Models\Setting::get('theme_mode', 'klasik'),
                'theme_color' => \App\Models\Setting::get('theme_color', 'indigo'),
                'chat_enabled' => \App\Models\Setting::get('chat_enabled', true),
            ]
        ];
    }
}
