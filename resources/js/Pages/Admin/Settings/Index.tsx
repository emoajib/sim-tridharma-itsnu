import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

interface Settings {
    layout_type: string;
    theme_color: string;
    dashboard_default_tab: string;
    chat_enabled: boolean;
    theme_mode: string;
    favicon_path: string | null;
    logo_path: string | null;
    ai_provider: string | null;
    gemini_enabled: boolean;
    gemini_api_key: string | null;
    gemini_model: string | null;
    openai_enabled: boolean;
    openai_base_url: string | null;
    openai_api_key: string | null;
    openai_model: string | null;
    [key: string]: string | boolean | null;
}

interface Props {
    settings: Settings;
}

const themeColorClasses: Record<string, { border: string; bg: string; text: string; radio: string; toggle: string; button: string; buttonHover: string }> = {
    indigo: { border: 'border-indigo-500', bg: 'bg-indigo-50', text: 'text-indigo-700', radio: 'text-indigo-600', toggle: 'peer-checked:bg-indigo-600', button: 'bg-indigo-600', buttonHover: 'hover:bg-indigo-700' },
    blue: { border: 'border-blue-500', bg: 'bg-blue-50', text: 'text-blue-700', radio: 'text-blue-600', toggle: 'peer-checked:bg-blue-600', button: 'bg-blue-600', buttonHover: 'hover:bg-blue-700' },
    purple: { border: 'border-purple-500', bg: 'bg-purple-50', text: 'text-purple-700', radio: 'text-purple-600', toggle: 'peer-checked:bg-purple-600', button: 'bg-purple-600', buttonHover: 'hover:bg-purple-700' },
    teal: { border: 'border-teal-500', bg: 'bg-teal-50', text: 'text-teal-700', radio: 'text-teal-600', toggle: 'peer-checked:bg-teal-600', button: 'bg-teal-600', buttonHover: 'hover:bg-teal-700' },
    emerald: { border: 'border-emerald-500', bg: 'bg-emerald-50', text: 'text-emerald-700', radio: 'text-emerald-600', toggle: 'peer-checked:bg-emerald-600', button: 'bg-emerald-600', buttonHover: 'hover:bg-emerald-700' },
};

