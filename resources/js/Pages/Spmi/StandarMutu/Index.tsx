import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Edit3, Trash2, AlertTriangle, CheckCircle, XCircle } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface StandarItem {
    id: number;
    kategori: string;
    kode_standar: string;
    nama_standar: string;
    deskripsi: string | null;
    sumber: string | null;
    referensi_regulasi: string | null;
    target_nilai: string | null;
    is_active: boolean;
    created_at: string;
    audit_mutus_count?: number;
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
    is_active: string;
}

interface Props {
    standar_mutu: PaginatedData<StandarItem>;
    kategori_list: string[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ standar_mutu, kategori_list, filters, success, errors: pageErrors }: Props) {
    // ── Filter state ──
    const [search, setSearch] = useState(filters.search || '');
    const [kategoriFilter, setKategoriFilter] = useState(filters.kategori || '');
    const [activeFilter, setActiveFilter] = useState(filters.is_active || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<StandarItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<StandarItem | null>(null);
    const [deleteError, setDeleteError] = useState('');

    // ── Form ──
    const { data, setData, post, put, errors, processing, reset } = useForm({
        kategori: '',
        kode_standar: '',
        nama_standar: '',
        deskripsi: '',
        sumber: '',
        referensi_regulasi: '',
        target_nilai: '',
        is_active: true,
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.standar-mutu'),
                {
                    search,
                    kategori: kategoriFilter,
                    is_active: activeFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search, kategoriFilter, activeFilter]);

    // ── Open create modal ──
    function openCreate() {
        reset();
        setEditing(null);
        setData('is_active', true);
        setShowModal(true);
    }

    // ── Open edit modal ──
    function openEdit(item: StandarItem) {
        setEditing(item);
        setData({
            kategori: item.kategori,
            kode_standar: item.kode_standar,
            nama_standar: item.nama_standar,
            deskripsi: item.deskripsi || '',
            sumber: item.sumber || '',
            referensi_regulasi: item.referensi_regulasi || '',
            target_nilai: item.target_nilai?.toString() || '',
            is_active: item.is_active,
        });
        setShowModal(true);
    }

    // ── Submit ──
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('spmi.standar-mutu.update', editing.id), {
                onSuccess: () => {
                    setShowModal(false);
                    setEditing(null);
                    reset();
                },
            });
        } else {
            post(route('spmi.standar-mutu.store'), {
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                },
            });
        }
    };

    // ── Delete ──
    function confirmDelete(item: StandarItem) {
        setDeleteTarget(item);
        setDeleteError('');
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.standar-mutu.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
            onError: (err) => {
                const msg = err?.standar_mutu || 'Gagal menghapus standar mutu.';
                setDeleteError(msg);
            },
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">Standar Mutu</h2>
            }
        >
            <Head title="Standar Mutu" />

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
                        <span className="text-gray-700">Standar Mutu</span>
                    </nav>

                    <div className="mb-4">
                        <Link
                            href={route('spmi.dashboard')}
                            className="text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            &larr; Kembali ke Dashboard SPMI
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                            {success}
                        </div>
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
                                            placeholder="Cari kode atau nama standar..."
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

                                    {/* Active Filter */}
                                    <select
                                        value={activeFilter}
                                        onChange={(e) => setActiveFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah Standar
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Kode Standar
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Nama Standar
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Kategori
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Sumber
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Target Nilai
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
                                    {standar_mutu.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data standar mutu.
                                            </td>
                                        </tr>
                                    ) : (
                                        standar_mutu.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800">
                                                        {item.kode_standar}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                    {item.nama_standar}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                                        {item.kategori}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.sumber || '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.target_nilai !== null ? `${item.target_nilai}%` : '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    {item.is_active ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                                            <CheckCircle className="h-3 w-3" />
                                                            Aktif
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                                                            <XCircle className="h-3 w-3" />
                                                            Tidak Aktif
                                                        </span>
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
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {standar_mutu.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {standar_mutu.from} - {standar_mutu.to} dari {standar_mutu.total}
                                </div>
                                <div className="flex gap-1">
                                    {standar_mutu.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url)
                                                    router.get(link.url, {}, { preserveState: true, replace: true });
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
                                {editing ? 'Edit Standar Mutu' : 'Tambah Standar Mutu'}
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

                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                {/* Kategori */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Kategori
                                    </label>
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
                                        <option value="Pendidikan">Pendidikan</option>
                                        <option value="Penelitian">Penelitian</option>
                                        <option value="PKM">PKM</option>
                                        <option value="Tambahan">Tambahan</option>
                                    </select>
                                    {errors.kategori && (
                                        <p className="mt-1 text-xs text-red-600">{errors.kategori}</p>
                                    )}
                                </div>

                                {/* Kode Standar */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Kode Standar
                                    </label>
                                    <input
                                        type="text"
                                        value={data.kode_standar}
                                        onChange={(e) => setData('kode_standar', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Contoh: SN-01"
                                    />
                                    {errors.kode_standar && (
                                        <p className="mt-1 text-xs text-red-600">{errors.kode_standar}</p>
                                    )}
                                </div>
                            </div>

                            {/* Nama Standar */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">
                                    Nama Standar
                                </label>
                                <input
                                    type="text"
                                    value={data.nama_standar}
                                    onChange={(e) => setData('nama_standar', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Nama lengkap standar mutu"
                                />
                                {errors.nama_standar && (
                                    <p className="mt-1 text-xs text-red-600">{errors.nama_standar}</p>
                                )}
                            </div>

                            {/* Deskripsi */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">
                                    Deskripsi
                                </label>
                                <textarea
                                    value={data.deskripsi}
                                    onChange={(e) => setData('deskripsi', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Deskripsi standar mutu"
                                />
                                {errors.deskripsi && (
                                    <p className="mt-1 text-xs text-red-600">{errors.deskripsi}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                {/* Sumber */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Sumber
                                    </label>
                                    <input
                                        type="text"
                                        value={data.sumber}
                                        onChange={(e) => setData('sumber', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Sumber referensi"
                                    />
                                    {errors.sumber && (
                                        <p className="mt-1 text-xs text-red-600">{errors.sumber}</p>
                                    )}
                                </div>

                                {/* Target Nilai */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Target Nilai (%)
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        value={data.target_nilai}
                                        onChange={(e) => setData('target_nilai', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="0-100"
                                    />
                                    {errors.target_nilai && (
                                        <p className="mt-1 text-xs text-red-600">{errors.target_nilai}</p>
                                    )}
                                </div>
                            </div>

                            {/* Referensi Regulasi */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">
                                    Referensi Regulasi
                                </label>
                                <input
                                    type="text"
                                    value={data.referensi_regulasi}
                                    onChange={(e) => setData('referensi_regulasi', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Dasar hukum atau regulasi"
                                />
                                {errors.referensi_regulasi && (
                                    <p className="mt-1 text-xs text-red-600">{errors.referensi_regulasi}</p>
                                )}
                            </div>

                            {/* Is Active */}
                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    checked={data.is_active as boolean}
                                    onChange={(e) => setData('is_active', e.target.checked)}
                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <label htmlFor="is_active" className="text-sm font-medium text-gray-700">
                                    Standar aktif
                                </label>
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
                        {deleteError ? (
                            <div className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                                <div className="flex items-start gap-2">
                                    <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
                                    <span>{deleteError}</span>
                                </div>
                            </div>
                        ) : (
                            <p className="mb-4 text-sm text-gray-600">
                                Yakin ingin menghapus standar mutu{' '}
                                <strong>
                                    {deleteTarget.kode_standar} - {deleteTarget.nama_standar}
                                </strong>
                                ?
                            </p>
                        )}
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => {
                                    setDeleteTarget(null);
                                    setDeleteError('');
                                }}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                {deleteError ? 'Tutup' : 'Batal'}
                            </button>
                            {!deleteError && (
                                <button
                                    onClick={executeDelete}
                                    className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                                >
                                    Hapus
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
