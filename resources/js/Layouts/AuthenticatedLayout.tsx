import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import ErrorBoundary from '@/Components/ErrorBoundary';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import RoleSwitcher from '@/Components/RoleSwitcher';
import { Link, usePage } from '@inertiajs/react';
import React, { PropsWithChildren, ReactNode, Suspense, useEffect, useState } from 'react';

const ChatButton = React.lazy(() => import('@/Components/ChatAssistant/ChatButton'));
const ChatModal = React.lazy(() => import('@/Components/ChatAssistant/ChatModal'));
import {
    BarChart3, Building2, BookOpen, GraduationCap, BookOpenText,
    ClipboardList, Target, CalendarDays, Microscope, FileText,
    Handshake, Folder, FileSpreadsheet, FolderOpen, MessageSquare,
    Building, Users, Link as LinkIcon, Wallet, GitBranch, ShieldCheck,
    AlertTriangle, TrendingUp, Bell, CheckCircle, Bot, Lightbulb,
    RefreshCw, Settings, ClipboardCheck, FileCheck, Award
} from 'lucide-react';

const sidebarIcons: Record<string, React.ComponentType<{ className?: string }>> = {
    '📊': BarChart3,
    '🏛️': Building2,
    '📚': BookOpen,
    '👨‍🏫': GraduationCap,
    '📖': BookOpenText,
    '📋': ClipboardList,
    '🎯': Target,
    '📅': CalendarDays,
    '🎓': GraduationCap,
    '🔬': Microscope,
    '📝': FileText,
    '🤝': Handshake,
    '📁': Folder,
    '📄': FileSpreadsheet,
    '📑': FolderOpen,
    '💬': MessageSquare,
    '🏢': Building,
    '🔗': LinkIcon,
    '💰': Wallet,
    '🔀': GitBranch,
    '📃': FileText,
    '✅': ShieldCheck,
    '⚠️': AlertTriangle,
    '🚨': Bell,
    '✔️': CheckCircle,
    '🤖': Bot,
    '💡': Lightbulb,
    '🔄': RefreshCw,
    '⚙️': Settings,
    '🗂️': ClipboardCheck,
    '📮': FileCheck,
    '🏆': Award,
};

