import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Eye, Edit3, CheckCircle, XCircle, Send, Loader2 } from 'lucide-react';
import StatusBadge from '@/Components/SPMI/StatusBadge';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface UserItem {
    id: number;
    name: string;
}

interface AuditItem {
    id: number;
    judul_audit: string;
    prodi?: ProdiItem;
    standarMutu?: { id: number; kode_standar: string; nama_standar: string };
}

interface CapaItem {
    id: number;
    audit_mutu_id: number;
    pic_user_id: number | null;
    verified_by_user_id: number | null;
    root_cause_category: string | null;
    root_cause_analysis: string | null;
    corrective_action: string | null;
    corrective_deadline: string | null;
    corrective_completed_at: string | null;
    corrective_evidence_file: string | null;
    preventive_action: string | null;
    preventive_deadline: string | null;
    preventive_completed_at: string | null;
    preventive_evidence_file: string | null;
    status: string;
    verification_note: string | null;
    verified_at: string | null;
    created_at: string;
    auditMutu?: AuditItem;
    picUser?: UserItem | null;
    verifiedBy?: UserItem | null;
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
    status: string;
    pic_user_id: string;
    overdue: string;
    search: string;
}

interface Props {
    capa: PaginatedData<CapaItem>;
    prodi_list: ProdiItem[];
    user_list: UserItem[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ capa, prodi_list, user_list, filters, success, errors: pageErrors }: Props) {
    // ── Filter state ──
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [prodiFilter, setProdiFilter] = useState(filters.prodi_id || '');
    const [picFilter, setPicFilter] = useState(filters.pic_user_id || '');
    const [overdueFilter, setOverdueFilter] = useState(filters.overdue || '');

    // ── Modal state ──
    const [showEditModal, setShowEditModal] = useState(false);
    const [editing, setEditing] = useState<CapaItem | null>(null);
    const [verifyTarget, setVerifyTarget] = useState<CapaItem | null>(null);
    const [verifyAction, setVerifyAction] = useState<'approved' | 'rejected'>('approved');
    const [verifyNote, setVerifyNote] = useState('');
    const [verifyProcessing, setVerifyProcessing] = useState(false);
    const [submitTarget, setSubmitTarget] = useState<CapaItem | null>(null);
    const [submitProcessing, setSubmitProcessing] = useState(false);

    // ── Edit form ──
    const editForm = useForm({
        root_cause_category: '',
        root_cause_analysis: '',
        corrective_action: '',
        corrective_deadline: '',
        corrective_evidence_file: null as File | null,
        preventive_action: '',
        preventive_deadline: '',
        preventive_evidence_file: null as File | null,
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.capa'),
                {
                    search,
                    status: statusFilter,
                    prodi_id: prodiFilter,
                    pic_user_id: picFilter,
                    overdue: overdueFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search, statusFilter, prodiFilter, picFilter, overdueFilter]);

    // ── Open edit modal ──
    function openEdit(item: CapaItem) {
        setEditing(item);
        editForm.setData({
            root_cause_category: item.root_cause_category || '',
            root_cause_analysis: item.root_cause_analysis || '',
            corrective_action: item.corrective_action || '',
            corrective_deadline: item.corrective_deadline || '',
            corrective_evidence_file: null,
            preventive_action: item.preventive_action || '',
            preventive_deadline: item.preventive_deadline || '',
            preventive_evidence_file: null,
        });
        setShowEditModal(true);
    }

    // ── Submit edit ──
    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editing) return;

        const formData = new FormData();
        formData.append('_method', 'PUT');

        Object.entries(editForm.data).forEach(([key, value]) => {
            if (key === 'corrective_evidence_file' || key === 'preventive_evidence_file') {
                if (value instanceof File) {
                    formData.append(key, value);
                }
            } else if (value !== null && value !== undefined) {
                formData.append(key, String(value));
            }
        });

        router.post(route('spmi.capa.update', editing.id), formData as any, {
            forceFormData: true,
            onSuccess: () => {
                setShowEditModal(false);
                setEditing(null);
                editForm.reset();
            },
        });
    };

    // ── Submit for verification ──
    function handleSubmitVerification(item: CapaItem) {
        setSubmitTarget(item);
        setSubmitProcessing(true);
        router.post(
            route('spmi.capa.submit-verification', item.id),
            {},
            {
                onSuccess: () => {
                    setSubmitTarget(null);
                    setSubmitProcessing(false);
                },
                onError: () => setSubmitProcessing(false),
            }
        );
    }

    // ── Verify / Reject ──
    function handleVerify() {
        if (!verifyTarget) return;
        setVerifyProcessing(true);
        router.post(
            route('spmi.capa.verify', verifyTarget.id),
            { action: verifyAction, note: verifyNote },
            {
                onSuccess: () => {
                    setVerifyTarget(null);
                    setVerifyNote('');
                    setVerifyProcessing(false);
                },
                onError: () => setVerifyProcessing(false),
            }
        );
    }

    function countdownLabel(deadline: string | null): { text: string; isOverdue: boolean } | null {
        if (!deadline) return null;
        const now = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate.getTime() - now.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 0) {
            return { text: `Terlambat ${Math.abs(diffDays)} hari`, isOverdue: true };
        }
        if (diffDays === 0) {
            return { text: 'Hari ini', isOverdue: false };
        }
        return { text: `${diffDays} hari lagi`, isOverdue: false };
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">CAPA</h2>}
        >
            <Head title="CAPA - Corrective and Preventive Action" />

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
                        <span className="text-gray-700">CAPA</span>
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
                    {pageErrors && Object.values(pageErrors).length > 0 && (
                        <div className="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700">
                            {Object.values(pageErrors).map((err, i) => (
                                <p key={i}>{err}</p>
                            ))}
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
                                            placeholder="Cari judul temuan..."
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

                                    {/* Status Filter */}
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => setStatusFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="awaiting_verification">Awaiting Verification</option>
                                        <option value="verified">Verified</option>
                                        <option value="closed">Closed</option>
                                        <option value="rejected">Rejected</option>
                                    </select>

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

                                    {/* PIC Filter */}
                                    <select
                                        value={picFilter}
                                        onChange={(e) => setPicFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua PIC</option>
                                        {user_list.map((u) => (
                                            <option key={u.id} value={u.id}>
                                                {u.name}
                                            </option>
                                        ))}
                                    </select>

                                    {/* Overdue Filter */}
                                    <select
                                        value={overdueFilter}
                                        onChange={(e) => setOverdueFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Deadline</option>
                                        <option value="1">Overdue Saja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Judul Temuan
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Standar Mutu
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Prodi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            PIC
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Deadline
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
                                    {capa.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data CAPA.
                                            </td>
                                        </tr>
                                    ) : (
                                        capa.data.map((item) => {
                                            const countdown = countdownLabel(item.corrective_deadline);
                                            return (
                                                <tr key={item.id} className="hover:bg-gray-50">
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <Link
                                                            href={route('spmi.capa.show', item.id)}
                                                            className="font-medium text-indigo-600 hover:text-indigo-900 hover:underline"
                                                        >
                                                            {item.auditMutu?.judul_audit || `CAPA #${item.id}`}
                                                        </Link>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {item.auditMutu?.standarMutu?.kode_standar || '-'}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {item.auditMutu?.prodi?.nama_prodi || '-'}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                        {item.picUser?.name || '-'}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        {countdown ? (
                                                            <span
                                                                className={
                                                                    countdown.isOverdue
                                                                        ? 'font-semibold text-red-600'
                                                                        : 'text-gray-600'
                                                                }
                                                            >
                                                                {countdown.text}
                                                            </span>
                                                        ) : (
                                                            <span className="text-gray-400">-</span>
                                                        )}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4">
                                                        <StatusBadge
                                                            status={item.status}
                                                            workflowType="capa"
                                                            size="sm"
                                                        />
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <div className="flex items-center gap-1">
                                                            <Link
                                                                href={route('spmi.capa.show', item.id)}
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
                                                            {item.status === 'in_progress' && (
                                                                <button
                                                                    onClick={() => handleSubmitVerification(item)}
                                                                    disabled={submitProcessing && submitTarget?.id === item.id}
                                                                    className="rounded p-1.5 text-purple-500 hover:bg-purple-50 hover:text-purple-600 disabled:opacity-50"
                                                                    title="Ajukan Verifikasi"
                                                                >
                                                                    {submitProcessing && submitTarget?.id === item.id ? (
                                                                        <Loader2 className="h-4 w-4 animate-spin" />
                                                                    ) : (
                                                                        <Send className="h-4 w-4" />
                                                                    )}
                                                                </button>
                                                            )}
                                                            {item.status === 'awaiting_verification' && (
                                                                <>
                                                                    <button
                                                                        onClick={() => {
                                                                            setVerifyTarget(item);
                                                                            setVerifyAction('approved');
                                                                            setVerifyNote('');
                                                                        }}
                                                                        className="rounded p-1.5 text-green-500 hover:bg-green-50 hover:text-green-600"
                                                                        title="Setujui"
                                                                    >
                                                                        <CheckCircle className="h-4 w-4" />
                                                                    </button>
                                                                    <button
                                                                        onClick={() => {
                                                                            setVerifyTarget(item);
                                                                            setVerifyAction('rejected');
                                                                            setVerifyNote('');
                                                                        }}
                                                                        className="rounded p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600"
                                                                        title="Tolak"
                                                                    >
                                                                        <XCircle className="h-4 w-4" />
                                                                    </button>
                                                                </>
                                                            )}
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
                        {capa.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {capa.from} - {capa.to} dari {capa.total}
                                </div>
                                <div className="flex gap-1">
                                    {capa.links.map((link, i) => (
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

            {/* ─── Edit Modal ─── */}
            {showEditModal && editing && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Edit CAPA #{editing.id}
                            </h3>
                            <button
                                onClick={() => {
                                    setShowEditModal(false);
                                    setEditing(null);
                                    editForm.reset();
                                }}
                                className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submitEdit} className="max-h-[70vh] overflow-y-auto space-y-4 pr-2">
                            {/* Root Cause Category */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">
                                    Kategori Root Cause
                                </label>
                                <select
                                    value={editForm.data.root_cause_category}
                                    onChange={(e) => editForm.setData('root_cause_category', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Kategori</option>
                                    <option value="sdm">SDM</option>
                                    <option value="proses">Proses</option>
                                    <option value="sarana">Sarana Prasarana</option>
                                    <option value="keuangan">Keuangan</option>
                                    <option value="kurikulum">Kurikulum</option>
                                    <option value="organisasi">Organisasi</option>
                                    <option value="eksternal">Eksternal</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>

                            {/* Root Cause Analysis */}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">
                                    Analisis Akar Masalah (Root Cause Analysis)
                                </label>
                                <textarea
                                    value={editForm.data.root_cause_analysis}
                                    onChange={(e) => editForm.setData('root_cause_analysis', e.target.value)}
                                    rows={4}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Jelaskan analisis akar masalah..."
                                />
                                {editForm.errors.root_cause_analysis && (
                                    <p className="mt-1 text-xs text-red-600">{editForm.errors.root_cause_analysis}</p>
                                )}
                            </div>

                            {/* Corrective Action */}
                            <div className="border-t border-gray-100 pt-4">
                                <h4 className="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">
                                    Corrective Action (Tindakan Korektif)
                                </h4>
                                <div className="space-y-3">
                                    <div>
                                        <textarea
                                            value={editForm.data.corrective_action}
                                            onChange={(e) => editForm.setData('corrective_action', e.target.value)}
                                            rows={3}
                                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Deskripsi tindakan korektif..."
                                        />
                                        {editForm.errors.corrective_action && (
                                            <p className="mt-1 text-xs text-red-600">{editForm.errors.corrective_action}</p>
                                        )}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="mb-1 block text-xs font-medium text-gray-600">
                                                Deadline
                                            </label>
                                            <input
                                                type="date"
                                                value={editForm.data.corrective_deadline}
                                                onChange={(e) => editForm.setData('corrective_deadline', e.target.value)}
                                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-xs font-medium text-gray-600">
                                                File Bukti
                                            </label>
                                            <input
                                                type="file"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] || null;
                                                    editForm.setData('corrective_evidence_file', file);
                                                }}
                                                className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                            />
                                            {editing.corrective_evidence_file && (
                                                <p className="mt-1 text-xs text-gray-400">
                                                    File saat ini: {editing.corrective_evidence_file}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Preventive Action */}
                            <div className="border-t border-gray-100 pt-4">
                                <h4 className="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">
                                    Preventive Action (Tindakan Preventif)
                                </h4>
                                <div className="space-y-3">
                                    <div>
                                        <textarea
                                            value={editForm.data.preventive_action}
                                            onChange={(e) => editForm.setData('preventive_action', e.target.value)}
                                            rows={3}
                                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Deskripsi tindakan preventif..."
                                        />
                                        {editForm.errors.preventive_action && (
                                            <p className="mt-1 text-xs text-red-600">{editForm.errors.preventive_action}</p>
                                        )}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="mb-1 block text-xs font-medium text-gray-600">
                                                Deadline
                                            </label>
                                            <input
                                                type="date"
                                                value={editForm.data.preventive_deadline}
                                                onChange={(e) => editForm.setData('preventive_deadline', e.target.value)}
                                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-xs font-medium text-gray-600">
                                                File Bukti
                                            </label>
                                            <input
                                                type="file"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] || null;
                                                    editForm.setData('preventive_evidence_file', file);
                                                }}
                                                className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                            />
                                            {editing.preventive_evidence_file && (
                                                <p className="mt-1 text-xs text-gray-400">
                                                    File saat ini: {editing.preventive_evidence_file}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowEditModal(false);
                                        setEditing(null);
                                        editForm.reset();
                                    }}
                                    className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* ─── Verify/Reject Modal ─── */}
            {verifyTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">
                            {verifyAction === 'approved' ? 'Verifikasi CAPA' : 'Tolak CAPA'}
                        </h3>
                        <p className="mb-4 text-sm text-gray-600">
                            {verifyAction === 'approved'
                                ? 'Setujui CAPA ini? Pastikan semua tindakan korektif dan preventif telah dilaksanakan.'
                                : 'Tolak CAPA ini dan kembalikan untuk perbaikan.'}
                        </p>
                        <div className="mb-4">
                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                Catatan Verifikasi <span className="text-red-500">*</span>
                            </label>
                            <textarea
                                value={verifyNote}
                                onChange={(e) => setVerifyNote(e.target.value)}
                                rows={3}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Masukkan catatan verifikasi..."
                            />
                        </div>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => {
                                    setVerifyTarget(null);
                                    setVerifyNote('');
                                }}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleVerify}
                                disabled={!verifyNote || verifyProcessing}
                                className={`rounded-lg px-4 py-2 text-sm text-white disabled:opacity-50 ${
                                    verifyAction === 'approved'
                                        ? 'bg-green-600 hover:bg-green-700'
                                        : 'bg-red-600 hover:bg-red-700'
                                }`}
                            >
                                {verifyProcessing
                                    ? 'Memproses...'
                                    : verifyAction === 'approved'
                                      ? 'Setujui'
                                      : 'Tolak'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
