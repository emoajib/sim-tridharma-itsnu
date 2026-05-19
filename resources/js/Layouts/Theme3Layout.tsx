import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import RoleSwitcher from '@/Components/RoleSwitcher';
import ChatButton from '@/Components/ChatAssistant/ChatButton';
import ChatModal from '@/Components/ChatAssistant/ChatModal';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useEffect, useState } from 'react';

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
        { name: 'Fakultas', route: 'master-data.fakultas', icon: '🏛️' },
        { name: 'Prodi', route: 'master-data.prodi', icon: '📚' },
        { name: 'Dosen', route: 'master-data.dosen', icon: '👨‍🏫' },
        { name: 'Mata Kuliah', route: 'master-data.mata-kuliah', icon: '📖' },
        { name: 'Kurikulum', route: 'master-data.kurikulum', icon: '📋' },
        { name: 'CPL', route: 'master-data.cpl', icon: '🎯' },
        { name: 'Periode Akademik', route: 'master-data.periode-akademik', icon: '📅' },
    ];

    const portofolioLinks = [
        { name: 'Dashboard Portofolio', route: 'portofolio', icon: '📊' },
        { name: 'Pendidikan', route: 'portofolio.pendidikan', icon: '🎓' },
        { name: 'Penelitian', route: 'portofolio.penelitian', icon: '🔬' },
        { name: 'Publikasi', route: 'portofolio.publikasi', icon: '📝' },
        { name: 'PKM', route: 'portofolio.pkm', icon: '🤝' },
        { name: 'Penunjang', route: 'portofolio.penunjang', icon: '📁' },
    ];

    const otherLinks = [
        { name: 'BKD', route: 'bkd', icon: '📄' },
        { name: 'Dokumen', route: 'dokumen', icon: '📑' },
        { name: 'Bimbingan', route: 'bimbingan', icon: '💬' },
        { name: 'Sarpras', route: 'sarpras', icon: '🏢' },
        { name: 'Alumni', route: 'alumni', icon: '🎓' },
        { name: 'Mitra', route: 'mitra', icon: '🤝' },
        { name: 'Kerjasama', route: 'kerjasama', icon: '🔗' },
        { name: 'Keuangan', route: 'keuangan', icon: '💰' },
        { name: 'Mapping CPL-MK', route: 'kurikulum.mapping', icon: '🔀' },
        { name: 'RPS', route: 'kurikulum.rps', icon: '📃' },
        { name: 'Audit Mutu', route: 'spmi.audit', icon: '✅' },
        { name: 'Risk Register', route: 'spmi.risk', icon: '⚠️' },
    ];

    const aiAgentLinks = [
        { name: 'Prediksi Akreditasi', route: 'prediksi', icon: '📊' },
        { name: 'Peringatan Dini', route: 'peringatan', icon: '🚨' },
        { name: 'Verifikasi Dokumen', route: 'verifikasi', icon: '✔️' },
        { name: 'Generator Dokumen', route: 'generator', icon: '🤖' },
    ];

    const adminLinks = [
        { name: 'Pengaturan Sistem', route: 'admin.settings', icon: '⚙️' },
        { name: 'Lembaga Akreditasi', route: 'admin.lembaga.index', icon: '🏛️' },
        { name: 'Instrumen Penilaian', route: 'admin.instrumen.index', icon: '📋' },
        { name: 'Knowledge Base', route: 'admin.knowledge-base.index', icon: '📚' },
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
                <div className="flex h-16 items-center justify-center border-b border-gray-200">
                    <Link href="/">
                        <ApplicationLogo logoUrl={logoUrl} className="block h-9 w-auto" />
                    </Link>
                </div>

                <nav className="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                    <Link
                        href={route('dashboard')}
                        className={`nav-item ${isActive('dashboard') ? 'active' : ''}`}
                    >
                        <div className="nav-item-left">
                            <span>📊</span>
                            <span>Dashboard</span>
                        </div>
                    </Link>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Master Data</p>
                        {masterDataLinks.map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    <span>{link.icon}</span>
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Portofolio</p>
                        {portofolioLinks.map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    <span>{link.icon}</span>
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Lainnya</p>
                        {otherLinks.map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    <span>{link.icon}</span>
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">AI Agents</p>
                        {aiAgentLinks.map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    <span>{link.icon}</span>
                                    <span>{link.name}</span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <div className="nav-group pt-4">
                        <p className="nav-group-title">Admin</p>
                        {adminLinks.map((link) => (
                            <Link
                                key={link.route}
                                href={route(link.route)}
                                className={`nav-item ${isActive(link.route) ? 'active' : ''}`}
                            >
                                <div className="nav-item-left">
                                    <span>{link.icon}</span>
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
                <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{children}</main>
                {chatEnabled && (
                    <>
                        <ChatButton onClick={() => setShowChat(true)} />
                        <ChatModal isOpen={showChat} onClose={() => setShowChat(false)} />
                    </>
                )}
            </div>
        </div>
    );
}
