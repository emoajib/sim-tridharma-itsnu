import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface AuditItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    judul_audit: string;
    tanggal_audit: string;
    auditor: string | null;
    temuan: string | null;
    rekomendasi: string | null;
    tindak_lanjut: string | null;
    status: string | null;
    prodi?: { nama_prodi: string };
    periode?: { nama_periode: string };
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
    audit: PaginatedData<AuditItem>;
    prodi_list: Prodi[];
    periode_list: Periode[];
    success?: string;
    errors?: Record<string, string>;
}

function dampakBadge(value: string | null) {
    const map: Record<string, string> = {
        rendah: 'bg-green-100 text-green-800',
        sedang: 'bg-yellow-100 text-yellow-800',
        tinggi: 'bg-red-100 text-red-800',
    };
    const v = value || '';
    return (
        <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${map[v] || 'bg-gray-100 text-gray-800'}`}>
            {v ? v.charAt(0).toUpperCase() + v.slice(1) : '-'}
        </span>
    );
}

export default function Index({ audit, prodi_list, periode_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [statusFilter, setStatusFilter] = useState(() => {
        return new URLSearchParams(window.location.search).get('status') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<AuditItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<AuditItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        prodi_id: '',
        periode_id: '',
        judul_audit: '',
        tanggal_audit: '',
        auditor: '',
        temuan: '',
        rekomendasi: '',
        tindak_lanjut: '',
        status: 'open',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('spmi.audit'), { search, status: statusFilter }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, statusFilter]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: AuditItem) {
        setEditing(item);
        setData({
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            judul_audit: item.judul_audit,
            tanggal_audit: item.tanggal_audit,
            auditor: item.auditor || '',
            temuan: item.temuan || '',
            rekomendasi: item.rekomendasi || '',
            tindak_lanjut: item.tindak_lanjut || '',
            status: item.status || 'open',
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('spmi.audit.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('spmi.audit.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: AuditItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('spmi.audit.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const statusBadge: Record<string, string> = {
        open: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        closed: 'bg-green-100 text-green-800',
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Audit Mutu</h2>}
        >
            <Head title="Audit Mutu" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-indigo-600 hover:text-indigo-900">SPMI</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Audit</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Dashboard</Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <input
                                        type="text"
                                        placeholder="Cari judul atau auditor..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => setStatusFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Audit
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Judul Audit</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Auditor</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {audit.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        audit.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.judul_audit}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.prodi?.nama_prodi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tanggal_audit}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.auditor || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[item.status || ''] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.status ? item.status.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : '-'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {audit.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {audit.from} - {audit.to} dari {audit.total}
                                </div>
                                <div className="flex gap-1">
                                    {audit.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Audit Mutu' : 'Tambah Audit Mutu'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Prodi</label>
                                <select value={data.prodi_id} onChange={(e) => setData('prodi_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                                {errors.prodi_id && <p className="mt-1 text-xs text-red-600">{errors.prodi_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                <select value={data.periode_id} onChange={(e) => setData('periode_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                                {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Judul Audit</label>
                                <input type="text" value={data.judul_audit} onChange={(e) => setData('judul_audit', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.judul_audit && <p className="mt-1 text-xs text-red-600">{errors.judul_audit}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Audit</label>
                                <input type="date" value={data.tanggal_audit} onChange={(e) => setData('tanggal_audit', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.tanggal_audit && <p className="mt-1 text-xs text-red-600">{errors.tanggal_audit}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Auditor</label>
                                <input type="text" value={data.auditor} onChange={(e) => setData('auditor', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.auditor && <p className="mt-1 text-xs text-red-600">{errors.auditor}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Temuan</label>
                                <textarea value={data.temuan} onChange={(e) => setData('temuan', e.target.value)} rows={3} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.temuan && <p className="mt-1 text-xs text-red-600">{errors.temuan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Rekomendasi</label>
                                <textarea value={data.rekomendasi} onChange={(e) => setData('rekomendasi', e.target.value)} rows={3} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.rekomendasi && <p className="mt-1 text-xs text-red-600">{errors.rekomendasi}</p>}
                            </div>
                            {editing && (
                                <>
                                    <div className="mb-4">
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Tindak Lanjut</label>
                                        <textarea value={data.tindak_lanjut} onChange={(e) => setData('tindak_lanjut', e.target.value)} rows={3} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        {errors.tindak_lanjut && <p className="mt-1 text-xs text-red-600">{errors.tindak_lanjut}</p>}
                                    </div>
                                    <div className="mb-4">
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Status</label>
                                        <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="open">Open</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                        {errors.status && <p className="mt-1 text-xs text-red-600">{errors.status}</p>}
                                    </div>
                                </>
                            )}
                            <div className="flex justify-end gap-2">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700 disabled:opacity-50">
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus audit <strong>{deleteTarget.judul_audit}</strong>?</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:opacity-50">
                                {processing ? 'Menghapus...' : 'Hapus'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
