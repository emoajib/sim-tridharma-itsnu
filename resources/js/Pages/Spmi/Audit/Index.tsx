import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import {
    AlertTriangle,
    Clock,
    CheckCircle2,
    TrendingUp,
    Search,
    X,
    RefreshCw,
    Users,
} from 'lucide-react';
import KpiCard from '@/Components/SPMI/KpiCard';
import AuditTable from './Partials/AuditTable';
import AuditFormModal from './Partials/AuditFormModal';

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

interface UserItem {
    id: number;
    name: string;
}

interface AuditItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    standar_mutu_id: number | null;
    judul_audit: string;
    tanggal_audit: string;
    auditor: string | null;
    temuan: string | null;
    rekomendasi: string | null;
    tindak_lanjut: string | null;
    status: string;
    severity: string | null;
    pic_user_id: number | null;
    auditor_user_id: number | null;
    deadline_tindak_lanjut: string | null;
    closed_at: string | null;
    evidence_file: string | null;
    verification_note: string | null;
    verified_by: number | null;
    verified_at: string | null;
    is_locked: boolean;
    locked_at: string | null;
    created_at: string;
    prodi?: ProdiItem;
    periode?: PeriodeItem;
    standarMutu?: StandarItem;
    picUser?: UserItem;
    capas?: { id: number; status: string }[];
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

interface DashboardStats {
    total_temuan: number;
    open_temuan: number;
    in_progress_temuan: number;
    closed_temuan: number;
    close_rate: number;
    skor_mutu: number;
    capa_overdue_count: number;
    capa_approaching_count: number;
}

interface Filters {
    search: string;
    status: string;
    standar_mutu_id: string;
    severity: string;
    pic_user_id: string;
    prodi_id: string;
    periode_id: string;
}

