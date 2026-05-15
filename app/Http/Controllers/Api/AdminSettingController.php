<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'layout_type' => Setting::get('layout_type', 'navbar'),
            'theme_color' => Setting::get('theme_color', 'indigo'),
            'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
            'chat_enabled' => Setting::get('chat_enabled', true),
            'theme_mode' => Setting::get('theme_mode', 'klasik'),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function updateMultiple(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $type = 'string';
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_array($value)) {
                $type = 'json';
            } elseif (is_numeric($value)) {
                $type = 'number';
            }
            Setting::set($key, $value, $type);
        }

        return back()->with('success', 'Semua pengaturan berhasil diperbarui');
    }

}