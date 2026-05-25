import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Edit3, Trash2, Download, AlertTriangle, Clock, FileText } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface DokumenItem {
    id: number;
    kategori: string;
    nomor_dokumen: string;
    judul: string;
    deskripsi: string | null;
    file: string | null;
    versi: string;
    tanggal_berlaku: string | null;
    tanggal_kadaluarsa: string | null;
    status: string;
    created_at: string;
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
    kategori: string;
    status: string;
}

interface Props {
    dokumen: PaginatedData<DokumenItem>;
    kategori_list: string[];
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
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return date;
    }
}

function isExpired(date: string | null): boolean {
    if (!date) return false;
    return new Date(date) < new Date();
}

function isExpiringSoon(date: string | null): boolean {
    if (!date) return false;
    const diff = new Date(date).getTime() - new Date().getTime();
    const diffDays = Math.ceil(diff / (1000 * 60 * 60 * 24));
    return diffDays >= 0 && diffDays <= 30;
}

const kategoriColors: Record<string, string> = {
    pedoman: 'bg-blue-100 text-blue-800',
    manual: 'bg-indigo-100 text-indigo-800',
    prosedur: 'bg-purple-100 text-purple-800',
    formulir: 'bg-teal-100 text-teal-800',
    panduan: 'bg-green-100 text-green-800',
    kebijakan: 'bg-orange-100 text-orange-800',
    lainnya: 'bg-gray-100 text-gray-800',
};

