<?php

namespace App\Providers;

use App\Models\AgentExecutionLog;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Setting;
use App\Observers\AgentExecutionLogObserver;
use App\Policies\DosenPolicy;
use App\Policies\ProdiPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        if (config('database.default') === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('SET statement_timeout = ' . (int) env('DB_STATEMENT_TIMEOUT', 30000));
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('crud', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        AgentExecutionLog::observe(AgentExecutionLogObserver::class);

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

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ImportCompleted::class,
            \App\Listeners\SendImportNotification::class,
        );

        Gate::policy(Prodi::class, ProdiPolicy::class);
        Gate::policy(Dosen::class, DosenPolicy::class);

        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        try {
            $settings = Setting::getAllCached();

            $settings = [
                'layout_type' => $settings['layout_type'] ?? 'navbar',
                'theme_color' => $settings['theme_color'] ?? 'indigo',
                'dashboard_default_tab' => $settings['dashboard_default_tab'] ?? 'overview',
                'chat_enabled' => $settings['chat_enabled'] ?? true,
                'theme_mode' => $settings['theme_mode'] ?? 'theme3',
                'logo_path' => $settings['logo_path'] ?? null,
                'favicon_path' => $settings['favicon_path'] ?? null,
            ];

            $dbProvider = $settings['ai_provider'] ?? 'gemini';
            $isGeminiEnabled = $settings['gemini_enabled'] ?? true;
            $isOpenAIEnabled = $settings['openai_enabled'] ?? false;

            config(['app-brain.ai.default' => $dbProvider]);

            if (($dbProvider === 'gemini' && ! $isGeminiEnabled) ||
                ($dbProvider === 'openai' && ! $isOpenAIEnabled)) {
                config(['app-brain.enabled' => false]);
            }

            if ($dbGeminiKey = $settings['gemini_api_key'] ?? null) {
                config(['app-brain.ai.providers.gemini.api_key' => $dbGeminiKey]);
            }
            $dbGeminiModel = $settings['gemini_model'] ?? null;
            if ($dbGeminiModel) {
                config(['app-brain.ai.providers.gemini.model' => $dbGeminiModel]);
            }

            $dbOpenAIKey = $settings['openai_api_key'] ?? null;
            if ($dbOpenAIKey) {
                config(['app-brain.ai.providers.openai.api_key' => $dbOpenAIKey]);
            }
            $dbOpenAIBaseUrl = $settings['openai_base_url'] ?? null;
            if ($dbOpenAIBaseUrl) {
                config(['app-brain.ai.providers.openai.base_url' => $dbOpenAIBaseUrl]);
            }
            $dbOpenAIModel = $settings['openai_model'] ?? null;
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
