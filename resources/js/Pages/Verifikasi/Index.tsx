import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface VerifikasiItem {
    id: number;
    prodi_id: number | null;
    dosen_id: number | null;
    doc_bukti_id: number | null;
    status: string;
    catatan: string;
    tingkat_kepercayaan: number;
    created_at: string;
    prodi?: { id: number; nama_prodi: string };
    dosen?: { id: number; nama_depan: string; nama_belakang: string };
    dokumen?: { id: number; nama_dokumen: string; file_path: string };
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Dosen {
    id: number;
    nama_depan: string;
    nama_belakang: string;
}

interface Stats {
    total: number;
    valid: number;
    need_review: number;
    invalid: number;
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
    verifikasi: PaginatedData<VerifikasiItem>;
    prodi_list: Prodi[];
    dosen_list: Dosen[];
    stats: Stats;
    filters: {
        prodi_id: number | null;
        dosen_id: number | null;
        status: string | null;
    };
}

export default function Index({ verifikasi, prodi_list, dosen_list, stats, filters }: Props) {
    const [showRunModal, setShowRunModal] = useState(false);
    const [selectedProdi, setSelectedProdi] = useState<number | ''>(filters.prodi_id || '');
    const [selectedDosen, setSelectedDosen] = useState<number | ''>(filters.dosen_id || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    useEffect(() => {
        router.get(route('verifikasi'), {
            prodi_id: selectedProdi || undefined,
            dosen_id: selectedDosen || undefined,
            status: selectedStatus || undefined,
        }, { preserveState: true, replace: true });
    }, [selectedProdi, selectedDosen, selectedStatus]);

    function runAgent() {
        router.post(route('verifikasi.run'), {
            prodi_id: selectedProdi || undefined,
            dosen_id: selectedDosen || undefined,
        }, {
            onSuccess: () => {
                setShowRunModal(false);
            },
        });
    }

    function getStatusColor(status: string) {
        switch (status) {
            case 'valid': return 'bg-green-100 text-green-800 border-green-200';
            case 'need_review': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'invalid': return 'bg-red-100 text-red-800 border-red-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }

    function getStatusBadge(status: string) {
        switch (status) {
            case 'valid': return 'bg-green-500';
            case 'need_review': return 'bg-yellow-500';
            case 'invalid': return 'bg-red-500';
            default: return 'bg-gray-500';
        }
    }

    function getStatusLabel(status: string) {
        switch (status) {
            case 'valid': return 'Valid';
            case 'need_review': return 'Perlu Tinjauan';
            case 'invalid': return 'Tidak Valid';
            default: return status;
        }
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Agent AI - Verifikasi Dokumen
                    </h2>
                    <button
                        onClick={() => setShowRunModal(true)}
                        className="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <svg className="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jalankan Agent
                    </button>
                </div>
            }
        >
            <Head title="Agent Verifikasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                            {flashSuccess}
                        </div>
                    )}

                    {/* Stats Cards */}
                    <div className="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-gray-900">{stats.total}</div>
                            <div className="text-sm text-gray-500">Total Dokumen</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-green-600">{stats.valid}</div>
                            <div className="text-sm text-gray-500">Valid</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-yellow-600">{stats.need_review}</div>
                            <div className="text-sm text-gray-500">Perlu Tinjauan</div>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-2xl font-bold text-red-600">{stats.invalid}</div>
                            <div className="text-sm text-gray-500">Tidak Valid</div>
                        </div>
                    </div>

                    {/* Filters */}
                    <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                        <div className="flex flex-wrap gap-4">
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
                                    value={selectedDosen}
                                    onChange={(e) => setSelectedDosen(e.target.value ? Number(e.target.value) : '')}
                                    className="rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="">Semua Dosen</option>
                                    {dosen_list.map((d) => (
                                        <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <select
                                    value={selectedStatus}
                                    onChange={(e) => setSelectedStatus(e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="valid">Valid</option>
                                    <option value="need_review">Perlu Tinjauan</option>
                                    <option value="invalid">Tidak Valid</option>
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
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Dokumen
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Prodi
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Dosen
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tingkat Kepercayaan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Catatan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Waktu
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {verifikasi.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-12 text-center text-gray-500">
                                            Tidak ada hasil verifikasi
                                        </td>
                                    </tr>
                                ) : (
                                    verifikasi.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border ${getStatusColor(item.status)}`}>
                                                    <span className={`mr-1.5 h-2 w-2 rounded-full ${getStatusBadge(item.status)}`}></span>
                                                    {getStatusLabel(item.status)}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900">
                                                    {item.dokumen?.nama_dokumen || `Dokumen #${item.doc_bukti_id}`}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {item.prodi?.nama_prodi || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {item.dosen ? `${item.dosen.nama_depan} ${item.dosen.nama_belakang}` : '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 h-2 w-16 rounded-full bg-gray-200">
                                                        <div
                                                            className="h-2 rounded-full bg-indigo-600"
                                                            style={{ width: `${item.tingkat_kepercayaan * 100}%` }}
                                                        ></div>
                                                    </div>
                                                    <span className="text-sm text-gray-600">{Math.round(item.tingkat_kepercayaan * 100)}%</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="max-w-xs truncate text-sm text-gray-500">
                                                    {item.catatan}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(item.created_at).toLocaleString('id-ID')}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {verifikasi.last_page > 1 && (
                        <div className="mt-4 flex justify-center">
                            <div className="flex gap-1">
                                {verifikasi.links.map((link, idx) => (
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
                        <h3 className="mb-4 text-lg font-semibold">Jalankan Agent Verifikasi</h3>
                        <div className="mb-4">
                            <label className="mb-1 block text-sm font-medium text-gray-700">Prodi</label>
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
                        <div className="mb-4">
                            <label className="mb-1 block text-sm font-medium text-gray-700">Dosen</label>
                            <select
                                value={selectedDosen}
                                onChange={(e) => setSelectedDosen(e.target.value ? Number(e.target.value) : '')}
                                className="w-full rounded-lg border-gray-300"
                            >
                                <option value="">Semua Dosen</option>
                                {dosen_list.map((d) => (
                                    <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
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