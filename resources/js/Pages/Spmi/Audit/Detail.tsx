import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ArrowLeft,
    Calendar,
    User,
    FileText,
    Clock,
    AlertTriangle,
    CheckCircle2,
    Edit3,
    Trash2,
    Download,
    Shield,
} from 'lucide-react';
import SeverityBadge from '@/Components/SPMI/SeverityBadge';
import StatusBadge from '@/Components/SPMI/StatusBadge';
import AuditTimeline from './Partials/AuditTimeline';
import WorkflowActions from './Partials/WorkflowActions';

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

interface CapaItem {
    id: number;
    status: string;
    corrective_deadline: string | null;
    preventive_deadline: string | null;
    root_cause_analysis: string | null;
    corrective_action: string | null;
    preventive_action: string | null;
    picUser?: UserItem | null;
}

interface AuditHistoryItem {
    id: number;
    audit_mutu_id: number;
    user_id: number | null;
    field: string;
    old_value: string | null;
    new_value: string | null;
    action: string;
    created_at: string;
    user?: { id: number; name: string } | null;
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
    capas?: CapaItem[];
    histories?: AuditHistoryItem[];
}

interface Props {
    audit: AuditItem;
    histories: AuditHistoryItem[];
    can_transition: Record<string, boolean>;
}