const themeColors: Record<string, { primary: string; primaryHover: string; light: string; text: string; border: string }> = {
    indigo: { primary: 'bg-indigo-600', primaryHover: 'hover:bg-indigo-700', light: 'bg-indigo-50', text: 'text-indigo-700', border: 'border-indigo-500' },
    blue: { primary: 'bg-blue-600', primaryHover: 'hover:bg-blue-700', light: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-500' },
    purple: { primary: 'bg-purple-600', primaryHover: 'hover:bg-purple-700', light: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-500' },
    teal: { primary: 'bg-teal-600', primaryHover: 'hover:bg-teal-700', light: 'bg-teal-50', text: 'text-teal-700', border: 'border-teal-500' },
    emerald: { primary: 'bg-emerald-600', primaryHover: 'hover:bg-emerald-700', light: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-500' },
};

import Theme3Layout from './Theme3Layout';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { props } = usePage();
    const auth = (props.auth as any) || {};
    const user = auth.user;
    const appSettings = (props.appSettings as any) || {};
    const themeMode = appSettings?.theme_mode || 'theme3';
    const logoUrl = appSettings?.logo_path ? '/storage/' + appSettings.logo_path : null;

    // Use Theme 3 layout when theme_mode is 'theme3'
    if (themeMode === 'theme3') {
        return <Theme3Layout header={header}>{children}</Theme3Layout>;
    }

    const permissions = new Set(auth.user?.permissions ?? []);
    const can = (perm?: string) => !perm || permissions.has(perm);

    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [showingMasterData, setShowingMasterData] = useState(false);
    const [showingPortofolio, setShowingPortofolio] = useState(false);
    const [showingSpmi, setShowingSpmi] = useState(false);
    const [showingBudgetKinerja, setShowingBudgetKinerja] = useState(false);
    const [showingAdmin, setShowingAdmin] = useState(false);
    const [showChat, setShowChat] = useState(false);
    const [sidebarOpen, setSidebarOpen] = useState(false);

     const layoutType = appSettings?.layout_type || 'navbar';
     const chatEnabled = appSettings?.chat_enabled !== false && appSettings?.chat_enabled !== 'false';
     const themeColor = appSettings?.theme_color || 'indigo';
     const colors = themeColors[themeColor] || themeColors.indigo;

     const isModernTheme = themeMode === 'modern';

     useEffect(() => {
         if (isModernTheme) {
             document.documentElement.classList.add('theme-modern');
             document.documentElement.classList.remove('theme-klasik', 'theme-3');
         } else {
             // klasik
             document.documentElement.classList.add('theme-klasik');
             document.documentElement.classList.remove('theme-modern', 'theme-3');
         }
     }, [themeMode]);

    const masterDataLinks = [
        { name: 'Fakultas', route: 'master-data.fakultas', icon: '🏛️', perm: 'master-data.view' },
        { name: 'Prodi', route: 'master-data.prodi', icon: '📚', perm: 'master-data.view' },
        { name: 'Dosen', route: 'master-data.dosen', icon: '👨‍🏫', perm: 'master-data.view' },
        { name: 'Mata Kuliah', route: 'master-data.mata-kuliah', icon: '📖', perm: 'master-data.view' },
        { name: 'Kurikulum', route: 'master-data.kurikulum', icon: '📋', perm: 'master-data.view' },
        { name: 'CPL', route: 'master-data.cpl', icon: '🎯', perm: 'master-data.view' },
        { name: 'Periode Akademik', route: 'master-data.periode-akademik', icon: '📅', perm: 'master-data.view' },
    ];

    const portofolioLinks = [
        { name: 'Dashboard Portofolio', route: 'portofolio', icon: '📊', perm: 'portofolio.view' },
        { name: 'Pendidikan', route: 'portofolio.pendidikan', icon: '🎓', perm: 'portofolio.view' },
        { name: 'Penelitian', route: 'portofolio.penelitian', icon: '🔬', perm: 'portofolio.view' },
        { name: 'Publikasi', route: 'portofolio.publikasi', icon: '📝', perm: 'portofolio.view' },
        { name: 'PKM', route: 'portofolio.pkm', icon: '🤝', perm: 'portofolio.view' },
        { name: 'Penunjang', route: 'portofolio.penunjang', icon: '📁', perm: 'portofolio.view' },
    ];

    const budgetKinerjaLinks = [
        { name: 'RKAT', route: 'rkat.index', icon: '💰', perm: 'rkat.view' },
        { name: 'Pagu Anggaran', route: 'rkat.pagu', icon: '📊', perm: 'rkat.configure' },
        { name: 'IKU', route: 'iku.index', icon: '🎯', perm: 'iku.view' },
        { name: 'Cascading IKU', route: 'iku.cascading', icon: '🔀', perm: 'cascading.view' },
    ];

    const tridharmaOtherLinks = [
        { name: 'BKD', route: 'bkd', icon: '📄', perm: 'bkd.view' },
        { name: 'Dokumen', route: 'dokumen', icon: '📑', perm: 'dokumen.view' },
        { name: 'Bimbingan', route: 'bimbingan', icon: '💬' },
        { name: 'Sarpras', route: 'sarpras', icon: '🏢', perm: 'sarpras.view' },
        { name: 'Alumni', route: 'alumni', icon: '🎓' },
        { name: 'Mitra', route: 'mitra', icon: '🤝', perm: 'kerjasama.view' },
        { name: 'Kerjasama', route: 'kerjasama', icon: '🔗', perm: 'kerjasama.view' },
        { name: 'Keuangan', route: 'keuangan', icon: '💰', perm: 'keuangan.view' },
        { name: 'Tracer Kuisioner', route: 'tracer.kuisioner', icon: '🗂️' },
        { name: 'Tracer Jawaban', route: 'tracer.jawaban', icon: '📮' },
    ];

    const spmiLinks = [
        { name: 'Mapping CPL-MK', route: 'kurikulum.mapping', icon: '🔀', perm: 'kurikulum.view' },
        { name: 'RPS', route: 'kurikulum.rps', icon: '📃', perm: 'kurikulum.view' },
        { name: 'Audit Mutu', route: 'spmi.audit', icon: '✅', perm: 'spmi.view' },
        { name: 'Risk Register', route: 'spmi.risk', icon: '⚠️', perm: 'spmi.view' },
        { name: 'Dashboard SPMI', route: 'spmi.dashboard', icon: '📊', perm: 'spmi.view' },
        { name: 'Standar Mutu', route: 'spmi.standar-mutu', icon: '🎯', perm: 'spmi.view' },
        { name: 'CAPA', route: 'spmi.capa', icon: '🔄', perm: 'spmi.view' },
        { name: 'Siklus PPEPP', route: 'spmi.cycle', icon: '📅', perm: 'spmi.view' },
        { name: 'EDPS', route: 'spmi.edps', icon: '📋', perm: 'spmi.view' },
        { name: 'RTM', route: 'spmi.rtm', icon: '🗂️', perm: 'spmi.view' },
        { name: 'Dokumen Mutu', route: 'spmi.dokumen-mutu', icon: '📄', perm: 'spmi.view' },
        { name: 'Survey SPMI', route: 'spmi.survey', icon: '📮', perm: 'spmi.view' },
    ];

    const aiAgentLinks = [
        { name: 'Prediksi Akreditasi', route: 'prediksi', icon: '📊', perm: 'agent-ai.view' },
        { name: 'Peringatan Dini', route: 'peringatan', icon: '🚨', perm: 'agent-ai.view' },
        { name: 'Verifikasi Dokumen', route: 'verifikasi', icon: '✔️', perm: 'agent-ai.view' },
        { name: 'Generator Dokumen', route: 'generator', icon: '🤖', perm: 'agent-ai.view' },
        { name: 'Rekomendasi Strategis', route: 'rekomendasi', icon: '💡', perm: 'agent-ai.view' },
        { name: 'Integrasi Data', route: 'integrasi', icon: '🔄', perm: 'agent-ai.view' },
        { name: 'AIPT', route: 'aipt.index', icon: '🏆', perm: 'agent-ai.view' },
    ];

    const adminLinks = [
        { name: 'Pengaturan Sistem', route: 'admin.settings', icon: '⚙️', perm: 'admin.view' },
        { name: 'Lembaga Akreditasi', route: 'admin.lembaga.index', icon: '🏛️', perm: 'admin.view' },
        { name: 'Instrumen Penilaian', route: 'admin.instrumen.index', icon: '📋', perm: 'admin.view' },
        { name: 'Indikator Akreditasi', route: 'admin.indikator.index', icon: '🎯', perm: 'admin.view' },
        { name: 'Template', route: 'admin.templates.index', icon: '📄', perm: 'admin.view' },
        { name: 'Knowledge Base', route: 'admin.knowledge-base.index', icon: '📚', perm: 'admin.view' },
    ];

    const isMasterDataActive = masterDataLinks.some((l) => route().current(l.route));
    const isPortofolioActive = portofolioLinks.some((l) => route().current(l.route));
    const isTridharmaActive = tridharmaOtherLinks.some((l) => route().current(l.route));
    const isBudgetKinerjaActive = budgetKinerjaLinks.some((l) => route().current(l.route));
    const isSpmiActive = spmiLinks.some((l) => route().current(l.route));
    const isAiAgentActive = aiAgentLinks.some((l) => route().current(l.route));
    const isAdminActive = adminLinks.some((l) => route().current(l.route));

    const getDropdownActiveClass = (active: boolean) => {
        if (active) {
            return `inline-flex items-center border-b-2 px-1 pt-1 text-sm font-bold transition duration-150 ease-in-out border-current ${colors.text}`;
        }
        return 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700';
    };

    const Sidebar = () => (
        <>
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}
            <aside
                className={`fixed inset-y-0 left-0 z-50 flex w-64 flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                } ${isModernTheme ? 'sidebar-modern' : 'bg-gray-900 shadow-xl'}`}
            >
                <div className="flex h-16 items-center justify-center border-b border-gray-800/50">
                    <Link href="/">
                        <ApplicationLogo logoUrl={logoUrl} isDark={!isModernTheme} className="block w-auto" />
                    </Link>
                </div>

                <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 custom-scrollbar">
                    <Link
                        href={route('dashboard')}
                        className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-bold transition-all mb-4 ${
                            route().current('dashboard')
                                ? isModernTheme
                                    ? 'sidebar-item-active'
                                    : `${colors.primary} text-white shadow-lg`
                                : isModernTheme
                                ? 'sidebar-item-modern'
                                : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                        }`}
                    >
                        <BarChart3 className="h-5 w-5" />
                        <span>DASHBOARD</span>
                    </Link>

                    <div className="pt-2 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Master Data</div>
                    {masterDataLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Kinerja Tridharma</div>
                    {portofolioLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Penjaminan Mutu (SPMI)</div>
                    {spmiLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Anggaran & Kinerja</div>
                    {budgetKinerjaLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Layanan Lainnya</div>
                    {tridharmaOtherLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Agent AI Copilot</div>
                    {aiAgentLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-1 px-4 text-[10px] font-black uppercase tracking-widest text-gray-500">Administrasi</div>
                    {adminLinks.filter(l => can(l.perm)).map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme ? 'sidebar-item-active' : `${colors.light} ${colors.text} font-bold`
                                    : isModernTheme ? 'sidebar-item-modern' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            {(() => {
                                const Icon = sidebarIcons[link.icon];
                                return Icon ? <Icon className="h-5 w-5" /> : <span className="text-base">{link.icon}</span>;
                            })()}
                            <span>{link.name}</span>
                        </Link>
                    ))}
                </nav>

                <div className="border-t border-gray-800 p-4 bg-gray-900/50 backdrop-blur-sm">
                    <RoleSwitcher />
                </div>
            </aside>
        </>
    );

    if (layoutType === 'sidebar') {
        return (
            <div className={`min-h-screen ${isModernTheme ? 'bg-gray-50' : 'bg-gray-100'}`}>
                <Sidebar />
                <div className="lg:pl-64">
                    <nav className={`sticky top-0 z-30 border-b ${isModernTheme ? 'border-gray-200 bg-white/80 backdrop-blur' : 'border-gray-100 bg-white shadow-sm'}`}>
                        <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                            <button onClick={() => setSidebarOpen(!sidebarOpen)} className="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </button>
                            <div className="flex items-center gap-4">
                                <div className="hidden sm:block text-xs font-bold text-gray-400 uppercase tracking-widest">ITSNU Pekalongan • Sistem Akreditasi Multi-Agent</div>
                            </div>
                            <div className="flex items-center gap-3">
                                <RoleSwitcher />
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <button className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-black leading-4 text-gray-700 transition hover:text-gray-900 focus:outline-none">
                                            {user?.name?.toUpperCase() || 'USER'}
                                            <svg className="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                            </svg>
                                        </button>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>
                    </nav>
                    {header && (
                        <header className={`${isModernTheme ? 'bg-white border-b border-gray-200' : 'bg-white shadow-sm'}`}>
                            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                        </header>
                    )}
                    <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"><ErrorBoundary>{children}</ErrorBoundary></main>
                </div>
                {chatEnabled && (
                    <Suspense fallback={null}>
                        <ChatButton onClick={() => setShowChat(true)} />
                        <ChatModal isOpen={showChat} onClose={() => setShowChat(false)} />
                    </Suspense>
                )}
            </div>
        );
    }

    return (
        <div className={`min-h-screen ${isModernTheme ? 'bg-gray-50' : 'bg-gray-100'}`}>
            <nav className={`border-b ${isModernTheme ? 'border-gray-200 bg-white/80 backdrop-blur' : 'border-gray-100 bg-white shadow-sm'}`}>
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/"><ApplicationLogo logoUrl={logoUrl} className="block w-auto" /></Link>
                            </div>
                            <div className="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')}>DASHBOARD</NavLink>
                                
                                <div className="relative flex items-center">
                                    <button onClick={() => setShowingMasterData(!showingMasterData)} className={getDropdownActiveClass(isMasterDataActive)}>
                                        MASTER DATA <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                    {showingMasterData && (
                                        <div className="absolute left-0 top-14 mt-2 w-56 rounded-md bg-white shadow-2xl ring-1 ring-black ring-opacity-5 z-50" onMouseLeave={() => setShowingMasterData(false)}>
                                            <div className="py-1">{masterDataLinks.filter(l => can(l.perm)).map((link) => (
                                                <Link key={link.route} href={route(link.route)} className={`block px-4 py-2 text-sm ${route().current(link.route) ? `${colors.light} ${colors.text} font-bold` : 'text-gray-700 hover:bg-gray-50'}`}>{link.name}</Link>
                                            ))}</div>
                                        </div>
                                    )}
                                </div>

                                <div className="relative flex items-center">
                                    <button onClick={() => setShowingPortofolio(!showingPortofolio)} className={getDropdownActiveClass(isPortofolioActive)}>
                                        PORTOFOLIO <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                    {showingPortofolio && (
                                        <div className="absolute left-0 top-14 mt-2 w-56 rounded-md bg-white shadow-2xl ring-1 ring-black ring-opacity-5 z-50" onMouseLeave={() => setShowingPortofolio(false)}>
                                            <div className="py-1">{portofolioLinks.filter(l => can(l.perm)).map((link) => (
                                                <Link key={link.route} href={route(link.route)} className={`block px-4 py-2 text-sm ${route().current(link.route) ? `${colors.light} ${colors.text} font-bold` : 'text-gray-700 hover:bg-gray-50'}`}>{link.name}</Link>
                                            ))}</div>
                                        </div>
                                    )}
                                </div>

                                <div className="relative flex items-center">
                                    <button onClick={() => setShowingSpmi(!showingSpmi)} className={getDropdownActiveClass(isSpmiActive)}>
                                        MUTU (SPMI) <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                    {showingSpmi && (
                                        <div className="absolute left-0 top-14 mt-2 w-56 rounded-md bg-white shadow-2xl ring-1 ring-black ring-opacity-5 z-50" onMouseLeave={() => setShowingSpmi(false)}>
                                            <div className="py-1">{spmiLinks.filter(l => can(l.perm)).map((link) => (
                                                <Link key={link.route} href={route(link.route)} className={`block px-4 py-2 text-sm ${route().current(link.route) ? `${colors.light} ${colors.text} font-bold` : 'text-gray-700 hover:bg-gray-50'}`}>{link.name}</Link>
                                            ))}</div>
                                        </div>
                                    )}
                                </div>

                                <div className="relative flex items-center">
                                    <button onClick={() => setShowingBudgetKinerja(!showingBudgetKinerja)} className={getDropdownActiveClass(isBudgetKinerjaActive)}>
                                        RKAT & IKU <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                    {showingBudgetKinerja && (
                                        <div className="absolute left-0 top-14 mt-2 w-56 rounded-md bg-white shadow-2xl ring-1 ring-black ring-opacity-5 z-50" onMouseLeave={() => setShowingBudgetKinerja(false)}>
                                            <div className="py-1">{budgetKinerjaLinks.filter(l => can(l.perm)).map((link) => (
                                                <Link key={link.route} href={route(link.route)} className={`block px-4 py-2 text-sm ${route().current(link.route) ? `${colors.light} ${colors.text} font-bold` : 'text-gray-700 hover:bg-gray-50'}`}>{link.name}</Link>
                                            ))}</div>
                                        </div>
                                    )}
                                </div>

                                {aiAgentLinks.filter(l => can(l.perm)).map((link) => (<NavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>{link.name.toUpperCase()}</NavLink>))}

                                <div className="relative flex items-center">
                                    <button onClick={() => setShowingAdmin(!showingAdmin)} className={getDropdownActiveClass(isAdminActive)}>
                                        ADMIN <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                    {showingAdmin && (
                                        <div className="absolute right-0 top-14 mt-2 w-56 rounded-md bg-white shadow-2xl ring-1 ring-black ring-opacity-5 z-50" onMouseLeave={() => setShowingAdmin(false)}>
                                            <div className="py-1">{adminLinks.filter(l => can(l.perm)).map((link) => (
                                                <Link key={link.route} href={route(link.route)} className={`block px-4 py-2 text-sm ${route().current(link.route) ? `${colors.light} ${colors.text} font-bold` : 'text-gray-700 hover:bg-gray-50'}`}>{link.name}</Link>
                                            ))}</div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center sm:gap-4">
                            <RoleSwitcher />
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <button type="button" className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-black leading-4 text-gray-700 transition duration-150 ease-in-out hover:text-gray-900 focus:outline-none">
                                        {user?.name?.toUpperCase() || 'USER'} <svg className="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    </button>
                                </Dropdown.Trigger>
                                <Dropdown.Content>
                                    <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                    <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>
                        </div>
                        <div className="-me-2 flex items-center sm:hidden">
                            <button onClick={() => setShowingNavigationDropdown((prev) => !prev)} className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" /><path className={showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>DASHBOARD</ResponsiveNavLink>
                        {masterDataLinks.filter(l => can(l.perm)).map((link) => (<ResponsiveNavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>{link.name}</ResponsiveNavLink>))}
                    </div>
                    <div className="border-t border-gray-200 pb-1 pt-4">
                        <div className="px-4"><div className="text-base font-medium text-gray-800">{user?.name}</div></div>
                        <div className="mt-3 space-y-1"><ResponsiveNavLink href={route('profile.edit')}>Profile</ResponsiveNavLink><ResponsiveNavLink method="post" href={route('logout')} as="button">Log Out</ResponsiveNavLink></div>
                    </div>
                </div>
            </nav>
            {header && (<header className={`${isModernTheme ? 'bg-white border-b' : 'bg-white shadow-sm'}`}><div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div></header>)}
            <main className="mx-auto max-w-7xl py-12 px-4 sm:px-6 lg:px-8"><ErrorBoundary>{children}</ErrorBoundary></main>
            {chatEnabled && <Suspense fallback={null}><ChatButton onClick={() => setShowChat(true)} /><ChatModal isOpen={showChat} onClose={() => setShowChat(false)} /></Suspense>}
        </div>
    );
}
