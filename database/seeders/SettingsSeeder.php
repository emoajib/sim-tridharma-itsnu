<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'layout_type',
                'value' => 'navbar',
                'type' => 'string',
                'description' => 'Layout type: navbar atau sidebar',
            ],
            [
                'key' => 'theme_color',
                'value' => 'indigo',
                'type' => 'string',
                'description' => 'Primary theme color',
            ],
            [
                'key' => 'dashboard_default_tab',
                'value' => 'overview',
                'type' => 'string',
                'description' => 'Default tab when opening dashboard',
            ],
            [
                'key' => 'chat_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable/disable AI Chat Assistant',
            ],
            [
                'key' => 'theme_mode',
                'value' => 'theme3',
                'type' => 'string',
                'description' => 'Theme mode: klasik, modern, atau theme3',
            ],
            [
                'key' => 'logo_path',
                'value' => null,
                'type' => 'string',
                'description' => 'Path file logo aplikasi',
            ],
            [
                'key' => 'favicon_path',
                'value' => null,
                'type' => 'string',
                'description' => 'Path file favicon aplikasi',
            ],
            [
                'key' => 'gemini_api_key',
                'value' => null,
                'type' => 'string',
                'description' => 'API Key untuk Google Gemini AI',
            ],
            [
                'key' => 'gemini_model',
                'value' => 'gemini-1.5-flash',
                'type' => 'string',
                'description' => 'Model Google Gemini yang digunakan',
            ],
            [
                'key' => 'gemini_enabled',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Status aktifasi khusus Google Gemini',
            ],
            [
                'key' => 'openai_enabled',
                'value' => false,
                'type' => 'boolean',
                'description' => 'Status aktifasi khusus Custom OpenAI Provider',
            ],
            [
                'key' => 'ai_provider',
                'value' => 'gemini',
                'type' => 'string',
                'description' => 'Provider AI aktif (gemini atau openai)',
            ],
            [
                'key' => 'openai_base_url',
                'value' => 'https://api.openai.com/v1',
                'type' => 'string',
                'description' => 'Base URL untuk Custom AI Provider (compatible with OpenAI)',
            ],
            [
                'key' => 'openai_api_key',
                'value' => null,
                'type' => 'string',
                'description' => 'API Key untuk Custom AI Provider',
            ],
            [
                'key' => 'openai_model',
                'value' => 'gpt-3.5-turbo',
                'type' => 'string',
                'description' => 'Model name untuk Custom AI Provider',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        echo "✅ Settings default berhasil dibuat\n";
    }
}
