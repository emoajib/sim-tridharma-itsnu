import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Settings {
    layout_type: string;
    theme_color: string;
    dashboard_default_tab: string;
    chat_enabled: boolean;
    theme_mode: string;
    [key: string]: string | boolean;
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
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;
    const colors = themeColorClasses[formData.theme_color] || themeColorClasses.indigo;

    function handleChange(key: keyof Settings, value: string | boolean) {
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
                                <div className="grid grid-cols-2 gap-4">
                                    <label className={`flex cursor-pointer items-center justify-between rounded-lg border-2 p-4 transition-all ${formData.theme_mode === 'klasik' ? `${colors.border} ${colors.bg}` : 'border-gray-200 hover:border-gray-300'}`}>
                                        <div className="flex items-center">
                                            <input
                                                type="radio"
                                                name="theme_mode"
                                                value="klasik"
                                                checked={formData.theme_mode === 'klasik'}
                                                onChange={() => handleChange('theme_mode', 'klasik')}
                                                className="hidden"
                                            />
                                            <div className="text-center">
                                                <span className="block text-lg font-semibold text-gray-800">📄 Klasik</span>
                                                <span className="text-xs text-gray-500">Tampilan default Laravel</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label className={`flex cursor-pointer items-center justify-between rounded-lg border-2 p-4 transition-all ${formData.theme_mode === 'modern' ? `${colors.border} ${colors.bg}` : 'border-gray-200 hover:border-gray-300'}`}>
                                        <div className="flex items-center">
                                            <input
                                                type="radio"
                                                name="theme_mode"
                                                value="modern"
                                                checked={formData.theme_mode === 'modern'}
                                                onChange={() => handleChange('theme_mode', 'modern')}
                                                className="hidden"
                                            />
                                            <div className="text-center">
                                                <span className="block text-lg font-semibold text-gray-800">✨ Modern</span>
                                                <span className="text-xs text-gray-500">Tampilan baru dengan Outfit font</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p className="mt-2 text-xs text-gray-500">
                                    Tema Klasik menggunakan gaya default Laravel Breeze. Tema Modern menggunakan font Outfit dengan komponen styled customs.
                                </p>
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