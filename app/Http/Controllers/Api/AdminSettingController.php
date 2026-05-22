<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FileUploadRequest;
use App\Http\Requests\Admin\SettingsUpdateRequest;
use App\Models\Setting;
use App\Services\Admin\FileUploadService;
use Inertia\Inertia;

class AdminSettingController extends Controller
{
    public function __construct(
        protected FileUploadService $fileUpload,
    ) {}

    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'layout_type' => Setting::get('layout_type', 'navbar'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
                'chat_enabled' => Setting::get('chat_enabled', true),
                'theme_mode' => Setting::get('theme_mode', 'klasik'),
                'favicon_path' => Setting::get('favicon_path'),
                'logo_path' => Setting::get('logo_path'),
            ],
        ]);
    }

    public function updateMultiple(SettingsUpdateRequest $request)
    {
        foreach ($request->validated()['settings'] as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_array($value) => 'json',
                is_numeric($value) => 'number',
                default => 'string',
            };
            Setting::set($key, $value, $type);
        }

        return back()->with('success', 'Semua pengaturan berhasil diperbarui');
    }

    public function uploadFavicon(FileUploadRequest $request)
    {
        $this->fileUpload->upload($request->file('favicon'), 'favicon_path', 'favicon');

        return back()->with('success', 'Favicon berhasil diperbarui');
    }

    public function removeFavicon()
    {
        $this->fileUpload->remove('favicon_path');

        return back()->with('success', 'Favicon dikembalikan ke default');
    }

    public function uploadLogo(FileUploadRequest $request)
    {
        $this->fileUpload->upload($request->file('logo'), 'logo_path', 'logo');

        return back()->with('success', 'Logo berhasil diperbarui');
    }

    public function removeLogo()
    {
        $this->fileUpload->remove('logo_path');

        return back()->with('success', 'Logo dikembalikan ke default');
    }
}