export default function Index({ settings }: Props) {
    const [formData, setFormData] = useState<Settings>(settings);
    const [saving, setSaving] = useState(false);
    const [testingKey, setTestingKey] = useState(false);
    const [showKey, setShowKey] = useState(false);
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;
    const colors = themeColorClasses[formData.theme_color] || themeColorClasses.indigo;

    function handleChange(key: keyof Settings, value: string | boolean | null) {
        setFormData((prev) => ({ ...prev, [key]: value }));
    }

    function handleSave() {
        setSaving(true);
        router.post(route('admin.settings.update'), {
            settings: formData,
        }, {
            onFinish: () => setSaving(false),
        });
    }

    function handleTestApiKey() {
        const provider = formData.ai_provider === 'gemini' ? 'gemini' : 'openai';
        const apiKey = provider === 'gemini' ? formData.gemini_api_key : formData.openai_api_key;
        
        if (!apiKey) {
            alert('Silakan isi API Key terlebih dahulu.');
            return;
        }
        
        setTestingKey(true);

        axios.post(route('admin.settings.api-key.test'), {
            api_key: apiKey,
            provider: provider,
        })
        .then((res: any) => alert(res.data.message))
        .catch((err: any) => alert(err.response?.data?.message || 'Terjadi kesalahan saat pengetesan.'))
        .finally(() => setTestingKey(false));
    }

    const aiPresets = [
        { name: 'FreeTheAi', url: 'https://api.freetheai.xyz/v1', model: 'gpt-3.5-turbo' },
        { name: 'DeepSeek (Official)', url: 'https://api.deepseek.com', model: 'deepseek-chat' },
        { name: 'Groq (Llama/Mixtral)', url: 'https://api.groq.com/openai/v1', model: 'llama-3.3-70b-versatile' },
        { name: 'OpenRouter (All Models)', url: 'https://openrouter.ai/api/v1', model: 'google/gemini-2.0-flash-001' },
        { name: 'Qwen (DashScope)', url: 'https://dashscope.aliyuncs.com/compatible-mode/v1', model: 'qwen-plus' },
        { name: 'Together AI', url: 'https://api.together.xyz/v1', model: 'meta-llama/Llama-3.3-70b-instruct-turbo' },
        { name: 'Free.ai', url: 'https://api.free.ai/v1', model: 'gpt-3.5-turbo' },
        { name: 'BazaarLink', url: 'https://bazaarlink.ai/api/v1', model: 'gpt-3.5-turbo' },
        { name: 'Edyx', url: 'https://edyx.in/v1', model: 'gpt-3.5-turbo' },
    ];

    const applyPreset = (preset: { url: string, model: string }) => {
        setFormData(prev => ({
            ...prev,
            ai_provider: 'openai',
            openai_base_url: preset.url,
            openai_model: preset.model
        }));
    };

    function handleRemoveApiKey() {
        const provider = formData.ai_provider === 'gemini' ? 'gemini' : 'openai';
        const label = provider === 'gemini' ? 'API Key Gemini' : 'API Key OpenAI/Custom';

        if (confirm(`Apakah Anda yakin ingin menghapus ${label} dari database?`)) {
            router.delete(route('admin.settings.api-key.remove'), {
                data: { provider },
                onFinish: () => {
                    const key = provider === 'gemini' ? 'gemini_api_key' : 'openai_api_key';
                    handleChange(key, null);
                    window.location.reload();
                },
            });
        }
    }

    function handleFaviconUpload(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;
        const form = new FormData();
        form.append('favicon', file);
        router.post(route('admin.settings.favicon.upload'), form, {
            onFinish: () => window.location.reload(),
        });
    }

    function handleFaviconRemove() {
        router.delete(route('admin.settings.favicon.remove'), {
            onFinish: () => window.location.reload(),
        });
    }

    function handleLogoUpload(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;
        const form = new FormData();
        form.append('logo', file);
        router.post(route('admin.settings.logo.upload'), form, {
            onFinish: () => window.location.reload(),
        });
    }

    function handleLogoRemove() {
        router.delete(route('admin.settings.logo.remove'), {
            onFinish: () => window.location.reload(),
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Pengaturan Sistem
                </h2>
            }
        >
            <Head title="Admin Settings" />

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                            {flashSuccess}
                        </div>
                    )}

                    {/* Layout Settings */}
                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">Tampilan</h3>
                        
                        <div className="space-y-4">
                            {/* Layout Type */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Tipe Layout
                                </label>
                                <div className="flex gap-4">
                                    <label className="flex items-center">
                                        <input
                                            type="radio"
                                            name="layout_type"
                                            value="navbar"
                                            checked={formData.layout_type === 'navbar'}
                                            onChange={() => handleChange('layout_type', 'navbar')}
                                            className={`h-4 w-4 ${colors.radio}`}
                                        />
                                        <span className="ml-2 text-sm text-gray-600">
                                            Top Navigation (Navbar)
                                        </span>
                                    </label>
                                    <label className="flex items-center">
                                        <input
                                            type="radio"
                                            name="layout_type"
                                            value="sidebar"
                                            checked={formData.layout_type === 'sidebar'}
                                            onChange={() => handleChange('layout_type', 'sidebar')}
                                            className={`h-4 w-4 ${colors.radio}`}
                                        />
                                        <span className="ml-2 text-sm text-gray-600">
                                            Sidebar (Kiri)
                                        </span>
                                    </label>
                                </div>
                                <p className="mt-1 text-xs text-gray-500">
                                    {formData.layout_type === 'navbar' 
                                        ? 'Menu terletak di bagian atas (default)' 
                                        : 'Menu terletak di bagian kiri dengan ikon'}
                                </p>
                            </div>

                            {/* Theme Color */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Warna Tema
                                </label>
                                <select
                                    value={formData.theme_color}
                                    onChange={(e) => handleChange('theme_color', e.target.value)}
                                    className="w-full rounded-lg border-gray-300"
                                >
                                    <option value="indigo">Indigo (Default)</option>
                                    <option value="blue">Blue</option>
                                    <option value="purple">Purple</option>
                                    <option value="teal">Teal</option>
                                    <option value="emerald">Emerald</option>
                                </select>
                            </div>

                            {/* Dashboard Default Tab */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Tab Default Dashboard
                                </label>
                                <select
                                    value={formData.dashboard_default_tab}
                                    onChange={(e) => handleChange('dashboard_default_tab', e.target.value)}
                                    className="w-full rounded-lg border-gray-300"
                                >
                                    <option value="overview">Overview</option>
                                    <option value="portofolio">Portofolio</option>
                                    <option value="bkd">BKD</option>
                                    <option value="ai">AI Agents</option>
                                </select>
                            </div>

                            {/* Chat Enabled */}
                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">
                                        AI Chat Assistant
                                    </label>
                                    <p className="text-xs text-gray-500">
                                        Aktifkan asisten AI di pojok kanan bawah
                                    </p>
                                </div>
                                <label className="relative inline-flex cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={formData.chat_enabled}
                                        onChange={(e) => handleChange('chat_enabled', e.target.checked)}
                                        className="sr-only peer"
                                    />
                                    <div className={`h-6 w-11 rounded-full bg-gray-200 ${colors.toggle} peer-checked:after:translate-x-full after:absolute after:top-0.5 after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all`}></div>
                                </label>
                            </div>

                            {/* Theme Mode */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Mode Tema
                                </label>
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <label className={`flex cursor-pointer items-center justify-between rounded-lg border-2 p-4 transition-all ${formData.theme_mode === 'klasik' ? `${colors.border} ${colors.bg}` : 'border-gray-200 hover:border-gray-300'}`}>
                                        <div className="flex items-center w-full">
                                            <input
                                                type="radio"
                                                name="theme_mode"
                                                value="klasik"
                                                checked={formData.theme_mode === 'klasik'}
                                                onChange={() => handleChange('theme_mode', 'klasik')}
                                                className="hidden"
                                            />
                                            <div className="text-center w-full">
                                                <span className="block text-lg font-semibold text-gray-800">📄 Klasik</span>
                                                <span className="text-xs text-gray-500">Tampilan default</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label className={`flex cursor-pointer items-center justify-between rounded-lg border-2 p-4 transition-all ${formData.theme_mode === 'modern' ? `${colors.border} ${colors.bg}` : 'border-gray-200 hover:border-gray-300'}`}>
                                        <div className="flex items-center w-full">
                                            <input
                                                type="radio"
                                                name="theme_mode"
                                                value="modern"
                                                checked={formData.theme_mode === 'modern'}
                                                onChange={() => handleChange('theme_mode', 'modern')}
                                                className="hidden"
                                            />
                                            <div className="text-center w-full">
                                                <span className="block text-lg font-semibold text-gray-800">✨ Modern</span>
                                                <span className="text-xs text-gray-500">Outfit Font</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label className={`flex cursor-pointer items-center justify-between rounded-lg border-2 p-4 transition-all ${formData.theme_mode === 'theme3' ? `${colors.border} ${colors.bg}` : 'border-gray-200 hover:border-gray-300'}`}>
                                        <div className="flex items-center w-full">
                                            <input
                                                type="radio"
                                                name="theme_mode"
                                                value="theme3"
                                                checked={formData.theme_mode === 'theme3'}
                                                onChange={() => handleChange('theme_mode', 'theme3')}
                                                className="hidden"
                                            />
                                            <div className="text-center w-full">
                                                <span className="block text-lg font-semibold text-gray-800">🎨 Theme 3</span>
                                                <span className="text-xs text-gray-500">Sidebar & KPI</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p className="mt-2 text-xs text-gray-500">
                                    Pilih mode tampilan sistem. Theme 3 memberikan pengalaman navigasi sidebar dengan visual KPI yang lebih detail.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* AI Configuration */}
                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm border-l-4 border-indigo-500">
                        <div className="flex items-center justify-between mb-6">
                            <div className="flex items-center gap-3">
                                <span className="text-2xl">🤖</span>
                                <h3 className="text-lg font-semibold text-gray-800">Konfigurasi AI Chatbot</h3>
                            </div>
                            <div className="flex bg-gray-100 p-1 rounded-lg">
                                <button
                                    onClick={() => handleChange('ai_provider', 'gemini')}
                                    className={`px-4 py-1.5 text-xs font-bold rounded-md transition-all ${formData.ai_provider === 'gemini' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'}`}
                                >
                                    Google Gemini
                                </button>
                                <button
                                    onClick={() => handleChange('ai_provider', 'openai')}
                                    className={`px-4 py-1.5 text-xs font-bold rounded-md transition-all ${formData.ai_provider === 'openai' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'}`}
                                >
                                    Custom (OpenAI)
                                </button>
                            </div>
                        </div>

                        {formData.ai_provider === 'gemini' ? (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <div>
                                        <p className="text-sm font-bold text-indigo-700">Status Google Gemini</p>
                                        <p className="text-xs text-indigo-500">Aktifkan atau nonaktifkan model ini saja</p>
                                    </div>
                                    <label className="relative inline-flex cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={Boolean(formData.gemini_enabled)}
                                            onChange={(e) => handleChange('gemini_enabled', e.target.checked)}
                                            className="sr-only peer"
                                        />
                                        <div className={`h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-indigo-600 after:absolute after:top-0.5 after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full`}></div>
                                    </label>
                                </div>
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">Google Gemini Model</label>
                                    <select
                                        value={String(formData.gemini_model || 'gemini-1.5-flash')}
                                        onChange={(e) => handleChange('gemini_model', e.target.value)}
                                        className="w-full rounded-lg border-gray-300"
                                    >
                                        <option value="gemini-1.5-flash">Gemini 1.5 Flash (Cepat & Irit)</option>
                                        <option value="gemini-1.5-pro">Gemini 1.5 Pro (Cerdas)</option>
                                        <option value="gemini-2.0-flash">Gemini 2.0 Flash (Eksperimental)</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">Gemini API Key</label>
                                    <div className="relative">
                                        <input
                                            type={showKey ? 'text' : 'password'}
                                            value={String(formData.gemini_api_key || '')}
                                            onChange={(e) => handleChange('gemini_api_key', e.target.value)}
                                            placeholder="AIzaSy..."
                                            className="w-full rounded-lg border-gray-300"
                                        />
                                        <button type="button" onClick={() => setShowKey(!showKey)} className="absolute right-3 top-2.5">{showKey ? '🙈' : '👁️'}</button>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <div>
                                        <p className="text-sm font-bold text-indigo-700">Status Custom Provider</p>
                                        <p className="text-xs text-indigo-500">Aktifkan atau nonaktifkan model ini saja</p>
                                    </div>
                                    <label className="relative inline-flex cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={Boolean(formData.openai_enabled)}
                                            onChange={(e) => handleChange('openai_enabled', e.target.checked)}
                                            className="sr-only peer"
                                        />
                                        <div className={`h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-indigo-600 after:absolute after:top-0.5 after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full`}></div>
                                    </label>
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-bold text-gray-500 uppercase">Presets Platform Gratis</label>
                                    <div className="flex flex-wrap gap-2 mb-4">
                                        {aiPresets.map(p => (
                                            <button
                                                key={p.name}
                                                onClick={() => applyPreset(p)}
                                                className="px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full text-[10px] font-bold hover:bg-indigo-600 hover:text-white transition-all"
                                            >
                                                + {p.name}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="col-span-2">
                                        <label className="mb-2 block text-sm font-medium text-gray-700">Base URL API</label>
                                        <input
                                            type="text"
                                            value={String(formData.openai_base_url || '')}
                                            onChange={(e) => handleChange('openai_base_url', e.target.value)}
                                            placeholder="https://api.freetheai.xyz/v1"
                                            className="w-full rounded-lg border-gray-300"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-gray-700">API Key</label>
                                        <input
                                            type="password"
                                            value={String(formData.openai_api_key || '')}
                                            onChange={(e) => handleChange('openai_api_key', e.target.value)}
                                            className="w-full rounded-lg border-gray-300"
                                        />
                                    </div>
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-gray-700">Model Name</label>
                                        <input
                                            type="text"
                                            value={String(formData.openai_model || '')}
                                            onChange={(e) => handleChange('openai_model', e.target.value)}
                                            className="w-full rounded-lg border-gray-300"
                                        />
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="flex flex-wrap gap-3 pt-4 mt-4 border-t border-gray-100">
                            <button
                                onClick={handleTestApiKey}
                                disabled={testingKey}
                                className="rounded-lg border border-indigo-200 px-4 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 transition-all uppercase tracking-wider"
                            >
                                {testingKey ? '🔄 Mengetes...' : '⚡ Test Koneksi'}
                            </button>
                            {((formData.ai_provider === 'gemini' && formData.gemini_api_key) || (formData.ai_provider === 'openai' && formData.openai_api_key)) && (
                                <button
                                    onClick={handleRemoveApiKey}
                                    className="rounded-lg border border-rose-200 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-all uppercase tracking-wider"
                                >
                                    🗑 Hapus API Key
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Branding - Logo */}
                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">Branding</h3>

                        <div className="space-y-6">
                            {/* Logo Aplikasi */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Logo Aplikasi
                                </label>
                                <p className="mb-3 text-xs text-gray-500">
                                    Upload logo ITSNU Pekalongan (PNG/JPG/SVG, maks 2 MB). Logo akan tampil di sidebar dan header.
                                </p>

                                <div className="flex flex-wrap items-center gap-6">
                                    <div className="flex h-20 w-20 items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 overflow-hidden">
                                        {formData.logo_path ? (
                                            <img
                                                src={'/storage/' + formData.logo_path}
                                                alt="Logo"
                                                className="h-full w-full object-contain"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                                <span className="text-lg font-black text-white italic">A</span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap gap-3">
                                        <label className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition-all uppercase tracking-wider">
                                            {formData.logo_path ? 'Ganti Logo' : 'Upload Logo'}
                                            <input
                                                type="file"
                                                accept=".png,.jpg,.jpeg,.svg,.webp"
                                                onChange={handleLogoUpload}
                                                className="hidden"
                                            />
                                        </label>

                                        {formData.logo_path && (
                                            <button
                                                onClick={handleLogoRemove}
                                                className="rounded-lg border border-rose-200 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-all uppercase tracking-wider"
                                            >
                                                Hapus Logo
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <hr className="border-gray-100" />

                            {/* Favicon */}
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Favicon (Ikon Tab Browser)
                                </label>
                                <p className="mb-3 text-xs text-gray-500">
                                    Upload file .ico, .png, atau .svg (maks 512 KB).
                                </p>

                                <div className="flex flex-wrap items-center gap-6">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50">
                                        {formData.favicon_path ? (
                                            <img
                                                src={'/storage/' + formData.favicon_path}
                                                alt="Favicon"
                                                className="h-10 w-10 object-contain"
                                            />
                                        ) : (
                                            <svg className="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap gap-3">
                                        <label className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition-all uppercase tracking-wider">
                                            {formData.favicon_path ? 'Ganti Favicon' : 'Upload Favicon'}
                                            <input
                                                type="file"
                                                accept=".ico,.png,.svg"
                                                onChange={handleFaviconUpload}
                                                className="hidden"
                                            />
                                        </label>

                                        {formData.favicon_path && (
                                            <button
                                                onClick={handleFaviconRemove}
                                                className="rounded-lg border border-rose-200 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-all uppercase tracking-wider"
                                            >
                                                Hapus Favicon
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Save Button */}
                    <div className="flex justify-end">
                        <button
                            onClick={handleSave}
                            disabled={saving}
                            className={`rounded-lg px-6 py-2 text-sm font-medium text-white ${colors.button} ${colors.buttonHover} disabled:opacity-50`}
                        >
                            {saving ? 'Menyimpan...' : 'Simpan Pengaturan'}
                        </button>
                    </div>

                    {/* Info Box */}
                    <div className="mt-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-700">
                        <p className="font-medium">Catatan:</p>
                        <p>Pengaturan ini hanya bisa diubah oleh Super Admin. Perubahan akan langsung diterapkan ke semua pengguna.</p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
