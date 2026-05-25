import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Edit3, Trash2, Eye, Calendar, Users, FileText } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface UserItem {
    id: number;
    name: string;
}

interface RtmItem {
    id: number;
    judul: string;
    tanggal_rapat: string | null;
    dipimpin_oleh_id: number | null;
    agenda: string | null;
    notulen: string | null;
    file_notulen: string | null;
    status: string;
    action_items_count: number;
    created_at: string;
    pimpinan?: UserItem;
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

interface Filters {
    search: string;
}

interface Props {
    rtm: PaginatedData<RtmItem>;
    user_list: UserItem[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(date: string | null): string {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return date;
    }
}

const statusBadge: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    conducted: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Index({ rtm, user_list, filters, success }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<RtmItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<RtmItem | null>(null);

    // ── Form ──
    const { data, setData, post, put, errors, processing, reset } = useForm({
        judul: '',
        tanggal_rapat: '',
        dipimpin_oleh_id: '',
        agenda: '',
        notulen: '',
        file_notulen: null as File | null,
        status: 'draft',
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.rtm'),
                { search },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    // ── Modal handlers ──
    function openCreate() {
        reset();
        setEditing(null);
        setData('status', 'draft');
        setShowModal(true);
    }

    function openEdit(item: RtmItem) {
        setEditing(item);
        setData({
            judul: item.judul,
            tanggal_rapat: item.tanggal_rapat || '',
            dipimpin_oleh_id: item.dipimpin_oleh_id?.toString() || '',
            agenda: item.agenda || '',
            notulen: item.notulen || '',
            file_notulen: null,
            status: item.status,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            post(route('spmi.rtm.update', editing.id), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    setEditing(null);
                    reset();
                },
            });
        } else {
            post(route('spmi.rtm.store'), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                },
            });
        }
    };

    function confirmDelete(item: RtmItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.rtm.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Rapat Tinjauan Manajemen (RTM)</h2>}
        >
            <Head title="RTM" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                            Dashboard
                        </Link>
                        <span className="mx-2">/</span>
                        <span className="text-indigo-600 hover:text-indigo-900">SPMI</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">RTM</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('spmi.dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">
                            &larr; Kembali ke Dashboard SPMI
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    {/* Main Card */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {/* Filter Bar */}
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        type="text"
                                        placeholder="Cari judul rapat..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-64 rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {search && (
                                        <button
                                            onClick={() => setSearch('')}
                                            className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <X className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah RTM
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Judul Rapat
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Pimpinan Rapat
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Action Items
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {rtm.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data RTM.
                                            </td>
                                        </tr>
                                    ) : (
                                        rtm.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <Link
                                                        href={route('spmi.rtm.show', item.id)}
                                                        className="font-medium text-indigo-600 hover:text-indigo-900 hover:underline"
                                                    >
                                                        {item.judul}
                                                    </Link>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    <div className="flex items-center gap-1.5">
                                                        <Calendar className="h-3.5 w-3.5 text-gray-400" />
                                                        {formatDate(item.tanggal_rapat)}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    <div className="flex items-center gap-1.5">
                                                        <Users className="h-3.5 w-3.5 text-gray-400" />
                                                        {item.pimpinan?.name || '-'}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">
                                                        <FileText className="h-3 w-3" />
                                                        {item.action_items_count}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                            statusBadge[item.status] || 'bg-gray-100 text-gray-800'
                                                        }`}
                                                    >
                                                        {item.status
                                                            ? item.status.charAt(0).toUpperCase() + item.status.slice(1)
                                                            : '-'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <div className="flex items-center gap-1">
                                                        <Link
                                                            href={route('spmi.rtm.show', item.id)}
                                                            className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600"
                                                            title="Lihat Detail"
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
                                                        <button
                                                            onClick={() => openEdit(item)}
                                                            className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600"
                                                            title="Edit"
                                                        >
                                                            <Edit3 className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            onClick={() => confirmDelete(item)}
                                                            className="rounded p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600"
                                                            title="Hapus"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {rtm.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {rtm.from} - {rtm.to} dari {rtm.total}
                                </div>
                                <div className="flex gap-1">
                                    {rtm.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm ${
                                                link.active
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-white text-gray-700 hover:bg-gray-100'
                                            } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* ─── Create/Edit Modal ─── */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {editing ? 'Edit RTM' : 'Tambah RTM'}
                            </h3>
                            <button
                                onClick={() => {
                                    setShowModal(false);
                                    setEditing(null);
                                    reset();
                                }}
                                className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submit} className="max-h-[70vh] overflow-y-auto space-y-4 pr-2">
                            {/* Judul */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Judul Rapat</label>
                                <input
                                    type="text"
                                    value={data.judul}
                                    onChange={(e) => setData('judul', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Masukkan judul rapat"
                                />
                                {errors.judul && <p className="mt-1 text-xs text-red-600">{errors.judul}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                {/* Tanggal Rapat */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Rapat</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_rapat}
                                        onChange={(e) => setData('tanggal_rapat', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_rapat && (
                                        <p className="mt-1 text-xs text-red-600">{errors.tanggal_rapat}</p>
                                    )}
                                </div>

                                {/* Pimpinan Rapat */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Pimpinan Rapat</label>
                                    <select
                                        value={data.dipimpin_oleh_id}
                                        onChange={(e) => setData('dipimpin_oleh_id', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Pilih Pimpinan</option>
                                        {user_list.map((u) => (
                                            <option key={u.id} value={u.id}>
                                                {u.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.dipimpin_oleh_id && (
                                        <p className="mt-1 text-xs text-red-600">{errors.dipimpin_oleh_id}</p>
                                    )}
                                </div>
                            </div>

                            {/* Agenda */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Agenda</label>
                                <textarea
                                    value={data.agenda}
                                    onChange={(e) => setData('agenda', e.target.value)}
                                    rows={5}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Agenda rapat..."
                                />
                                {errors.agenda && <p className="mt-1 text-xs text-red-600">{errors.agenda}</p>}
                            </div>

                            {/* Notulen */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Notulen</label>
                                <textarea
                                    value={data.notulen}
                                    onChange={(e) => setData('notulen', e.target.value)}
                                    rows={5}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Hasil notulen rapat..."
                                />
                                {errors.notulen && <p className="mt-1 text-xs text-red-600">{errors.notulen}</p>}
                            </div>

                            {/* File Notulen */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Notulen</label>
                                <input
                                    type="file"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0] || null;
                                        setData('file_notulen', file);
                                    }}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {editing?.file_notulen && (
                                    <p className="mt-1 text-xs text-gray-500">
                                        File saat ini: {editing.file_notulen}
                                    </p>
                                )}
                                {errors.file_notulen && (
                                    <p className="mt-1 text-xs text-red-600">{errors.file_notulen}</p>
                                )}
                            </div>

                            {/* Status */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Status</label>
                                <select
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="conducted">Terlaksana</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                                {errors.status && <p className="mt-1 text-xs text-red-600">{errors.status}</p>}
                            </div>

                            <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowModal(false);
                                        setEditing(null);
                                        reset();
                                    }}
                                    className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* ─── Delete Confirmation ─── */}
            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">
                            Yakin ingin menghapus rapat <strong>{deleteTarget.judul}</strong>?
                        </p>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setDeleteTarget(null)}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={executeDelete}
                                className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
