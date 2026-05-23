<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FileUploadRequest;
use App\Http\Requests\Admin\SettingsUpdateRequest;
use App\Models\Setting;
use App\Services\Admin\FileUploadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
                'theme_mode' => Setting::get('theme_mode', 'theme3'),
                'favicon_path' => Setting::get('favicon_path'),
                'logo_path' => Setting::get('logo_path'),
                'gemini_api_key' => Setting::get('gemini_api_key'),
                'gemini_model' => Setting::get('gemini_model', 'gemini-1.5-flash'),
                'gemini_enabled' => Setting::get('gemini_enabled', true),
                'openai_enabled' => Setting::get('openai_enabled', false),
                'ai_provider' => Setting::get('ai_provider', 'gemini'),
                'openai_base_url' => Setting::get('openai_base_url', 'https://api.openai.com/v1'),
                'openai_api_key' => Setting::get('openai_api_key'),
                'openai_model' => Setting::get('openai_model', 'gpt-3.5-turbo'),
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

    public function removeApiKey(Request $request)
    {
        $provider = $request->input('provider', 'gemini');

        $key = match ($provider) {
            'openai' => 'openai_api_key',
            default => 'gemini_api_key',
        };

        Setting::set($key, null);

        $label = $provider === 'openai' ? 'Custom OpenAI' : 'Gemini';
        return back()->with('success', "API Key {$label} berhasil dihapus");
    }

    public function testApiKey(Request $request)
    {
        $key = $request->input('api_key');
        $provider = $request->input('provider', 'gemini');

        if (! $key) {
            return response()->json([
                'success' => false,
                'message' => 'API Key wajib diisi untuk pengetesan.',
            ], 422);
        }

        try {
            if ($provider === 'openai') {
                $baseUrl = Setting::get('openai_base_url', 'https://api.openai.com/v1');
                $response = Http::timeout(15)
                    ->withHeaders(['Authorization' => "Bearer {$key}"])
                    ->get("{$baseUrl}/models");

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Koneksi Berhasil! API Key valid.',
                    ]);
                }

                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'API Key tidak valid.';

                return response()->json([
                    'success' => false,
                    'message' => "Koneksi Gagal: {$errorMessage}",
                ], 400);
            }

            $response = Http::timeout(15)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi Berhasil! API Key valid.',
                ]);
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'API Key tidak valid atau tidak diizinkan.';

            return response()->json([
                'success' => false,
                'message' => "Koneksi Gagal: {$errorMessage}",
            ], 400);
        } catch (Exception $e) {
            $label = $provider === 'openai' ? 'server AI' : 'server Google';
            return response()->json([
                'success' => false,
                'message' => "Gagal menghubungi {$label}. Pastikan server memiliki koneksi internet.",
            ], 500);
        }
    }
}

