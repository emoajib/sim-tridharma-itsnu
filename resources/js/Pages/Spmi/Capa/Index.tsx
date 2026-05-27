import React, { Suspense, useState, useEffect, FormEventHandler } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import FilterBar from './Components/FilterBar';
import CapaTable from './Components/CapaTable';

// ─── Lazy-loaded modal ────────────────────────────────────────────────────────
const CapaFormModal = React.lazy(() => import('./Components/CapaFormModal'));

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
                { search, status: statusFilter, prodi_id: prodiFilter, pic_user_id: picFilter, overdue: overdueFilter },
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
                onSuccess: () => { setSubmitTarget(null); setSubmitProcessing(false); },
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
                onSuccess: () => { setVerifyTarget(null); setVerifyNote(''); setVerifyProcessing(false); },
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
        if (diffDays < 0) return { text: `Terlambat ${Math.abs(diffDays)} hari`, isOverdue: true };
        if (diffDays === 0) return { text: 'Hari ini', isOverdue: false };
        return { text: `${diffDays} hari lagi`, isOverdue: false };
    }

    function closeEditModal() {
        setShowEditModal(false);
        setEditing(null);
        editForm.reset();
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">CAPA</h2>}>
            <Head title="CAPA - Corrective and Preventive Action" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-indigo-600 hover:text-indigo-900">SPMI</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">CAPA</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('spmi.dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">
                            &larr; Kembali ke Dashboard SPMI
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}
                    {pageErrors && Object.values(pageErrors).length > 0 && (
                        <div className="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700">
                            {Object.values(pageErrors).map((err, i) => <p key={i}>{err}</p>)}
                        </div>
                    )}

                    {/* Main Card */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {/* Filter Bar */}
                        <div className="border-b border-gray-200 p-6">
                            <FilterBar
                                search={search}
                                setSearch={setSearch}
                                statusFilter={statusFilter}
                                setStatusFilter={setStatusFilter}
                                prodiFilter={prodiFilter}
                                setProdiFilter={setProdiFilter}
                                picFilter={picFilter}
                                setPicFilter={setPicFilter}
                                overdueFilter={overdueFilter}
                                setOverdueFilter={setOverdueFilter}
                                prodi_list={prodi_list}
                                user_list={user_list}
                            />
                        </div>

                        {/* Table */}
                        <CapaTable
                            capa={capa}
                            openEdit={openEdit}
                            handleSubmitVerification={handleSubmitVerification}
                            setVerifyTarget={setVerifyTarget}
                            setVerifyAction={setVerifyAction}
                            setVerifyNote={setVerifyNote}
                            submitTarget={submitTarget}
                            submitProcessing={submitProcessing}
                            countdownLabel={countdownLabel}
                        />
                    </div>
                </div>
            </div>

            {/* ─── Edit Modal (lazy) ─── */}
            <Suspense fallback={null}>
                <CapaFormModal
                    show={showEditModal}
                    editing={editing}
                    onClose={closeEditModal}
                    onSubmit={submitEdit}
                    form={editForm}
                />
            </Suspense>

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
