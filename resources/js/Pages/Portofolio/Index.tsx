import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Dosen {
    id: number;
    nama_depan: string;
    nama_belakang: string;
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface RecentItem {
    id: number;
    title: string;
    type: string;
    status: string;
    is_verified: boolean;
}

interface Props {
    pendidikan_count: number;
    penelitian_count: number;
    publikasi_count: number;
    pkm_count: number;
    penunjang_count: number;
    recent_pendidikan: RecentItem[];
    recent_penelitian: RecentItem[];
    recent_publikasi: RecentItem[];
    recent_pkm: RecentItem[];
    recent_penunjang: RecentItem[];
    dosen_list: Dosen[];
    prodi_list: Prodi[];
    periode_list: Periode[];
}

const statCards = [
    { label: 'Pendidikan', countKey: 'pendidikan_count' as const, color: 'blue', route: 'portofolio.pendidikan', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { label: 'Penelitian', countKey: 'penelitian_count' as const, color: 'green', route: 'portofolio.penelitian', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { label: 'Publikasi', countKey: 'publikasi_count' as const, color: 'purple', route: 'portofolio.publikasi', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z' },
    { label: 'PKM', countKey: 'pkm_count' as const, color: 'orange', route: 'portofolio.pkm', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Penunjang', countKey: 'penunjang_count' as const, color: 'teal', route: 'portofolio.penunjang', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
];

const colorMap: Record<string, { bg: string; text: string; border: string; hover: string }> = {
    blue: { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-200', hover: 'hover:bg-blue-100' },
    green: { bg: 'bg-green-50', text: 'text-green-600', border: 'border-green-200', hover: 'hover:bg-green-100' },
    purple: { bg: 'bg-purple-50', text: 'text-purple-600', border: 'border-purple-200', hover: 'hover:bg-purple-100' },
    orange: { bg: 'bg-orange-50', text: 'text-orange-600', border: 'border-orange-200', hover: 'hover:bg-orange-100' },
    teal: { bg: 'bg-teal-50', text: 'text-teal-600', border: 'border-teal-200', hover: 'hover:bg-teal-100' },
};

export default function PortofolioIndex({
    pendidikan_count,
    penelitian_count,
    publikasi_count,
    pkm_count,
    penunjang_count,
    recent_pendidikan,
    recent_penelitian,
    recent_publikasi,
    recent_pkm,
    recent_penunjang,
    dosen_list,
    prodi_list,
    periode_list,
}: Props) {
    const params = new URLSearchParams(window.location.search);
    const [filters, setFilters] = useState({
        dosen_id: params.get('dosen_id') || '',
        prodi_id: params.get('prodi_id') || '',
        periode_id: params.get('periode_id') || '',
    });

    function applyFilter(key: string, value: string) {
        const next = { ...filters, [key]: value };
        setFilters(next);
        router.get(route('portofolio'), next, { preserveState: true, replace: true });
    }

    const counts = { pendidikan_count, penelitian_count, publikasi_count, pkm_count, penunjang_count };
    const recentAll: { id: number; title: string; type: string; is_verified: boolean }[] = [
        ...recent_pendidikan.map((r) => ({ ...r, type: 'Pendidikan' })),
        ...recent_penelitian.map((r) => ({ ...r, type: 'Penelitian' })),
        ...recent_publikasi.map((r) => ({ ...r, type: 'Publikasi' })),
        ...recent_pkm.map((r) => ({ ...r, type: 'PKM' })),
        ...recent_penunjang.map((r) => ({ ...r, type: 'Penunjang' })),
    ].sort(() => -1).slice(0, 10);

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Portofolio Tridharma</h2>}
        >
            <Head title="Portofolio Tridharma" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                        <div className="flex flex-wrap gap-4">
                            <div>
                                <label className="mb-1 block text-xs font-medium text-gray-600">Dosen</label>
                                <select
                                    value={filters.dosen_id}
                                    onChange={(e) => applyFilter('dosen_id', e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Dosen</option>
                                    {dosen_list.map((d) => (
                                        <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-medium text-gray-600">Prodi</label>
                                <select
                                    value={filters.prodi_id}
                                    onChange={(e) => applyFilter('prodi_id', e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-medium text-gray-600">Periode</label>
                                <select
                                    value={filters.periode_id}
                                    onChange={(e) => applyFilter('periode_id', e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        {statCards.map((card) => {
                            const c = colorMap[card.color];
                            return (
                                <Link
                                    key={card.route}
                                    href={route(card.route)}
                                    className={`rounded-lg border ${c.border} ${c.bg} ${c.hover} p-5 shadow-sm transition`}
                                >
                                    <div className="mb-3 flex items-center justify-center">
                                        <div className={`rounded-full p-3 ${c.text} bg-white shadow-sm`}>
                                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d={card.icon} />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className={`text-center text-3xl font-bold ${c.text}`}>{counts[card.countKey]}</div>
                                    <div className="mt-1 text-center text-sm font-medium text-gray-600">{card.label}</div>
                                </Link>
                            );
                        })}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-base font-semibold text-gray-800">Aktivitas Terbaru</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aktivitas</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kategori</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {recentAll.length === 0 ? (
                                        <tr>
                                            <td colSpan={3} className="px-6 py-12 text-center text-gray-500">Belum ada aktivitas</td>
                                        </tr>
                                    ) : (
                                        recentAll.map((item, i) => (
                                            <tr key={i} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.title}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{item.type}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${item.is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                                        {item.is_verified ? 'Terverifikasi' : 'Belum'}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
