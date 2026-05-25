import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ArrowLeft,
    Calendar,
    User,
    FileText,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    Download,
    Send,
    Shield,
    Clock,
    Edit3,
} from 'lucide-react';
import StatusBadge from '@/Components/SPMI/StatusBadge';
import CapaTimeline from './Partials/CapaTimeline';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface UserItem {
    id: number;
    name: string;
}

interface StandarItem {
    id: number;
    kode_standar: string;
    nama_standar: string;
}

interface AuditItem {
    id: number;
    judul_audit: string;
    temuan: string | null;
    rekomendasi: string | null;
    severity: string | null;
    prodi?: ProdiItem;
    standarMutu?: StandarItem;
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

interface Props {
    capa: CapaItem;
    timeline: AuditHistoryItem[];
}

export default function Show({ capa, timeline }: Props) {
    const [verifyNote, setVerifyNote] = useState('');
    const [verifyProcessing, setVerifyProcessing] = useState(false);
    const [submitProcessing, setSubmitProcessing] = useState(false);

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

    function handleSubmitVerification() {
        setSubmitProcessing(true);
        router.post(
            route('spmi.capa.submit-verification', capa.id),
            {},
            {
                onSuccess: () => setSubmitProcessing(false),
                onError: () => setSubmitProcessing(false),
            }
        );
    }

    function handleVerify(approved: boolean) {
        if (!verifyNote) return;
        setVerifyProcessing(true);
        router.post(
            route('spmi.capa.verify', capa.id),
            { action: approved ? 'approved' : 'rejected', note: verifyNote },
            {
                onSuccess: () => {
                    setVerifyProcessing(false);
                    setVerifyNote('');
                },
                onError: () => setVerifyProcessing(false),
            }
        );
    }

    const showVerificationActions =
        capa.status === 'awaiting_verification' || capa.status === 'rejected';
    const showSubmitButton = capa.status === 'in_progress' || capa.status === 'rejected';

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Detail CAPA #{capa.id}
                </h2>
            }
        >
            <Head title="Detail CAPA" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                            Dashboard
                        </Link>
                        <span className="mx-2">/</span>
                        <Link href={route('spmi.capa')} className="text-indigo-600 hover:text-indigo-900">
                            CAPA
                        </Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">#{capa.id}</span>
                    </nav>

                    <div className="mb-4">
                        <Link
                            href={route('spmi.capa')}
                            className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar CAPA
                        </Link>
                    </div>

