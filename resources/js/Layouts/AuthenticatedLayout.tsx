import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import RoleSwitcher from '@/Components/RoleSwitcher';
import ChatButton from '@/Components/ChatAssistant/ChatButton';
import ChatModal from '@/Components/ChatAssistant/ChatModal';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useEffect, useState } from 'react';

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
    const user = props.auth?.user;
    const appSettings = props.appSettings as any;
    const themeMode = appSettings?.theme_mode || 'klasik';

    // Use Theme 3 layout when theme_mode is 'theme3'
    if (themeMode === 'theme3') {
        return <Theme3Layout header={header}>{children}</Theme3Layout>;
    }

    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [showingMasterData, setShowingMasterData] = useState(false);
    const [showingPortofolio, setShowingPortofolio] = useState(false);
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
        { name: 'Fakultas', route: 'master-data.fakultas', icon: '🏛️' },
        { name: 'Prodi', route: 'master-data.prodi', icon: '📚' },
        { name: 'Dosen', route: 'master-data.dosen', icon: '👨‍🏫' },
        { name: 'Mata Kuliah', route: 'master-data.mata-kuliah', icon: '📖' },
        { name: 'Kurikulum', route: 'master-data.kurikulum', icon: '📋' },
        { name: 'CPL', route: 'master-data.cpl', icon: '🎯' },
        { name: 'Periode Akademik', route: 'master-data.periode-akademik', icon: '📅' },
    ];

    const isMasterDataActive = masterDataLinks.some((l) => route().current(l.route));

    const portofolioLinks = [
        { name: 'Dashboard Portofolio', route: 'portofolio', icon: '📊' },
        { name: 'Pendidikan', route: 'portofolio.pendidikan', icon: '🎓' },
        { name: 'Penelitian', route: 'portofolio.penelitian', icon: '🔬' },
        { name: 'Publikasi', route: 'portofolio.publikasi', icon: '📝' },
        { name: 'PKM', route: 'portofolio.pkm', icon: '🤝' },
        { name: 'Penunjang', route: 'portofolio.penunjang', icon: '📁' },
    ];

    const isPortofolioActive = portofolioLinks.some((l) => route().current(l.route));

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
        { name: 'Peringatan Dini', route: 'peringatan', icon: '🚨' },
        { name: 'Verifikasi Dokumen', route: 'verifikasi', icon: '✔️' },
        { name: 'Generator Dokumen', route: 'generator', icon: '🤖' },
    ];

    const adminLinks = [
        { name: 'Pengaturan Sistem', route: 'admin.settings', icon: '⚙️' },
        { name: 'Lembaga Akreditasi', route: 'admin.lembaga.index', icon: '🏛️' },
        { name: 'Instrumen Penilaian', route: 'admin.instrumen.index', icon: '📋' },
    ];

    const isAiAgentActive = aiAgentLinks.some((l) => route().current(l.route));
    const isAdminActive = adminLinks.some((l) => route().current(l.route));

    const getDropdownActiveClass = (active: boolean) => {
        if (active) {
            return `inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition duration-150 ease-in-out border-current ${colors.text}`;
        }
        return 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700';
    };

    const getActiveDropdownColor = () => colors.text;

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
                } ${isModernTheme ? 'sidebar-modern' : 'bg-gray-900'}`}
            >
                <div className="flex h-16 items-center justify-center border-b border-gray-800">
                    <Link href="/">
                        <ApplicationLogo className="block h-9 w-auto fill-current text-white" />
                    </Link>
                </div>

                <nav className="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                    <Link
                        href={route('dashboard')}
                        className={`flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all ${
                            route().current('dashboard')
                                ? isModernTheme
                                    ? 'sidebar-item-active'
                                    : `${colors.primary} text-white`
                                : isModernTheme
                                ? 'sidebar-item-modern'
                                : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                        }`}
                    >
                        <span>📊</span>
                        <span>Dashboard</span>
                    </Link>

                    <div className="pt-4 pb-2">
                        <p className="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Master Data</p>
                    </div>
                    {masterDataLinks.map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme
                                        ? 'sidebar-item-active'
                                        : `${colors.light} ${colors.text}`
                                    : isModernTheme
                                    ? 'sidebar-item-modern'
                                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            <span>{link.icon}</span>
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-2">
                        <p className="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Portofolio</p>
                    </div>
                    {portofolioLinks.map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme
                                        ? 'sidebar-item-active'
                                        : `${colors.light} ${colors.text}`
                                    : isModernTheme
                                    ? 'sidebar-item-modern'
                                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            <span>{link.icon}</span>
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-2">
                        <p className="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Lainnya</p>
                    </div>
                    {otherLinks.map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme
                                        ? 'sidebar-item-active'
                                        : `${colors.light} ${colors.text}`
                                    : isModernTheme
                                    ? 'sidebar-item-modern'
                                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            <span>{link.icon}</span>
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-2">
                        <p className="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">AI Agents</p>
                    </div>
                    {aiAgentLinks.map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme
                                        ? 'sidebar-item-active'
                                        : `${colors.light} ${colors.text}`
                                    : isModernTheme
                                    ? 'sidebar-item-modern'
                                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            <span>{link.icon}</span>
                            <span>{link.name}</span>
                        </Link>
                    ))}

                    <div className="pt-4 pb-2">
                        <p className="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Admin</p>
                    </div>
                    {adminLinks.map((link) => (
                        <Link
                            key={link.route}
                            href={route(link.route)}
                            className={`flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm transition-all ${
                                route().current(link.route)
                                    ? isModernTheme
                                        ? 'sidebar-item-active'
                                        : `${colors.light} ${colors.text}`
                                    : isModernTheme
                                    ? 'sidebar-item-modern'
                                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                            }`}
                        >
                            <span>{link.icon}</span>
                            <span>{link.name}</span>
                        </Link>
                    ))}
                </nav>

                <div className="border-t border-gray-800 p-4">
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
                    <nav className={`sticky top-0 z-30 border-b ${isModernTheme ? 'border-gray-200 bg-white/80 backdrop-blur' : 'border-gray-100 bg-white'}`}>
                        <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                            <button
                                onClick={() => setSidebarOpen(!sidebarOpen)}
                                className="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
                            >
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div className="flex items-center gap-3">
                                <RoleSwitcher />
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <button className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:outline-none">
                                            {user?.name}
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
                        <header className={`${isModernTheme ? 'bg-white shadow-sm' : 'bg-white shadow'}`}>
                            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                        </header>
                    )}

                    <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{children}</main>
                </div>

                {chatEnabled && (
                    <>
                        <ChatButton onClick={() => setShowChat(true)} />
                        <ChatModal isOpen={showChat} onClose={() => setShowChat(false)} />
                    </>
                )}
            </div>
        );
    }

    return (
        <div className={`min-h-screen ${isModernTheme ? 'bg-gray-50' : 'bg-gray-100'}`}>
            <nav className={`border-b ${isModernTheme ? 'border-gray-200 bg-white/80 backdrop-blur' : 'border-gray-100 bg-white'}`}>
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                                    Dashboard
                                </NavLink>

                                <div className="relative">
                                    <button
                                        onClick={() => setShowingMasterData(!showingMasterData)}
                                        className={getDropdownActiveClass(isMasterDataActive)}
                                    >
                                        Master Data
                                        <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    </button>

                                    {showingMasterData && (
                                        <div
                                            className="absolute left-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                            onMouseLeave={() => setShowingMasterData(false)}
                                        >
                                            <div className="py-1" role="menu">
                                                {masterDataLinks.map((link) => (
                                                    <Link
                                                        key={link.route}
                                                        href={route(link.route)}
                                                        className={`block px-4 py-2 text-sm ${
                                                            route().current(link.route)
                                                                ? `${colors.light} ${colors.text}`
                                                                : 'text-gray-700 hover:bg-gray-50'
                                                        }`}
                                                        role="menuitem"
                                                    >
                                                        {link.name}
                                                    </Link>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="relative">
                                    <button
                                        onClick={() => setShowingPortofolio(!showingPortofolio)}
                                        className={getDropdownActiveClass(isPortofolioActive)}
                                    >
                                        Portofolio
                                        <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    </button>

                                    {showingPortofolio && (
                                        <div
                                            className="absolute left-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                            onMouseLeave={() => setShowingPortofolio(false)}
                                        >
                                            <div className="py-1" role="menu">
                                                {portofolioLinks.map((link) => (
                                                    <Link
                                                        key={link.route}
                                                        href={route(link.route)}
                                                        className={`block px-4 py-2 text-sm ${
                                                            route().current(link.route)
                                                                ? `${colors.light} ${colors.text}`
                                                                : 'text-gray-700 hover:bg-gray-50'
                                                        }`}
                                                        role="menuitem"
                                                    >
                                                        {link.name}
                                                    </Link>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {otherLinks.map((link) => (
                                    <NavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>
                                        {link.name}
                                    </NavLink>
                                ))}

                                {aiAgentLinks.map((link) => (
                                    <NavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>
                                        {link.name}
                                    </NavLink>
                                ))}

                                <div className="relative">
                                    <button
                                        onClick={() => setShowingAdmin(!showingAdmin)}
                                        className={getDropdownActiveClass(isAdminActive)}
                                    >
                                        Admin
                                        <svg className="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    </button>

                                    {showingAdmin && (
                                        <div
                                            className="absolute right-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                            onMouseLeave={() => setShowingAdmin(false)}
                                        >
                                            <div className="py-1" role="menu">
                                                {adminLinks.map((link) => (
                                                    <Link
                                                        key={link.route}
                                                        href={route(link.route)}
                                                        className={`block px-4 py-2 text-sm ${
                                                            route().current(link.route)
                                                                ? `${colors.light} ${colors.text}`
                                                                : 'text-gray-700 hover:bg-gray-50'
                                                        }`}
                                                        role="menuitem"
                                                    >
                                                        {link.name}
                                                    </Link>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                            <RoleSwitcher />
                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {user.name}
                                                <svg className="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown((prev) => !prev)}
                                className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path className={showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>
                            Dashboard
                        </ResponsiveNavLink>
                        <div className="px-4 py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">
                            Master Data
                        </div>
                        {masterDataLinks.map((link) => (
                            <ResponsiveNavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>
                                {link.name}
                            </ResponsiveNavLink>
                        ))}
                        <div className="px-4 py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">
                            Portofolio
                        </div>
                        {portofolioLinks.map((link) => (
                            <ResponsiveNavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>
                                {link.name}
                            </ResponsiveNavLink>
                        ))}
                        <div className="px-4 py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">
                            Lainnya
                        </div>
                        {otherLinks.map((link) => (
                            <ResponsiveNavLink key={link.route} href={route(link.route)} active={route().current(link.route)}>
                                {link.name}
                            </ResponsiveNavLink>
                        ))}
                    </div>
                    <div className="border-t border-gray-200 pb-1 pt-4">
                        <div className="px-4">
                            <div className="text-base font-medium text-gray-800">{user.name}</div>
                            <div className="text-sm font-medium text-gray-500">{user.email}</div>
                        </div>
                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>Profile</ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">Log Out</ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className={`${isModernTheme ? 'bg-white shadow-sm' : 'bg-white shadow'}`}>
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main>{children}</main>

            {chatEnabled && (
                <>
                    <ChatButton onClick={() => setShowChat(true)} />
                    <ChatModal isOpen={showChat} onClose={() => setShowChat(false)} />
                </>
            )}
        </div>
    );
}
