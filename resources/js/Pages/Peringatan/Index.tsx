import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface PeringatanItem {
    id: number;
    prodi_id: number | null;
    dosen_id: number | null;
    jenis_peringatan: string;
    tingkat: string;
    pesan: string;
    is_read: boolean;
    read_at: string | null;
    created_at: string;
    prodi?: { id: number; nama_prodi: string };
    dosen?: { id: number; nama_depan: string; nama_belakang: string };
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Stats {
    total: number;
    critical: number;
    warning: number;
    info: number;
    unread: number;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    peringatan: PaginatedData<PeringatanItem>;
    prodi_list: Prodi[];
    stats: Stats;
    filters: {
        prodi_id: number | null;
        tingkat: string | null;
        search: string | null;
    };
}

export default function Index({ peringatan, prodi_list, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [showRunModal, setShowRunModal] = useState(false);
    const [selectedProdi, setSelectedProdi] = useState<number | ''>(filters.prodi_id || '');
    const [selectedTingkat, setSelectedTingkat] = useState(filters.tingkat || '');
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('peringatan'), {
                search: search || undefined,
                prodi_id: selectedProdi || undefined,
                tingkat: selectedTingkat || undefined,
            }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, selectedProdi, selectedTingkat]);

    function runAgent() {
        router.post(route('peringatan.run'), {
            prodi_id: selectedProdi || undefined,
        }, {
            onSuccess: () => {
                setShowRunModal(false);
            },
        });
    }

    function markAsRead(id: number) {
        router.post(route('peringatan.markRead', id));
    }

    function markAllAsRead() {
        router.post(route('peringatan.markAllRead'));
    }

    function getTingkatColor(tingkat: string) {
        switch (tingkat) {
            case 'critical': return 'bg-red-100 text-red-800 border-red-200';
            case 'warning': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'info': return 'bg-blue-100 text-blue-800 border-blue-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }

    function getTingkatBadge(tingkat: string) {
        switch (tingkat) {
            case 'critical': return 'bg-red-500';
            case 'warning': return 'bg-yellow-500';
            case 'info': return 'bg-blue-500';
            default: return 'bg-gray-500';
        }
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Agent AI - Peringatan Dini
                    </h2>
                    <button
                        onClick={() => setShowRunModal(true)}
                        className="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <svg className="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jalankan Agent
                    </button>
                </div>
            }
        >
            <Head title="Agent Peringatan" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                            {flashSuccess}
                        </div>
                    )}

                    {/* Stats Cards */}
                    <div className="mb-6 grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-gray-900">{stats.total}</div>
                            <div className="text-sm text-gray-500">Total</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-red-600">{stats.critical}</div>
                            <div className="text-sm text-gray-500">Critical</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-yellow-600">{stats.warning}</div>
                            <div className="text-sm text-gray-500">Warning</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-blue-600">{stats.info}</div>
                            <div className="text-sm text-gray-500">Info</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-indigo-600">{stats.unread}</div>
                            <div className="text-sm text-gray-500">Belum Dibaca</div>
                        </div>
                    </div>

                    {/* Mark All Read Button */}
                    {stats.unread > 0 && (
                        <div className="mb-4">
                            <button
                                onClick={markAllAsRead}
                                className="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                Tandai semua sudah dibaca ({stats.unread})
                            </button>
                        </div>
                    )}

                    {/* Filters */}
                    <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                        <div className="flex flex-wrap gap-4">
                            <div className="flex-1 min-w-[200px]">
                                <input
                                    type="text"
                                    placeholder="Cari peringatan..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm"
                                />
                            </div>
                            <div>
                                <select
                                    value={selectedProdi}
                                    onChange={(e) => setSelectedProdi(e.target.value ? Number(e.target.value) : '')}
                                    className="rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="">Semua Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <select
                                    value={selectedTingkat}
                                    onChange={(e) => setSelectedTingkat(e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="">Semua Tingkat</option>
                                    <option value="critical">Critical</option>
                                    <option value="warning">Warning</option>
                                    <option value="info">Info</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tingkat
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Pesan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Prodi
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Waktu
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {peringatan.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-gray-500">
                                            Tidak ada peringatan
                                        </td>
                                    </tr>
                                ) : (
                                    peringatan.data.map((item) => (
                                        <tr key={item.id} className={`hover:bg-gray-50 ${!item.is_read ? 'bg-blue-50' : ''}`}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border ${getTingkatColor(item.tingkat)}`}>
                                                    <span className={`mr-1.5 h-2 w-2 rounded-full ${getTingkatBadge(item.tingkat)}`}></span>
                                                    {item.tingkat.toUpperCase()}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900">{item.pesan}</div>
                                                <div className="text-xs text-gray-500">{item.jenis_peringatan}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {item.prodi?.nama_prodi || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {item.is_read ? (
                                                    <span className="text-sm text-green-600">Sudah dibaca</span>
                                                ) : (
                                                    <span className="text-sm text-blue-600">Belum dibaca</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(item.created_at).toLocaleString('id-ID')}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                {!item.is_read && (
                                                    <button
                                                        onClick={() => markAsRead(item.id)}
                                                        className="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Tandai
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {peringatan.last_page > 1 && (
                        <div className="mt-4 flex justify-center">
                            <div className="flex gap-1">
                                {peringatan.links.map((link, idx) => (
                                    <button
                                        key={idx}
                                        onClick={() => link.url && router.get(link.url)}
                                        disabled={!link.url}
                                        className={`px-3 py-1 text-sm ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-white text-gray-700 hover:bg-gray-50'
                                                : 'bg-gray-100 text-gray-400'
                                        } rounded border`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Run Agent Modal */}
            {showRunModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="w-full max-w-md rounded-lg bg-white p-6">
                        <h3 className="mb-4 text-lg font-semibold">Jalankan Agent Peringatan</h3>
                        <div className="mb-4">
                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                Filter Prodi (Opsional)
                            </label>
                            <select
                                value={selectedProdi}
                                onChange={(e) => setSelectedProdi(e.target.value ? Number(e.target.value) : '')}
                                className="w-full rounded-lg border-gray-300"
                            >
                                <option value="">Semua Prodi</option>
                                {prodi_list.map((p) => (
                                    <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setShowRunModal(false)}
                                className="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={runAgent}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Jalankan
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}