                    {/* ════════════════════════════════════════════════════════════════
                        Header Card
                        ════════════════════════════════════════════════════════════════ */}
                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                            <div className="min-w-0 flex-1">
                                <h1 className="text-xl font-bold text-gray-900">
                                    CAPA #{capa.id}
                                    {capa.auditMutu && (
                                        <span className="ml-2 text-base font-normal text-gray-500">
                                            — {capa.auditMutu.judul_audit}
                                        </span>
                                    )}
                                </h1>
                                <div className="mt-2 flex items-center gap-3">
                                    <StatusBadge status={capa.status} workflowType="capa" size="md" />
                                    {capa.auditMutu?.severity && (
                                        <span className="text-xs text-gray-500">
                                            Severity temuan: {capa.auditMutu.severity}
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex flex-wrap items-center gap-2">
                                {showSubmitButton && (
                                    <button
                                        onClick={handleSubmitVerification}
                                        disabled={submitProcessing}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-purple-50 px-3 py-2 text-sm font-medium text-purple-700 hover:bg-purple-100 disabled:opacity-50"
                                    >
                                        <Send className="h-4 w-4" />
                                        {submitProcessing ? 'Mengirim...' : 'Ajukan Verifikasi'}
                                    </button>
                                )}
                                {capa.auditMutu && (
                                    <Link
                                        href={route('spmi.audit.show', capa.audit_mutu_id)}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        <FileText className="h-4 w-4" />
                                        Lihat Audit
                                    </Link>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                            {/* Audit Terkait */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                    <FileText className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Audit Terkait
                                    </p>
                                    {capa.auditMutu ? (
                                        <Link
                                            href={route('spmi.audit.show', capa.audit_mutu_id)}
                                            className="text-sm font-semibold text-indigo-600 hover:text-indigo-900 hover:underline"
                                        >
                                            {capa.auditMutu.judul_audit}
                                        </Link>
                                    ) : (
                                        <p className="text-sm text-gray-500">-</p>
                                    )}
                                </div>
                            </div>

                            {/* Standar Mutu */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                                    <Shield className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Standar Mutu
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {capa.auditMutu?.standarMutu?.kode_standar
                                            ? `${capa.auditMutu.standarMutu.kode_standar} - ${capa.auditMutu.standarMutu.nama_standar}`
                                            : '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Prodi */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Program Studi
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {capa.auditMutu?.prodi?.nama_prodi || '-'}
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
                                        {capa.picUser?.name || '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Diverifikasi Oleh */}
                            {capa.verifiedBy && (
                                <div className="flex items-start gap-3">
                                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                        <CheckCircle2 className="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                            Diverifikasi Oleh
                                        </p>
                                        <p className="text-sm font-semibold text-gray-900">
                                            {capa.verifiedBy.name}
                                        </p>
                                        {capa.verified_at && (
                                            <p className="text-xs text-gray-400">
                                                {formatDate(capa.verified_at)}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Dibuat Pada */}
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600">
                                    <Clock className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Dibuat Pada
                                    </p>
                                    <p className="text-sm font-semibold text-gray-900">
                                        {formatDate(capa.created_at)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* ════════════════════════════════════════════════════════════════
                        Content Grid
                        ════════════════════════════════════════════════════════════════ */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="lg:col-span-2 space-y-6">
                            {/* Root Cause Analysis */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <AlertTriangle className="h-4 w-4 text-orange-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Root Cause Analysis
                                    </h3>
                                    {capa.root_cause_category && (
                                        <span className="ml-auto rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-semibold text-orange-700">
                                            {capa.root_cause_category}
                                        </span>
                                    )}
                                </div>
                                <div className="p-6">
                                    {capa.root_cause_analysis ? (
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {capa.root_cause_analysis}
                                        </p>
                                    ) : (
                                        <p className="text-sm italic text-gray-400">
                                            Belum ada analisis akar masalah.
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Corrective Action */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <CheckCircle2 className="h-4 w-4 text-green-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Corrective Action
                                    </h3>
                                    {capa.corrective_deadline && (
                                        <span className="ml-auto text-[10px] font-medium text-gray-400">
                                            Deadline: {formatDate(capa.corrective_deadline)}
                                        </span>
                                    )}
                                </div>
                                <div className="p-6">
                                    {capa.corrective_action ? (
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {capa.corrective_action}
                                        </p>
                                    ) : (
                                        <p className="text-sm italic text-gray-400">
                                            Belum ada tindakan korektif.
                                        </p>
                                    )}
                                    {capa.corrective_evidence_file && (
                                        <a
                                            href={`/storage/${capa.corrective_evidence_file}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50"
                                        >
                                            <Download className="h-3.5 w-3.5" />
                                            Download Bukti Korektif
                                        </a>
                                    )}
                                    {capa.corrective_completed_at && (
                                        <p className="mt-2 text-xs text-gray-400">
                                            Selesai pada: {formatDate(capa.corrective_completed_at)}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Preventive Action */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                    <Shield className="h-4 w-4 text-blue-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Preventive Action
                                    </h3>
                                    {capa.preventive_deadline && (
                                        <span className="ml-auto text-[10px] font-medium text-gray-400">
                                            Deadline: {formatDate(capa.preventive_deadline)}
                                        </span>
                                    )}
                                </div>
                                <div className="p-6">
                                    {capa.preventive_action ? (
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {capa.preventive_action}
                                        </p>
                                    ) : (
                                        <p className="text-sm italic text-gray-400">
                                            Belum ada tindakan preventif.
                                        </p>
                                    )}
                                    {capa.preventive_evidence_file && (
                                        <a
                                            href={`/storage/${capa.preventive_evidence_file}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50"
                                        >
                                            <Download className="h-3.5 w-3.5" />
                                            Download Bukti Preventif
                                        </a>
                                    )}
                                    {capa.preventive_completed_at && (
                                        <p className="mt-2 text-xs text-gray-400">
                                            Selesai pada: {formatDate(capa.preventive_completed_at)}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Verification Note */}
                            {capa.verification_note && (
                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                        <FileText className="h-4 w-4 text-purple-500" />
                                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                            Catatan Verifikasi
                                        </h3>
                                    </div>
                                    <div className="p-6">
                                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                            {capa.verification_note}
                                        </p>
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
                                    <CapaTimeline histories={timeline} />
                                </div>
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                            Right Column: Verification Panel
                            ════════════════════════════════════════════════════════════════ */}
                        <div className="space-y-6">
                            {/* Verification Panel */}
                            {showVerificationActions && (
                                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div className="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                                        <Shield className="h-4 w-4 text-indigo-500" />
                                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                            Verifikasi
                                        </h3>
                                    </div>
                                    <div className="p-6">
                                        <div className="mb-4">
                                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                                Catatan Verifikasi <span className="text-red-500">*</span>
                                            </label>
                                            <textarea
                                                value={verifyNote}
                                                onChange={(e) => setVerifyNote(e.target.value)}
                                                rows={4}
                                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Masukkan catatan verifikasi..."
                                            />
                                        </div>
                                        <div className="flex gap-2">
                                            <button
                                                onClick={() => handleVerify(true)}
                                                disabled={!verifyNote || verifyProcessing}
                                                className="flex-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
                                            >
                                                {verifyProcessing ? 'Memproses...' : 'Setujui'}
                                            </button>
                                            <button
                                                onClick={() => handleVerify(false)}
                                                disabled={!verifyNote || verifyProcessing}
                                                className="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                                            >
                                                {verifyProcessing ? 'Memproses...' : 'Tolak'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* CAPA Info */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div className="border-b border-gray-100 px-6 py-4">
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Informasi CAPA
                                    </h3>
                                </div>
                                <div className="divide-y divide-gray-50">
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">Status</span>
                                        <StatusBadge status={capa.status} workflowType="capa" size="sm" />
                                    </div>
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">Kategori Root Cause</span>
                                        <span className="text-xs font-medium text-gray-700">
                                            {capa.root_cause_category || '-'}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">PIC</span>
                                        <span className="text-xs font-medium text-gray-700">
                                            {capa.picUser?.name || '-'}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">Deadline Korektif</span>
                                        <span className="text-xs font-medium text-gray-700">
                                            {formatDate(capa.corrective_deadline)}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between px-6 py-3">
                                        <span className="text-xs text-gray-500">Deadline Preventif</span>
                                        <span className="text-xs font-medium text-gray-700">
                                            {formatDate(capa.preventive_deadline)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
