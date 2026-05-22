<?php

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
                'value' => 'klasik',
                'type' => 'string',
                'description' => 'Theme mode: klasik, modern, atau theme3',
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
