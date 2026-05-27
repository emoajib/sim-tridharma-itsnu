import { Link, router } from '@inertiajs/react';
import { Eye, Edit3, CheckCircle, XCircle, Send, Loader2 } from 'lucide-react';
import StatusBadge from '@/Components/SPMI/StatusBadge';

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

interface Props {
    capa: PaginatedData<CapaItem>;
    openEdit: (item: CapaItem) => void;
    handleSubmitVerification: (item: CapaItem) => void;
    setVerifyTarget: (item: CapaItem) => void;
    setVerifyAction: (action: 'approved' | 'rejected') => void;
    setVerifyNote: (note: string) => void;
    submitTarget: CapaItem | null;
    submitProcessing: boolean;
    countdownLabel: (deadline: string | null) => { text: string; isOverdue: boolean } | null;
}

export default function CapaTable({
    capa,
    openEdit,
    handleSubmitVerification,
    setVerifyTarget,
    setVerifyAction,
    setVerifyNote,
    submitTarget,
    submitProcessing,
    countdownLabel,
}: Props) {
    return (
        <>
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Judul Temuan</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Standar Mutu</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">PIC</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Deadline</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 bg-white">
                        {capa.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="px-6 py-12 text-center text-sm text-gray-500">Tidak ada data CAPA.</td>
                            </tr>
                        ) : (
                            capa.data.map((item) => {
                                const countdown = countdownLabel(item.corrective_deadline);
                                return (
                                    <tr key={item.id} className="hover:bg-gray-50">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm">
                                            <Link href={route('spmi.capa.show', item.id)} className="font-medium text-indigo-600 hover:text-indigo-900 hover:underline">
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
                                                <span className={countdown.isOverdue ? 'font-semibold text-red-600' : 'text-gray-600'}>
                                                    {countdown.text}
                                                </span>
                                            ) : (
                                                <span className="text-gray-400">-</span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4">
                                            <StatusBadge status={item.status} workflowType="capa" size="sm" />
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm">
                                            <div className="flex items-center gap-1">
                                                <Link href={route('spmi.capa.show', item.id)} className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600" title="Lihat Detail">
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                                <button onClick={() => openEdit(item)} className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600" title="Edit">
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
                                                            onClick={() => { setVerifyTarget(item); setVerifyAction('approved'); setVerifyNote(''); }}
                                                            className="rounded p-1.5 text-green-500 hover:bg-green-50 hover:text-green-600"
                                                            title="Setujui"
                                                        >
                                                            <CheckCircle className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            onClick={() => { setVerifyTarget(item); setVerifyAction('rejected'); setVerifyNote(''); }}
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
                                onClick={() => { if (link.url) router.get(link.url, {}, { preserveState: true, replace: true }); }}
                                className={`rounded px-3 py-1 text-sm ${
                                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'
                                } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                </div>
            )}
        </>
    );
}