export default function Detail({ audit, histories, can_transition = {} }: Props) {
    const [deleteConfirm, setDeleteConfirm] = useState(false);
    const severity = audit.severity as 'ringan' | 'sedang' | 'berat' | 'kritis' | null;

    function handleTransition(toStatus: string) {
        router.post(
            route('spmi.audit.transition', audit.id),
            { status: toStatus },
            { preserveScroll: true }
        );
    }

    function handleDelete() {
        router.delete(route('spmi.audit.destroy', audit.id), {
            onSuccess: () => setDeleteConfirm(false),
        });
    }

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

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Detail Audit Mutu
                </h2>
            }
        >
            <Head title="Detail Audit Mutu" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                            Dashboard
                        </Link>
                        <span className="mx-2">/</span>
                        <Link href={route('spmi.audit')} className="text-indigo-600 hover:text-indigo-900">
                            Audit Mutu
                        </Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">{audit.judul_audit}</span>
                    </nav>

                    <div className="mb-4">
                        <Link
                            href={route('spmi.audit')}
                            className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar Audit
                        </Link>
                    </div>

                    {/* ════════════════════════════════════════════════════════════════
                        Header Card
                        ════════════════════════════════════════════════════════════════ */}
                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                            <div className="min-w-0 flex-1">
                                <h1 className="text-xl font-bold text-gray-900">{audit.judul_audit}</h1>
                                <div className="mt-2 flex flex-wrap items-center gap-3">
                                    {severity && <SeverityBadge severity={severity} size="md" />}
                                    <StatusBadge status={audit.status} workflowType="audit" size="md" />
                                    {audit.is_locked && (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                            <Shield className="h-3 w-3" />
                                            Terkunci
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex flex-wrap items-center gap-2">
                                <Link
                                    href={route('spmi.audit')}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    <Edit3 className="h-4 w-4" />
                                    Edit
                                </Link>
                                {!audit.is_locked && (
                                    <button
                                        onClick={() => setDeleteConfirm(true)}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                        Hapus
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                            {/* Prodi */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Program Studi
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {audit.prodi?.nama_prodi || '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Standar Mutu */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                                    <FileText className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Standar Mutu
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {audit.standarMutu
                                            ? `${audit.standarMutu.kode_standar} - ${audit.standarMutu.nama_standar}`
                                            : '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Periode */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Periode
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {audit.periode?.nama_periode || '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Tanggal Audit */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Tanggal Audit
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {formatDate(audit.tanggal_audit)}
                                    </p>
                                </div>
                            </div>

                            {/* Auditor */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Auditor
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {audit.auditor || '-'}
                                    </p>
                                </div>
                            </div>

                            {/* PIC */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        PIC
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {audit.picUser?.name || '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Deadline */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                                    <Clock className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Deadline Tindak Lanjut
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {formatDate(audit.deadline_tindak_lanjut)}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Workflow Actions */}
                        <div className="border-t border-gray-100 px-6 py-4">
                            <div className="flex items-center gap-4">
                                <span className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                    Workflow:
                                </span>
                                <WorkflowActions
                                    currentStatus={audit.status}
                                    canTransition={(toStatus) => can_transition[toStatus] ?? true}
                                    onTransition={handleTransition}
                                    isLocked={audit.is_locked}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* ════════════════════════════════════════════════════════════════
                            Left Column: Temuan & Rekomendasi
                            ════════════════════════════════════════════════════════════════ */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Temuan */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <AlertTriangle className="h-4 w-4 text-red-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Temuan
                                    </h3>
                                </div>
                                <div className="p-6">
                                    {audit.temuan ? (
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {audit.temuan}
                                        </p>
                                    ) : (
                                        <p className="text-sm italic text-gray-400">Tidak ada temuan.</p>
                                    )}
                                </div>
                            </div>

                            {/* Rekomendasi */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <CheckCircle2 className="h-4 w-4 text-green-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Rekomendasi
                                    </h3>
                                </div>
                                <div className="p-6">
                                    {audit.rekomendasi ? (
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {audit.rekomendasi}
                                        </p>
                                    ) : (
                                        <p className="text-sm italic text-gray-400">Tidak ada rekomendasi.</p>
                                    )}
                                </div>
                            </div>

                            {/* Tindak Lanjut */}
                            {audit.tindak_lanjut && (
                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                        <FileText className="h-4 w-4 text-indigo-500" />
                                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                            Tindak Lanjut
                                        </h3>
                                    </div>
                                    <div className="p-6">
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {audit.tindak_lanjut}
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* Evidence File */}
                            {audit.evidence_file && (
                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                        <Download className="h-4 w-4 text-blue-500" />
                                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                            File Bukti
                                        </h3>
                                    </div>
                                    <div className="p-6">
                                        <a
                                            href={`/storage/${audit.evidence_file}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50"
                                        >
                                            <Download className="h-4 w-4" />
                                            Download File Bukti
                                        </a>
                                    </div>
                                </div>
                            )}

                            {/* Verification Note */}
                            {audit.verification_note && (
                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                        <Shield className="h-4 w-4 text-purple-500" />
                                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                            Catatan Verifikasi
                                        </h3>
                                    </div>
                                    <div className="p-6">
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {audit.verification_note}
                                        </p>
                                        {audit.verified_at && (
                                            <p className="mt-2 text-xs text-gray-400">
                                Diverifikasi pada: {formatDate(audit.verified_at)}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Timeline */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <Clock className="h-4 w-4 text-gray-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Timeline Aktivitas
                                    </h3>
                                </div>
                                <div className="p-6">
                                    <AuditTimeline histories={histories} />
                                </div>
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                            Right Column: CAPA
                            ════════════════════════════════════════════════════════════════ */}
                        <div className="space-y-6">
                            {/* Related CAPA */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <AlertTriangle className="h-4 w-4 text-orange-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        CAPA Terkait
                                    </h3>
                                </div>
                                <div className="p-6">
                                    {audit.capas && audit.capas.length > 0 ? (
                                        <div className="space-y-3">
                                            {audit.capas.map((capa) => (
                                                <Link
                                                    key={capa.id}
                                                    href={route('spmi.capa.show', capa.id)}
                                                    className="block rounded-lg border border-gray-100 p-4 transition-all hover:border-indigo-200 hover:shadow-sm"
                                                >
                                                    <div className="mb-2 flex items-center justify-between">
                                                        <span className="text-xs font-bold text-gray-500">
                                                            CAPA #{capa.id}
                                                        </span>
                                                        <StatusBadge
                                                            status={capa.status}
                                                            workflowType="capa"
                                                            size="sm"
                                                        />
                                                    </div>
                                                    {capa.root_cause_analysis && (
                                                        <p className="line-clamp-2 text-xs text-gray-600">
                                                            {capa.root_cause_analysis}
                                                        </p>
                                                    )}
                                                    {capa.corrective_deadline && (
                                                        <p className="mt-2 text-[10px] font-medium text-gray-400">
                                                            Deadline: {formatDate(capa.corrective_deadline)}
                                                        </p>
                                                    )}
                                                </Link>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="py-6 text-center">
                                            <p className="text-sm text-gray-400">Belum ada CAPA terkait.</p>
                                            {audit.severity && ['sedang', 'berat', 'kritis'].includes(audit.severity) && (
                                                <p className="mt-1 text-xs text-gray-400">
                                                    CAPA akan dibuat otomatis saat audit disimpan untuk severity Sedang ke atas.
                                                </p>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Info Card */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="border-b border-gray-100 px-6 py-4">
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Informasi Audit
                                    </h3>
                                </div>
                                <div className="divide-y divide-gray-50">
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">Dibuat pada</span>
                                        <span className="text-xs font-medium text-gray-700">
                                            {formatDate(audit.created_at)}
                                        </span>
                                    </div>
                                    {audit.closed_at && (
                                        <div className="flex items-center justify-between px-6 py-3">
                                            <span className="text-xs text-gray-500">Ditutup pada</span>
                                            <span className="text-xs font-medium text-gray-700">
                                                {formatDate(audit.closed_at)}
                                            </span>
                                        </div>
                                    )}
                                    {audit.verified_at && (
                                        <div className="flex items-center justify-between px-6 py-3">
                                            <span className="text-xs text-gray-500">Diverifikasi pada</span>
                                            <span className="text-xs font-medium text-gray-700">
                                                {formatDate(audit.verified_at)}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* ─── Delete Confirmation ─── */}
            {deleteConfirm && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">
                            Yakin ingin menghapus audit <strong>{audit.judul_audit}</strong>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setDeleteConfirm(false)}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleDelete}
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
