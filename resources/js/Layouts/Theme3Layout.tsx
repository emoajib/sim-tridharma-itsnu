import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import ErrorBoundary from '@/Components/ErrorBoundary';
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
    RefreshCw, Settings, ClipboardCheck, FileCheck, Award, X
} from 'lucide-react';

// Icon mapping from emoji keys to lucide-react components
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

export default function Theme3Layout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { props } = usePage();
    const user = props.auth?.user;
    const appSettings = props.appSettings as any;
    const chatEnabled = appSettings?.chat_enabled !== false && appSettings?.chat_enabled !== 'false';
    const themeColor = appSettings?.theme_color || 'indigo';
    const logoUrl = appSettings?.logo_path ? '/storage/' + appSettings.logo_path : null;

    const permissions = new Set(user?.permissions ?? []);
    const can = (perm?: string) => !perm || permissions.has(perm);

    // State for sidebar mobile
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [showChat, setShowChat] = useState(false);
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    useEffect(() => {
        document.documentElement.classList.add('theme-3');
        document.documentElement.classList.remove('theme-klasik', 'theme-modern');
        
        return () => {
            document.documentElement.classList.remove('theme-3');
        };
    }, []);

    // Helper to determine if a route is active
    const isActive = (routeName: string) => route().current(routeName);

    // Define link groups
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

    const otherLinks = [
        { name: 'BKD', route: 'bkd', icon: '📄', perm: 'bkd.view' },
        { name: 'Dokumen', route: 'dokumen', icon: '📑', perm: 'dokumen.view' },
        { name: 'Bimbingan', route: 'bimbingan', icon: '💬' },
        { name: 'Sarpras', route: 'sarpras', icon: '🏢', perm: 'sarpras.view' },
        { name: 'Alumni', route: 'alumni', icon: '🎓' },
        { name: 'Mitra', route: 'mitra', icon: '🤝', perm: 'kerjasama.view' },
        { name: 'Kerjasama', route: 'kerjasama', icon: '🔗', perm: 'kerjasama.view' },
        { name: 'Keuangan', route: 'keuangan', icon: '💰', perm: 'keuangan.view' },
        { name: 'Mapping CPL-MK', route: 'kurikulum.mapping', icon: '🔀', perm: 'kurikulum.view' },
        { name: 'RPS', route: 'kurikulum.rps', icon: '📃', perm: 'kurikulum.view' },
        { name: 'Audit Mutu', route: 'spmi.audit', icon: '✅', perm: 'spmi.view' },
        { name: 'Risk Register', route: 'spmi.risk', icon: '⚠️', perm: 'spmi.view' },
        { name: 'Tracer Kuisioner', route: 'tracer.kuisioner', icon: '🗂️' },
        { name: 'Tracer Jawaban', route: 'tracer.jawaban', icon: '📮' },
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
        { name: 'Manajemen Pengguna', route: 'admin.users.index', icon: '👥', perm: 'users.view' },
        { name: 'Manajemen Role', route: 'admin.roles.index', icon: '🔐', perm: 'admin.view' },
        { name: 'Daftar Permission', route: 'admin.permissions.index', icon: '📋', perm: 'admin.view' },
    ];

    // Sidebar component
    const Sidebar = () => (
        <>
            <div
                className={`fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden ${
                    sidebarOpen ? 'block' : 'hidden'
                }`}
                onClick={() => setSidebarOpen(false)}
            />
            <aside
                className={`fixed inset-y-0 left-0 z-50 flex w-64 flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 bg-white border-r border-gray-200 ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-16 items-center justify-between border-b border-gray-200 px-4">
                    <Link href="/">
                        <ApplicationLogo logoUrl={logoUrl} className="block h-9 w-auto" />
                    </Link>
                    <button
                        onClick={() => setSidebarOpen(false)}
                        className="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 lg:hidden"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <nav className="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                    <Link
                        href={route('dashboard')}
                        className={`nav-item ${isActive('dashboard') ? 'active' : ''}`}
                    >
                        <div className="nav-item-left">
                            <BarChart3 className="h-5 w-5" />
                            <span>Dashboard</span>
                        </div>
                    </Link>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Master Data</p>
                        {masterDataLinks.filter(l => can(l.perm)).map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    {(() => {
                                        const Icon = sidebarIcons[link.icon];
                                        return Icon ? <Icon className="h-5 w-5" /> : <span>{link.icon}</span>;
                                    })()}
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Portofolio</p>
                        {portofolioLinks.filter(l => can(l.perm)).map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    {(() => {
                                        const Icon = sidebarIcons[link.icon];
                                        return Icon ? <Icon className="h-5 w-5" /> : <span>{link.icon}</span>;
                                    })()}
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Lainnya</p>
                        {otherLinks.filter(l => can(l.perm)).map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    {(() => {
                                        const Icon = sidebarIcons[link.icon];
                                        return Icon ? <Icon className="h-5 w-5" /> : <span>{link.icon}</span>;
                                    })()}
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">AI Agents</p>
                        {aiAgentLinks.filter(l => can(l.perm)).map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    {(() => {
                                        const Icon = sidebarIcons[link.icon];
                                        return Icon ? <Icon className="h-5 w-5" /> : <span>{link.icon}</span>;
                                    })()}
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Admin</p>
                        {adminLinks.filter(l => can(l.perm)).map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    {(() => {
                                        const Icon = sidebarIcons[link.icon];
                                        return Icon ? <Icon className="h-5 w-5" /> : <span>{link.icon}</span>;
                                    })()}
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>
                </nav>

                <div className="border-t border-gray-200 p-4">
                    <RoleSwitcher />
                </div>
            </aside>
        </>
    );

    // Topbar component
    const Topbar = () => (
        <nav className={`sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur`}>
            <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <div className="flex items-center gap-4">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div className="text-xl font-bold text-gray-800 lg:hidden">
                        Akreditasi AI
                    </div>
                    <div className="hidden lg:block">
                        {header}
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <div className="hidden sm:flex items-center gap-3">
                        <RoleSwitcher />
                        <div className="relative">
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <button className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700">
                                        {user?.name}
                                        <svg className="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 00-1.414 0l-4-4a1 1 0 000-1.414z" clipRule="evenodd" />
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
                    
                    <button
                        onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
                        className="flex rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:hidden"
                    >
                        <span className="sr-only">Open user menu</span>
                        <div className="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                            {user?.name?.[0]}
                        </div>
                    </button>
                </div>
            </div>

            <div className={`${showingNavigationDropdown ? 'block' : 'hidden'} sm:hidden border-t border-gray-200 bg-white px-4 py-3`}>
                <div className="flex items-center px-4">
                    <div className="flex-shrink-0">
                        <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                            {user?.name?.[0]}
                        </div>
                    </div>
                    <div className="ml-3">
                        <div className="text-base font-medium text-gray-800">{user?.name}</div>
                        <div className="text-sm font-medium text-gray-500">{user?.email}</div>
                    </div>
                </div>
                <div className="mt-3 space-y-1">
                    <Link href={route('profile.edit')} className="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                        Profile
                    </Link>
                    <Link href={route('logout')} method="post" as="button" className="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                        Log Out
                    </Link>
                </div>
            </div>
        </nav>
    );

    return (
        <div className={`theme-3 min-h-screen bg-gray-50`}>
            <Sidebar />
            <div className={`lg:pl-64`}>
                <Topbar />
                <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"><ErrorBoundary>{children}</ErrorBoundary></main>
                {chatEnabled && (
                    <Suspense fallback={null}>
                        <ChatButton onClick={() => setShowChat(true)} />
                        <ChatModal isOpen={showChat} onClose={() => setShowChat(false)} />
                    </Suspense>
                )}
            </div>
        </div>
    );
}
