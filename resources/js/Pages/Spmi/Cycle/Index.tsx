import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Edit3, Trash2, CheckCircle, Circle, ArrowRight } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface SpmiCycleItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    instrumen_id: number | null;
    nama_siklus: string;
    tahap: string;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    progress: number;
    status: string;
    created_at: string;
    prodi?: ProdiItem;
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
    prodi_id: string;
    tahap: string;
}

interface Props {
    cycles: PaginatedData<SpmiCycleItem>;
    prodi_list: ProdiItem[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

// ─── Constants ─────────────────────────────────────────────────────────────────

const PPEPP_STEPS = [
    { key: 'P', label: 'Penetapan', color: 'bg-blue-500' },
    { key: 'P', label: 'Pelaksanaan', color: 'bg-indigo-500' },
    { key: 'E', label: 'Evaluasi', color: 'bg-yellow-500' },
    { key: 'P', label: 'Pengendalian', color: 'bg-orange-500' },
    { key: 'P', label: 'Peningkatan', color: 'bg-green-500' },
];

const TAHAP_LABEL: Record<string, string> = {
    penetapan: 'Penetapan',
    pelaksanaan: 'Pelaksanaan',
    evaluasi: 'Evaluasi',
    pengendalian: 'Pengendalian',
    peningkatan: 'Peningkatan',
};

const TAHAP_INDEX: Record<string, number> = {
    penetapan: 0,
    pelaksanaan: 1,
    evaluasi: 2,
    pengendalian: 3,
    peningkatan: 4,
};

function tahapColor(tahap: string): string {
    const map: Record<string, string> = {
        penetapan: 'bg-blue-100 text-blue-800',
        pelaksanaan: 'bg-indigo-100 text-indigo-800',
        evaluasi: 'bg-yellow-100 text-yellow-800',
        pengendalian: 'bg-orange-100 text-orange-800',
        peningkatan: 'bg-green-100 text-green-800',
    };
    return map[tahap] || 'bg-gray-100 text-gray-800';
}

function tahapLetter(tahap: string): string {
    if (tahap === 'penetapan' || tahap === 'pelaksanaan' || tahap === 'peningkatan') return 'P';
    if (tahap === 'evaluasi') return 'E';
    if (tahap === 'pengendalian') return 'P';
    return '?';
}

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

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Index({ cycles, prodi_list, filters, success }: Props) {
    // ── Filter state ──
    const [search, setSearch] = useState(filters.search || '');
    const [prodiFilter, setProdiFilter] = useState(filters.prodi_id || '');
    const [tahapFilter, setTahapFilter] = useState(filters.tahap || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<SpmiCycleItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<SpmiCycleItem | null>(null);

     // ── Form ──
    const { data, setData, post, put, errors, processing, reset } = useForm({
        prodi_id: '',
        periode_id: '',
        instrumen_id: '',
        nama_siklus: '',
        tahap: 'penetapan',
        tanggal_mulai: '',
        tanggal_selesai: '',
        status: 'active',
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.cycle'),
                {
                    search,
                    prodi_id: prodiFilter,
                    tahap: tahapFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search, prodiFilter, tahapFilter]);

    // ── Modal handlers ──
    function openCreate() {
        reset();
        setEditing(null);
        setData('status', 'active');
        setShowModal(true);
    }

    function openEdit(item: SpmiCycleItem) {
        setEditing(item);
        setData({
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            instrumen_id: item.instrumen_id ? String(item.instrumen_id) : '',
            nama_siklus: item.nama_siklus,
            tahap: item.tahap,
            tanggal_mulai: item.tanggal_mulai || '',
            tanggal_selesai: item.tanggal_selesai || '',
            status: item.status,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('spmi.cycle.update', editing.id), {
                onSuccess: () => {
                    setShowModal(false);
                    setEditing(null);
                    reset();
                },
            });
        } else {
            post(route('spmi.cycle.store'), {
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                },
            });
        }
    };

    function confirmDelete(item: SpmiCycleItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.cycle.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    // ── Render PPEPP visualization ──
    function renderPpeppVisual(tahap: string, progress: number) {
        const idx = TAHAP_INDEX[tahap] ?? -1;

        return (
            <div className="flex items-center gap-1">
                {PPEPP_STEPS.map((step, i) => {
                    const isActive = i <= idx;
                    const isCurrent = i === idx;
                    return (
                        <div key={i} className="flex items-center">
                            <div
                                className={`flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold ${
                                    isActive
                                        ? isCurrent
                                            ? `${step.color} text-white ring-2 ring-offset-1 ring-indigo-400`
                                            : `${step.color} text-white`
                                        : 'bg-gray-200 text-gray-400'
                                }`}
                            >
                                {step.key}
                            </div>
                            {i < PPEPP_STEPS.length - 1 && (
                                <div
                                    className={`mx-0.5 h-0.5 w-4 ${isActive && i < idx ? 'bg-indigo-400' : 'bg-gray-200'}`}
                                />
                            )}
                        </div>
                    );
                })}
            </div>
        );
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Siklus PPEPP</h2>}
        >
            <Head title="Siklus PPEPP" />

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
                        <span className="text-gray-700">Siklus PPEPP</span>
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
                                            placeholder="Cari nama siklus..."
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

                                    {/* Tahap Filter */}
                                    <select
                                        value={tahapFilter}
                                        onChange={(e) => setTahapFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Tahap</option>
                                        <option value="penetapan">Penetapan</option>
                                        <option value="pelaksanaan">Pelaksanaan</option>
                                        <option value="evaluasi">Evaluasi</option>
                                        <option value="pengendalian">Pengendalian</option>
                                        <option value="peningkatan">Peningkatan</option>
                                    </select>
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah Siklus
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Nama Siklus
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Prodi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tahap
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal Mulai
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal Selesai
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Progress
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
                                    {cycles.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data siklus PPEPP.
                                            </td>
                                        </tr>
                                    ) : (
                                        cycles.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                    {item.nama_siklus}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.prodi?.nama_prodi || '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center gap-2">
                                                        <span
                                                            className={`inline-flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold ${tahapColor(item.tahap)}`}
                                                        >
                                                            {tahapLetter(item.tahap)}
                                                        </span>
                                                        <span className="text-sm text-gray-700">
                                                            {TAHAP_LABEL[item.tahap] || item.tahap}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {formatDate(item.tanggal_mulai)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {formatDate(item.tanggal_selesai)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center gap-2">
                                                        <div className="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                                            <div
                                                                className={`h-full rounded-full ${
                                                                    item.progress >= 100
                                                                        ? 'bg-green-500'
                                                                        : item.progress >= 50
                                                                          ? 'bg-yellow-500'
                                                                          : 'bg-indigo-500'
                                                                }`}
                                                                style={{ width: `${Math.min(item.progress, 100)}%` }}
                                                            />
                                                        </div>
                                                        <span className="text-xs font-medium text-gray-600">
                                                            {item.progress}%
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    {item.status === 'active' ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                                            <CheckCircle className="h-3 w-3" />
                                                            Aktif
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-800">
                                                            <Circle className="h-3 w-3" />
                                                            {item.status.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase())}
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
                        {cycles.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {cycles.from} - {cycles.to} dari {cycles.total}
                                </div>
                                <div className="flex gap-1">
                                    {cycles.links.map((link, i) => (
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

                    {/* PPEPP Visualization */}
                    {cycles.data.length > 0 && (
                        <div className="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    Visualisasi Siklus PPEPP
                                </h3>
                            </div>
                            <div className="divide-y divide-gray-50">
                                {cycles.data.slice(0, 5).map((item) => (
                                    <div key={item.id} className="flex items-center gap-4 px-6 py-4">
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-semibold text-gray-900">{item.nama_siklus}</p>
                                            <p className="text-xs text-gray-500">{item.prodi?.nama_prodi}</p>
                                        </div>
                                        <div className="flex items-center gap-1">
                                            {renderPpeppVisual(item.tahap, item.progress)}
                                            <ArrowRight className="ml-2 h-4 w-4 text-gray-400" />
                                            <span className="text-xs font-medium text-gray-600">{item.progress}%</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* ─── Create/Edit Modal ─── */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {editing ? 'Edit Siklus PPEPP' : 'Tambah Siklus PPEPP'}
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
                                     <label className="mb-1 block text-sm font-medium text-gray-700">Periode Akademik</label>
                                     <select
                                         value={data.periode_id}
                                         onChange={(e) => setData('periode_id', e.target.value)}
                                         className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                     >
                                         <option value="">Pilih Periode</option>
                                     </select>
                                     {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                                 </div>
                             </div>

                             {/* Instrumen */}
                             <div>
                                 <label className="mb-1 block text-sm font-medium text-gray-700">Instrumen Akreditasi</label>
                                 <select
                                     value={data.instrumen_id}
                                     onChange={(e) => setData('instrumen_id', e.target.value)}
                                     className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                 >
                                     <option value="">Pilih Instrumen (Opsional)</option>
                                 </select>
                                 {errors.instrumen_id && <p className="mt-1 text-xs text-red-600">{errors.instrumen_id}</p>}
                             </div>

                             {/* Tahap */}
                             <div>
                                 <label className="mb-1 block text-sm font-medium text-gray-700">Tahap PPEPP</label>
                                 <select
                                     value={data.tahap}
                                     onChange={(e) => setData('tahap', e.target.value)}
                                     className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                 >
                                     <option value="penetapan">Penetapan</option>
                                     <option value="pelaksanaan">Pelaksanaan</option>
                                     <option value="evaluasi">Evaluasi</option>
                                     <option value="pengendalian">Pengendalian</option>
                                     <option value="peningkatan">Peningkatan</option>
                                 </select>
                                 {errors.tahap && <p className="mt-1 text-xs text-red-600">{errors.tahap}</p>}
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
                            Yakin ingin menghapus siklus <strong>{deleteTarget.nama_siklus}</strong>?
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
