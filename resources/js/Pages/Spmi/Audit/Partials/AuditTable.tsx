import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { CheckSquare, Square, Eye, Edit3, Trash2 } from 'lucide-react';
import SeverityBadge from '@/Components/SPMI/SeverityBadge';
import StatusBadge from '@/Components/SPMI/StatusBadge';
import WorkflowDropdown from '@/Components/SPMI/WorkflowDropdown';

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

interface AuditTableProps {
    audit: PaginatedData<AuditItem>;
    filters: { search: string; status: string; standar_mutu_id: string; severity: string; prodi_id: string; periode_id: string };
    onEdit: (item: AuditItem) => void;
    onDelete: (item: AuditItem) => void;
    onTransition: (item: AuditItem, toStatus: string) => void;
    selectedIds: number[];
    onSelectChange: (ids: number[]) => void;
}

function countdownLabel(deadline: string | null): { text: string; isOverdue: boolean; isUrgent: boolean } | null {
    if (!deadline) return null;
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diffTime = deadlineDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return { text: `Terlambat ${Math.abs(diffDays)} hari`, isOverdue: true, isUrgent: false };
    }
    if (diffDays === 0) {
        return { text: 'Hari ini', isOverdue: false, isUrgent: true };
    }
    if (diffDays <= 7) {
        return { text: `${diffDays} hari lagi`, isOverdue: false, isUrgent: true };
    }
    return { text: `${diffDays} hari lagi`, isOverdue: false, isUrgent: false };
}

function getWorkflowTransitions(status: string): string[] {
    const flow: Record<string, string[]> = {
        draft: ['submitted'],
        submitted: ['assigned'],
        assigned: ['in_progress'],
        in_progress: ['awaiting_verification'],
        awaiting_verification: ['verified', 'rejected'],
        verified: ['closed'],
        closed: ['archived'],
        rejected: ['in_progress'],
        archived: [],
    };
    return flow[status] || [];
}

export default function AuditTable({
    audit,
    filters,
    onEdit,
    onDelete,
    onTransition,
    selectedIds,
    onSelectChange,
}: AuditTableProps) {
    const toggleSelect = (id: number) => {
        if (selectedIds.includes(id)) {
            onSelectChange(selectedIds.filter((sid) => sid !== id));
        } else {
            onSelectChange([...selectedIds, id]);
        }
    };

    const toggleSelectAll = () => {
        if (selectedIds.length === audit.data.length) {
            onSelectChange([]);
        } else {
            onSelectChange(audit.data.map((d) => d.id));
        }
    };

    const isAllSelected = audit.data.length > 0 && selectedIds.length === audit.data.length;

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        <th className="w-10 px-4 py-3 text-left">
                            <button onClick={toggleSelectAll} className="text-gray-400 hover:text-gray-600">
                                {isAllSelected ? (
                                    <CheckSquare className="h-4 w-4 text-indigo-600" />
                                ) : (
                                    <Square className="h-4 w-4" />
                                )}
                            </button>
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Judul Audit
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Prodi
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Standar Mutu
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Severity
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            PIC
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Deadline
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            CAPA
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 bg-white">
                    {audit.data.length === 0 ? (
                        <tr>
                            <td colSpan={10} className="px-6 py-12 text-center text-sm text-gray-500">
                                Tidak ada data audit.
                            </td>
                        </tr>
                    ) : (
                        audit.data.map((item) => {
                            const countdown = countdownLabel(item.deadline_tindak_lanjut);
                            const transitions = getWorkflowTransitions(item.status);
                            const severity = item.severity as 'ringan' | 'sedang' | 'berat' | 'kritis' | null;
                            const isKritis = item.severity === 'kritis';
                            const isBerat = item.severity === 'berat';
                            const isOverdue = countdown?.isOverdue ?? false;

                            return (
                                <tr
                                    key={item.id}
                                    className={`hover:bg-gray-50 transition-colors ${
                                        isKritis ? 'bg-red-50' : isBerat ? 'bg-orange-50' : ''
                                    }`}
                                >
                                    <td className="px-4 py-4">
                                        <button
                                            onClick={() => toggleSelect(item.id)}
                                            className="text-gray-400 hover:text-gray-600"
                                        >
                                            {selectedIds.includes(item.id) ? (
                                                <CheckSquare className="h-4 w-4 text-indigo-600" />
                                            ) : (
                                                <Square className="h-4 w-4" />
                                            )}
                                        </button>
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                        <Link
                                            href={route('spmi.audit.show', item.id)}
                                            className="font-medium text-indigo-600 hover:text-indigo-900 hover:underline"
                                        >
                                            {item.judul_audit}
                                        </Link>
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {item.prodi?.nama_prodi || '-'}
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4">
                                        {item.standarMutu ? (
                                            <span className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">
                                                {item.standarMutu.kode_standar}
                                            </span>
                                        ) : (
                                            <span className="text-xs text-gray-400">-</span>
                                        )}
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4">
                                        {severity ? (
                                            <SeverityBadge severity={severity} size="sm" />
                                        ) : (
                                            <span className="text-xs text-gray-400">-</span>
                                        )}
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4">
                                        <StatusBadge status={item.status} workflowType="audit" size="sm" />
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
                                                        : countdown.isUrgent
                                                          ? 'font-semibold text-orange-600'
                                                          : 'text-gray-600'
                                                }
                                            >
                                                {countdown.text}
                                            </span>
                                        ) : (
                                            <span className="text-gray-400">-</span>
                                        )}
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                        {item.capas && item.capas.length > 0 ? (
                                            <Link
                                                href={route('spmi.capa.show', item.capas[0].id)}
                                                className="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-900 hover:underline"
                                            >
                                                <StatusBadge status={item.capas[0].status} workflowType="capa" size="sm" />
                                            </Link>
                                        ) : (
                                            <span className="text-xs text-gray-400">-</span>
                                        )}
                                    </td>
                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                        <div className="flex items-center gap-1">
                                            <button
                                                onClick={() => onEdit(item)}
                                                disabled={item.is_locked}
                                                className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40"
                                                title="Edit"
                                            >
                                                <Edit3 className="h-4 w-4" />
                                            </button>
                                            {!item.is_locked && transitions.length > 0 && (
                                                <WorkflowDropdown
                                                    currentStatus={item.status}
                                                    workflowType="audit"
                                                    onTransition={(toStatus) => onTransition(item, toStatus)}
                                                    transitions={transitions}
                                                />
                                            )}
                                            <button
                                                onClick={() => onDelete(item)}
                                                disabled={item.is_locked}
                                                className="rounded p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40"
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

            {audit.last_page > 1 && (
                <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                    <div className="text-sm text-gray-700">
                        Menampilkan {audit.from} - {audit.to} dari {audit.total}
                    </div>
                    <div className="flex gap-1">
                        {audit.links.map((link, i) => (
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
    );
}