interface Props {
    audit: PaginatedData<AuditItem>;
    prodi_list: ProdiItem[];
    periode_list: PeriodeItem[];
    standar_mutu_list: StandarItem[];
    user_list: UserItem[];
    filters: Filters;
    dashboard_stats?: DashboardStats;
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({
    audit,
    prodi_list,
    periode_list,
    standar_mutu_list,
    user_list,
    filters,
    dashboard_stats,
    success,
}: Props) {
    // ── Local state for filters (debounced) ──
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [standarFilter, setStandarFilter] = useState(filters.standar_mutu_id || '');
    const [severityFilter, setSeverityFilter] = useState(filters.severity || '');
    const [picFilter, setPicFilter] = useState(filters.pic_user_id || '');
    const [prodiFilter, setProdiFilter] = useState(filters.prodi_id || '');
    const [periodeFilter, setPeriodeFilter] = useState(filters.periode_id || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<AuditItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<AuditItem | null>(null);
    const [transitionTarget, setTransitionTarget] = useState<{ item: AuditItem; toStatus: string } | null>(null);
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [bulkStatus, setBulkStatus] = useState('');
    const [bulkProcessing, setBulkProcessing] = useState(false);

    // ── Debounced filter change ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.audit'),
                {
                    search,
                    status: statusFilter,
                    standar_mutu_id: standarFilter,
                    severity: severityFilter,
                    pic_user_id: picFilter,
                    prodi_id: prodiFilter,
                    periode_id: periodeFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [search, statusFilter, standarFilter, severityFilter, picFilter, prodiFilter, periodeFilter]);

    // ── Modal handlers ──
    function openCreate() {
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: AuditItem) {
        setEditing(item);
        setShowModal(true);
    }

    function confirmDelete(item: AuditItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        router.delete(route('spmi.audit.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    // ── Transition handler ──
    function requestTransition(item: AuditItem, toStatus: string) {
        setTransitionTarget({ item, toStatus });
    }

    function executeTransition() {
        if (!transitionTarget) return;
        router.post(
            route('spmi.audit.transition', transitionTarget.item.id),
            { status: transitionTarget.toStatus },
            {
                onSuccess: () => {
                    setTransitionTarget(null);
                    setSelectedIds([]);
                },
            }
        );
    }

    // ── Bulk action ──
    function executeBulkTransition() {
        if (!bulkStatus || selectedIds.length === 0) return;
        setBulkProcessing(true);
        router.post(
            route('spmi.audit.batch-transition'),
            { ids: selectedIds, status: bulkStatus },
            {
                onSuccess: () => {
                    setBulkProcessing(false);
                    setSelectedIds([]);
                    setBulkStatus('');
                },
                onError: () => setBulkProcessing(false),
            }
        );
    }

    // ── Render ──
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">Audit Mutu</h2>
            }
        >
            <Head title="Audit Mutu" />

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
                        <span className="text-gray-700">Audit</span>
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

                    {/* ════════════════════════════════════════════════════════════════
                        KPI Summary Cards
                        ════════════════════════════════════════════════════════════════ */}
                    {dashboard_stats && (
                        <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                            <KpiCard
                                title="Total Temuan"
                                value={dashboard_stats.total_temuan}
                                icon={<AlertTriangle className="h-5 w-5" />}
                                color="blue"
                            />
                            <KpiCard
                                title="Open"
                                value={dashboard_stats.open_temuan}
                                icon={<Clock className="h-5 w-5" />}
                                color="yellow"
                            />
                            <KpiCard
                                title="In Progress"
                                value={dashboard_stats.in_progress_temuan}
                                icon={<RefreshCw className="h-5 w-5" />}
                                color="purple"
                            />
                            <KpiCard
                                title="Close Rate"
                                value={`${dashboard_stats.close_rate}%`}
                                icon={<CheckCircle2 className="h-5 w-5" />}
                                color="green"
                                trend={{
                                    value: dashboard_stats.close_rate,
                                    direction:
                                        dashboard_stats.close_rate >= 70
                                            ? 'up'
                                            : dashboard_stats.close_rate >= 40
                                              ? 'flat'
                                              : 'down',
                                }}
                            />
                            <KpiCard
                                title="Skor Mutu"
                                value={dashboard_stats.skor_mutu.toFixed(2)}
                                icon={<TrendingUp className="h-5 w-5" />}
                                color="purple"
                            />
                            <KpiCard
                                title="CAPA Overdue"
                                value={dashboard_stats.capa_overdue_count}
                                icon={<AlertTriangle className="h-5 w-5" />}
                                color="red"
                            />
                            <KpiCard
                                title="CAPA Mendekat"
                                value={dashboard_stats.capa_approaching_count}
                                icon={<Clock className="h-5 w-5" />}
                                color="yellow"
                            />
                        </div>
                    )}

                    {/* ════════════════════════════════════════════════════════════════
                        Main Card
                        ════════════════════════════════════════════════════════════════ */}
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
                                            placeholder="Cari judul atau auditor..."
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
                                        <option value="submitted">Submitted</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="awaiting_verification">Awaiting Verification</option>
                                        <option value="verified">Verified</option>
                                        <option value="closed">Closed</option>
                                        <option value="rejected">Rejected</option>
                                    </select>

                                    {/* Standar Mutu Filter */}
                                    <select
                                        value={standarFilter}
                                        onChange={(e) => setStandarFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Standar</option>
                                        {standar_mutu_list.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.kode_standar}
                                            </option>
                                        ))}
                                    </select>

                                    {/* Severity Filter */}
                                    <select
                                        value={severityFilter}
                                        onChange={(e) => setSeverityFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Severity</option>
                                        <option value="ringan">Ringan</option>
                                        <option value="sedang">Sedang</option>
                                        <option value="berat">Berat</option>
                                        <option value="kritis">Kritis</option>
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
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Audit
                                </button>
                            </div>
                        </div>

                        {/* Bulk Actions Bar */}
                        {selectedIds.length > 0 && (
                            <div className="flex items-center gap-3 border-b border-gray-200 bg-indigo-50 px-6 py-3">
                                <span className="text-sm font-medium text-indigo-700">
                                    <Users className="mr-1 inline-block h-4 w-4" />
                                    {selectedIds.length} terpilih
                                </span>
                                <select
                                    value={bulkStatus}
                                    onChange={(e) => setBulkStatus(e.target.value)}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Ubah Status</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="awaiting_verification">Awaiting Verification</option>
                                    <option value="verified">Verified</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <button
                                    onClick={executeBulkTransition}
                                    disabled={!bulkStatus || bulkProcessing}
                                    className="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {bulkProcessing ? 'Memproses...' : 'Terapkan'}
                                </button>
                                <button
                                    onClick={() => setSelectedIds([])}
                                    className="text-xs font-medium text-gray-500 hover:text-gray-700"
                                >
                                    Batalkan pilihan
                                </button>
                            </div>
                        )}

                        {/* Table */}
                        <AuditTable
                            audit={audit}
                            filters={{
                                search,
                                status: statusFilter,
                                standar_mutu_id: standarFilter,
                                severity: severityFilter,
                                prodi_id: prodiFilter,
                                periode_id: periodeFilter,
                            }}
                            onEdit={openEdit}
                            onDelete={confirmDelete}
                            onTransition={requestTransition}
                            selectedIds={selectedIds}
                            onSelectChange={setSelectedIds}
                        />
                    </div>
                </div>
            </div>

            {/* ─── Create/Edit Modal ─── */}
            <AuditFormModal
                show={showModal}
                editing={editing}
                prodi_list={prodi_list}
                periode_list={periode_list}
                standar_list={standar_mutu_list}
                user_list={user_list}
                onClose={() => {
                    setShowModal(false);
                    setEditing(null);
                }}
                onSuccess={() => {}}
            />

            {/* ─── Delete Confirmation ─── */}
            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">
                            Yakin ingin menghapus audit <strong>{deleteTarget.judul_audit}</strong>?
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

            {/* ─── Transition Confirmation ─── */}
            {transitionTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Perubahan Status</h3>
                        <p className="mb-4 text-sm text-gray-600">
                            Ubah status <strong>{transitionTarget.item.judul_audit}</strong> ke{' '}
                            <strong>{transitionTarget.toStatus.replace(/_/g, ' ')}</strong>?
                        </p>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setTransitionTarget(null)}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={executeTransition}
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                            >
                                Konfirmasi
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
