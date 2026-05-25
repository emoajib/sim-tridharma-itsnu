<?php

namespace App\Providers;

use App\Models\AgentExecutionLog;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Setting;
use App\Observers\AgentExecutionLogObserver;
use App\Policies\DosenPolicy;
use App\Policies\ProdiPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        AgentExecutionLog::observe(AgentExecutionLogObserver::class);

        // ------------------------------
        // SPMI Event-Listener Registrations
        // ------------------------------
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AuditSevereFindingCreated::class,
            \App\Listeners\CreateRiskRegisterFromSevereFinding::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AuditStatusChanged::class,
            \App\Listeners\SendPeringatanOnAuditAssignment::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AuditStatusChanged::class,
            \App\Listeners\SyncAuditToPythonAgent::class,
        );

        // Register authorization gates (policies)
        Gate::policy(Prodi::class, ProdiPolicy::class);
        Gate::policy(Dosen::class, DosenPolicy::class);

        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        try {
            $settings = [
                'layout_type' => Setting::get('layout_type', 'navbar'),
                'theme_color' => Setting::get('theme_color', 'indigo'),
                'dashboard_default_tab' => Setting::get('dashboard_default_tab', 'overview'),
                'chat_enabled' => Setting::get('chat_enabled', true),
                'theme_mode' => Setting::get('theme_mode', 'theme3'),
                'logo_path' => Setting::get('logo_path'),
                'favicon_path' => Setting::get('favicon_path'),
            ];

            // 1. Set Active Provider & Global Switch
            $dbProvider = Setting::get('ai_provider', 'gemini');
            $isGeminiEnabled = Setting::get('gemini_enabled', true);
            $isOpenAIEnabled = Setting::get('openai_enabled', false);

            config(['app-brain.ai.default' => $dbProvider]);

            // Logic: If the selected provider is disabled, disable the entire brain engine
            if (($dbProvider === 'gemini' && ! $isGeminiEnabled) ||
                ($dbProvider === 'openai' && ! $isOpenAIEnabled)) {
                config(['app-brain.enabled' => false]);
            }

            // 2. Override Gemini Config
            if ($dbGeminiKey = Setting::get('gemini_api_key')) {
                config(['app-brain.ai.providers.gemini.api_key' => $dbGeminiKey]);
            }
            $dbGeminiModel = Setting::get('gemini_model');
            if ($dbGeminiModel) {
                config(['app-brain.ai.providers.gemini.model' => $dbGeminiModel]);
            }

            // 3. Override Custom (OpenAI) Config
            $dbOpenAIKey = Setting::get('openai_api_key');
            if ($dbOpenAIKey) {
                config(['app-brain.ai.providers.openai.api_key' => $dbOpenAIKey]);
            }
            $dbOpenAIBaseUrl = Setting::get('openai_base_url');
            if ($dbOpenAIBaseUrl) {
                config(['app-brain.ai.providers.openai.base_url' => $dbOpenAIBaseUrl]);
            }
            $dbOpenAIModel = Setting::get('openai_model');
            if ($dbOpenAIModel) {
                config(['app-brain.ai.providers.openai.model' => $dbOpenAIModel]);
            }

        } catch (\Exception $e) {
            $settings = [
                'layout_type' => 'navbar',
                'theme_color' => 'indigo',
                'dashboard_default_tab' => 'overview',
                'chat_enabled' => true,
                'theme_mode' => 'theme3',
                'logo_path' => null,
                'favicon_path' => null,
            ];
        }

        Inertia::share('appSettings', $settings);
    }
}
