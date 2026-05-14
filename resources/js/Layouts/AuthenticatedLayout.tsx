import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [showingMasterData, setShowingMasterData] = useState(false);
    const [showingPortofolio, setShowingPortofolio] = useState(false);

    const masterDataLinks = [
        { name: 'Fakultas', route: 'master-data.fakultas' },
        { name: 'Prodi', route: 'master-data.prodi' },
        { name: 'Dosen', route: 'master-data.dosen' },
        { name: 'Mata Kuliah', route: 'master-data.mata-kuliah' },
        { name: 'Kurikulum', route: 'master-data.kurikulum' },
        { name: 'CPL', route: 'master-data.cpl' },
        { name: 'Periode Akademik', route: 'master-data.periode-akademik' },
    ];

    const isMasterDataActive = masterDataLinks.some((l) => route().current(l.route));

    const portofolioLinks = [
        { name: 'Dashboard Portofolio', route: 'portofolio' },
        { name: 'Pendidikan', route: 'portofolio.pendidikan' },
        { name: 'Penelitian', route: 'portofolio.penelitian' },
        { name: 'Publikasi', route: 'portofolio.publikasi' },
        { name: 'PKM', route: 'portofolio.pkm' },
        { name: 'Penunjang', route: 'portofolio.penunjang' },
    ];

    const isPortofolioActive = portofolioLinks.some((l) => route().current(l.route));

    const otherLinks = [
        { name: 'BKD', route: 'bkd' },
        { name: 'Dokumen', route: 'dokumen' },
        { name: 'Bimbingan', route: 'bimbingan' },
        { name: 'Mapping CPL-MK', route: 'kurikulum.mapping' },
        { name: 'RPS', route: 'kurikulum.rps' },
        { name: 'Audit Mutu', route: 'spmi.audit' },
        { name: 'Risk Register', route: 'spmi.risk' },
    ];

    const isOtherActive = otherLinks.some((l) => route().current(l.route));

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="border-b border-gray-100 bg-white">
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
                                        className={`inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition duration-150 ease-in-out ${
                                            isMasterDataActive
                                                ? 'border-indigo-400 text-gray-900'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                                        }`}
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
                                                                ? 'bg-indigo-50 text-indigo-700'
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
                                        className={`inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition duration-150 ease-in-out ${
                                            isPortofolioActive
                                                ? 'border-indigo-400 text-gray-900'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                                        }`}
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
                                                                ? 'bg-indigo-50 text-indigo-700'
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
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
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
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