const statusBadge: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    archived: 'bg-gray-100 text-gray-800',
};

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Index({ dokumen, kategori_list, filters, success }: Props) {
    // ── Filter state ──
    const [search, setSearch] = useState(filters.search || '');
    const [kategoriFilter, setKategoriFilter] = useState(filters.kategori || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<DokumenItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<DokumenItem | null>(null);

    // ── Form ──
    const { data, setData, post, put, errors, processing, reset } = useForm({
        kategori: '',
        nomor_dokumen: '',
        judul: '',
        deskripsi: '',
        file: null as File | null,
        tanggal_berlaku: '',
        tanggal_kadaluarsa: '',
        status: 'draft',
        versi: '1.0',
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.dokumen-mutu'),
                {
                    search,
                    kategori: kategoriFilter,
                    status: statusFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search, kategoriFilter, statusFilter]);

    // ── Modal handlers ──
    function openCreate() {
        reset();
        setEditing(null);
        setData('status', 'draft');
        setData('versi', '1.0');
        setShowModal(true);
    }

    function openEdit(item: DokumenItem) {
        setEditing(item);
        setData({
            kategori: item.kategori,
            nomor_dokumen: item.nomor_dokumen,
            judul: item.judul,
            deskripsi: item.deskripsi || '',
            file: null,
            tanggal_berlaku: item.tanggal_berlaku || '',
            tanggal_kadaluarsa: item.tanggal_kadaluarsa || '',
            status: item.status,
            versi: item.versi,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            post(route('spmi.dokumen-mutu.update', editing.id), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    setEditing(null);
                    reset();
                },
            });
        } else {
            post(route('spmi.dokumen-mutu.store'), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                },
            });
        }
    };

    function confirmDelete(item: DokumenItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.dokumen-mutu.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dokumen Mutu</h2>}
        >
            <Head title="Dokumen Mutu" />

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
                        <span className="text-gray-700">Dokumen Mutu</span>
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
                                <div className="flex flex-wrap items-center gap-3">
                                    {/* Search */}
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                        <input
                                            type="text"
                                            placeholder="Cari nomor atau judul dokumen..."
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="w-56 rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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

                                    {/* Kategori Filter */}
                                    <select
                                        value={kategoriFilter}
                                        onChange={(e) => setKategoriFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Kategori</option>
                                        {kategori_list.map((k) => (
                                            <option key={k} value={k}>
                                                {k}
                                            </option>
                                        ))}
                                    </select>

                                    {/* Status Filter */}
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => setStatusFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="review">Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="expired">Expired</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah Dokumen
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Nomor Dokumen
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Judul
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Kategori
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Versi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal Berlaku
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal Kadaluarsa
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            File
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {dokumen.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={9} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data dokumen mutu.
                                            </td>
                                        </tr>
                                    ) : (
                                        dokumen.data.map((item) => {
                                            const expired = isExpired(item.tanggal_kadaluarsa) && item.status !== 'archived';
                                            const expiringSoon = isExpiringSoon(item.tanggal_kadaluarsa) && !expired;
                                            return (
                                                <tr
                                                    key={item.id}
                                                    className={`hover:bg-gray-50 transition-colors ${
                                                        expired ? 'bg-red-50' : expiringSoon ? 'bg-yellow-50' : ''
                                                    }`}
                                                >
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                        {item.nomor_dokumen}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        <div className="flex items-center gap-1.5">
                                                            <FileText className="h-3.5 w-3.5 text-gray-400" />
                                                            {item.judul}
                                                        </div>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4">
                                                        <span
                                                            className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                                kategoriColors[item.kategori] || 'bg-gray-100 text-gray-800'
                                                            }`}
                                                        >
                                                            {item.kategori}
                                                        </span>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4">
                                                        <span className="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                                            v{item.versi}
                                                        </span>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {formatDate(item.tanggal_berlaku)}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {formatDate(item.tanggal_kadaluarsa)}
                                                        {expired && (
                                                            <span className="ml-1 inline-flex items-center text-red-500">
                                                                <AlertTriangle className="h-3 w-3" />
                                                            </span>
                                                        )}
                                                        {expiringSoon && (
                                                            <span className="ml-1 inline-flex items-center text-yellow-500">
                                                                <Clock className="h-3 w-3" />
                                                            </span>
                                                        )}
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
                                                        {item.file ? (
                                                            <a
                                                                href={`/storage/${item.file}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="inline-flex items-center gap-1 rounded p-1.5 text-indigo-600 hover:bg-indigo-50"
                                                                title="Download"
                                                            >
                                                                <Download className="h-4 w-4" />
                                                            </a>
                                                        ) : (
                                                            <span className="text-xs text-gray-400">-</span>
                                                        )}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <div className="flex items-center gap-1">
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
                                            );
                                        })
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {dokumen.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {dokumen.from} - {dokumen.to} dari {dokumen.total}
                                </div>
                                <div className="flex gap-1">
                                    {dokumen.links.map((link, i) => (
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
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {editing ? 'Edit Dokumen Mutu' : 'Tambah Dokumen Mutu'}
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
                            <div className="grid grid-cols-2 gap-4">
                                {/* Kategori */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                                    <select
                                        value={data.kategori}
                                        onChange={(e) => setData('kategori', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Pilih Kategori</option>
                                        {kategori_list.map((k) => (
                                            <option key={k} value={k}>
                                                {k}
                                            </option>
                                        ))}
                                        <option value="pedoman">Pedoman</option>
                                        <option value="manual">Manual</option>
                                        <option value="prosedur">Prosedur</option>
                                        <option value="formulir">Formulir</option>
                                        <option value="panduan">Panduan</option>
                                        <option value="kebijakan">Kebijakan</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                    {errors.kategori && <p className="mt-1 text-xs text-red-600">{errors.kategori}</p>}
                                </div>

                                {/* Nomor Dokumen */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Nomor Dokumen</label>
                                    <input
                                        type="text"
                                        value={data.nomor_dokumen}
                                        onChange={(e) => setData('nomor_dokumen', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Contoh: SOP-001"
                                    />
                                    {errors.nomor_dokumen && (
                                        <p className="mt-1 text-xs text-red-600">{errors.nomor_dokumen}</p>
                                    )}
                                </div>
                            </div>

                            {/* Judul */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Judul Dokumen</label>
                                <input
                                    type="text"
                                    value={data.judul}
                                    onChange={(e) => setData('judul', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Judul dokumen mutu"
                                />
                                {errors.judul && <p className="mt-1 text-xs text-red-600">{errors.judul}</p>}
                            </div>

                            {/* Deskripsi */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea
                                    value={data.deskripsi}
                                    onChange={(e) => setData('deskripsi', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Deskripsi dokumen..."
                                />
                                {errors.deskripsi && <p className="mt-1 text-xs text-red-600">{errors.deskripsi}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                {/* Tanggal Berlaku */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Berlaku</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_berlaku}
                                        onChange={(e) => setData('tanggal_berlaku', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_berlaku && (
                                        <p className="mt-1 text-xs text-red-600">{errors.tanggal_berlaku}</p>
                                    )}
                                </div>

                                {/* Tanggal Kadaluarsa */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Kadaluarsa</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_kadaluarsa}
                                        onChange={(e) => setData('tanggal_kadaluarsa', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_kadaluarsa && (
                                        <p className="mt-1 text-xs text-red-600">{errors.tanggal_kadaluarsa}</p>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                {/* Versi */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Versi</label>
                                    <input
                                        type="text"
                                        value={data.versi}
                                        onChange={(e) => setData('versi', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="1.0"
                                    />
                                    {errors.versi && <p className="mt-1 text-xs text-red-600">{errors.versi}</p>}
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
                                        <option value="review">Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="expired">Expired</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    {errors.status && <p className="mt-1 text-xs text-red-600">{errors.status}</p>}
                                </div>
                            </div>

                            {/* File Upload */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Dokumen</label>
                                <input
                                    type="file"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0] || null;
                                        setData('file', file);
                                    }}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {editing?.file && (
                                    <p className="mt-1 text-xs text-gray-500">File saat ini: {editing.file}</p>
                                )}
                                {errors.file && <p className="mt-1 text-xs text-red-600">{errors.file}</p>}
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
                            Yakin ingin menghapus dokumen <strong>{deleteTarget.nomor_dokumen} - {deleteTarget.judul}</strong>?
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
