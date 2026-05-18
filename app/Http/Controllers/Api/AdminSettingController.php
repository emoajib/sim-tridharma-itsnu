<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'favicon_path' => Setting::get('favicon_path'),
            'logo_path' => Setting::get('logo_path'),
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

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,svg|max:512',
        ]);

        if (Setting::get('favicon_path')) {
            Storage::disk('public')->delete(Setting::get('favicon_path'));
        }

        $path = $request->file('favicon')->store('favicon', 'public');
        Setting::set('favicon_path', $path, 'string', 'Path file favicon');

        return back()->with('success', 'Favicon berhasil diperbarui');
    }

    public function removeFavicon()
    {
        if (Setting::get('favicon_path')) {
            Storage::disk('public')->delete(Setting::get('favicon_path'));
            Setting::set('favicon_path', null, 'string');
        }

        return back()->with('success', 'Favicon dikembalikan ke default');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        if (Setting::get('logo_path')) {
            Storage::disk('public')->delete(Setting::get('logo_path'));
        }

        $path = $request->file('logo')->store('logo', 'public');
        Setting::set('logo_path', $path, 'string', 'Path file logo aplikasi');

        return back()->with('success', 'Logo berhasil diperbarui');
    }

    public function removeLogo()
    {
        if (Setting::get('logo_path')) {
            Storage::disk('public')->delete(Setting::get('logo_path'));
            Setting::set('logo_path', null, 'string');
        }

        return back()->with('success', 'Logo dikembalikan ke default');
    }
}