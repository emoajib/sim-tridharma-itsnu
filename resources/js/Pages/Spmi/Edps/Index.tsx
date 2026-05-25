import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Edit3, Trash2, Eye, TrendingUp, TrendingDown, Minus } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface PeriodeItem {
    id: number;
    nama_periode: string;
}

interface StandarItem {
    id: number;
    kode_standar: string;
    nama_standar: string;
}

interface EdpsItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    standar_mutu_id: number;
    target: number | null;
    capaian: number | null;
    gap: number | null;
    analisis: string | null;
    bukti_file: string | null;
    status: string;
    created_at: string;
    prodi?: ProdiItem;
    periode?: PeriodeItem;
    standarMutu?: StandarItem;
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
    prodi_id: string;
    periode_id: string;
    status: string;
}

interface Props {
    edps: PaginatedData<EdpsItem>;
    prodi_list: ProdiItem[];
    periode_list: PeriodeItem[];
    standar_list: StandarItem[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function formatNumber(val: number | null): string {
    if (val === null || val === undefined) return '-';
    return val.toFixed(2);
}

function gapColor(val: number | null): string {
    if (val === null) return '';
    return val < 0 ? 'text-red-600 font-semibold' : val > 0 ? 'text-green-600 font-semibold' : 'text-gray-600';
}

function gapIcon(val: number | null) {
    if (val === null) return null;
    if (val < 0) return <TrendingDown className="h-3.5 w-3.5 text-red-500" />;
    if (val > 0) return <TrendingUp className="h-3.5 w-3.5 text-green-500" />;
    return <Minus className="h-3.5 w-3.5 text-gray-400" />;
}

const statusBadge: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    reviewed: 'bg-green-100 text-green-800',
};

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Index({ edps, prodi_list, periode_list, standar_list, filters, success }: Props) {
    // ── Filter state ──
    const [prodiFilter, setProdiFilter] = useState(filters.prodi_id || '');
    const [periodeFilter, setPeriodeFilter] = useState(filters.periode_id || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<EdpsItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<EdpsItem | null>(null);

    // ── Form ──
    const { data, setData, post, put, errors, processing, reset } = useForm({
        prodi_id: '',
        periode_id: '',
        standar_mutu_id: '',
        target: '',
        capaian: '',
        analisis: '',
        bukti_file: null as File | null,
        status: 'draft',
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.edps'),
                {
                    prodi_id: prodiFilter,
                    periode_id: periodeFilter,
                    status: statusFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [prodiFilter, periodeFilter, statusFilter]);

    // ── Modal handlers ──
    function openCreate() {
        reset();
        setEditing(null);
        setData('status', 'draft');
        setShowModal(true);
    }

    function openEdit(item: EdpsItem) {
        setEditing(item);
        setData({
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            standar_mutu_id: String(item.standar_mutu_id),
            target: item.target?.toString() || '',
            capaian: item.capaian?.toString() || '',
            analisis: item.analisis || '',
            bukti_file: null,
            status: item.status,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            post(route('spmi.edps.update', editing.id), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    setEditing(null);
                    reset();
                },
            });
        } else {
            post(route('spmi.edps.store'), {
                forceFormData: true,
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                },
            });
        }
    };

    function confirmDelete(item: EdpsItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.edps.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">EDPS — Evaluasi Diri Program Studi</h2>}
        >
            <Head title="EDPS" />

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
                        <span className="text-gray-700">EDPS</span>
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
                                    {/* Prodi Filter */}
                                    <select
                                        value={prodiFilter}
                                        onChange={(e) => setProdiFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Prodi</option>
                                        {prodi_list.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nama_prodi}
                                            </option>
                                        ))}
                                    </select>

                                    {/* Periode Filter */}
                                    <select
                                        value={periodeFilter}
                                        onChange={(e) => setPeriodeFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Periode</option>
                                        {periode_list.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nama_periode}
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
                                        <option value="submitted">Submitted</option>
                                        <option value="reviewed">Reviewed</option>
                                    </select>
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah EDPS
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Prodi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Periode
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Standar Mutu
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Target
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Capaian
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Gap
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
                                    {edps.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data EDPS.
                                            </td>
                                        </tr>
                                    ) : (
                                        edps.data.map((item) => {
                                            const gap = item.gap ?? (item.target !== null && item.capaian !== null ? item.capaian - item.target : null);
                                            return (
                                                <tr
                                                    key={item.id}
                                                    className={`hover:bg-gray-50 ${
                                                        gap !== null && gap < 0
                                                            ? 'bg-red-50'
                                                            : gap !== null && gap > 0
                                                              ? 'bg-green-50'
                                                              : ''
                                                    }`}
                                                >
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {item.prodi?.nama_prodi || '-'}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {item.periode?.nama_periode || '-'}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4">
                                                        {item.standarMutu ? (
                                                            <span className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">
                                                                {item.standarMutu.kode_standar}
                                                            </span>
                                                        ) : (
                                                            <span className="text-xs text-gray-400">-</span>
                                                        )}
                                                        {item.standarMutu && (
                                                            <span className="ml-1 text-xs text-gray-500">
                                                                {item.standarMutu.nama_standar}
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {formatNumber(item.target)}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {formatNumber(item.capaian)}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <div className="flex items-center gap-1">
                                                            {gapIcon(gap)}
                                                            <span className={gapColor(gap)}>
                                                                {gap !== null ? (gap >= 0 ? '+' : '') + gap.toFixed(2) : '-'}
                                                            </span>
                                                        </div>
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
                        {edps.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {edps.from} - {edps.to} dari {edps.total}
                                </div>
                                <div className="flex gap-1">
                                    {edps.links.map((link, i) => (
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
                                {editing ? 'Edit EDPS' : 'Tambah EDPS'}
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
                                {/* Prodi */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Program Studi</label>
                                    <select
                                        value={data.prodi_id}
                                        onChange={(e) => setData('prodi_id', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Pilih Prodi</option>
                                        {prodi_list.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nama_prodi}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.prodi_id && <p className="mt-1 text-xs text-red-600">{errors.prodi_id}</p>}
                                </div>

                                {/* Periode */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                    <select
                                        value={data.periode_id}
                                        onChange={(e) => setData('periode_id', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Pilih Periode</option>
                                        {periode_list.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nama_periode}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                                </div>
                            </div>

                            {/* Standar Mutu */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Standar Mutu</label>
                                <select
                                    value={data.standar_mutu_id}
                                    onChange={(e) => setData('standar_mutu_id', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Standar Mutu</option>
                                    {standar_list.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.kode_standar} — {s.nama_standar}
                                        </option>
                                    ))}
                                </select>
                                {errors.standar_mutu_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.standar_mutu_id}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                {/* Target */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Target</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value={data.target}
                                        onChange={(e) => setData('target', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="0.00"
                                    />
                                    {errors.target && <p className="mt-1 text-xs text-red-600">{errors.target}</p>}
                                </div>

                                {/* Capaian */}
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Capaian</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value={data.capaian}
                                        onChange={(e) => setData('capaian', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="0.00"
                                    />
                                    {errors.capaian && <p className="mt-1 text-xs text-red-600">{errors.capaian}</p>}
                                </div>
                            </div>

                            {/* Analisis */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Analisis</label>
                                <textarea
                                    value={data.analisis}
                                    onChange={(e) => setData('analisis', e.target.value)}
                                    rows={4}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Analisis kesenjangan antara target dan capaian..."
                                />
                                {errors.analisis && <p className="mt-1 text-xs text-red-600">{errors.analisis}</p>}
                            </div>

                            {/* File Upload */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Bukti</label>
                                <input
                                    type="file"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0] || null;
                                        setData('bukti_file', file);
                                    }}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {editing?.bukti_file && (
                                    <p className="mt-1 text-xs text-gray-500">File saat ini: {editing.bukti_file}</p>
                                )}
                                {errors.bukti_file && <p className="mt-1 text-xs text-red-600">{errors.bukti_file}</p>}
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
                                    <option value="submitted">Submitted</option>
                                    <option value="reviewed">Reviewed</option>
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
                            Yakin ingin menghapus data EDPS ini?
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
