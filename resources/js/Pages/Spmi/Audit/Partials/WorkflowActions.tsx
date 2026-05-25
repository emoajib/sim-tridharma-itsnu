import { ArrowRightCircle } from 'lucide-react';
import StatusBadge from '@/Components/SPMI/StatusBadge';

interface WorkflowActionsProps {
    currentStatus: string;
    canTransition: (toStatus: string) => boolean;
    onTransition: (toStatus: string) => void;
    isLocked?: boolean;
}

const AUDIT_STATUS_FLOW: Record<string, string[]> = {
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

const TRANSITION_LABELS: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Ajukan',
    assigned: 'Tugaskan',
    in_progress: 'Proses',
    awaiting_verification: 'Ajukan Verifikasi',
    verified: 'Verifikasi',
    rejected: 'Tolak',
    closed: 'Tutup',
    archived: 'Arsipkan',
};

function capitalizeLabel(status: string): string {
    return status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function WorkflowActions({
    currentStatus,
    canTransition,
    onTransition,
    isLocked = false,
}: WorkflowActionsProps) {
    const transitions = AUDIT_STATUS_FLOW[currentStatus] || [];

    if (transitions.length === 0) {
        return (
            <div className="flex items-center gap-2">
                <span className="text-xs text-gray-400">Status akhir</span>
                <StatusBadge status={currentStatus} workflowType="audit" size="sm" />
            </div>
        );
    }

    return (
        <div className="flex flex-wrap items-center gap-2">
            <StatusBadge status={currentStatus} workflowType="audit" size="sm" />
            <ArrowRightCircle className="h-4 w-4 text-gray-400" />
            {transitions.map((toStatus) => {
                const allowed = canTransition(toStatus);
                return (
                    <button
                        key={toStatus}
                        type="button"
                        disabled={!allowed || isLocked}
                        onClick={() => onTransition(toStatus)}
                        className={`inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-all ${
                            !allowed || isLocked
                                ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                                : 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300'
                        }`}
                    >
                        {TRANSITION_LABELS[toStatus] || capitalizeLabel(toStatus)}
                    </button>
                );
            })}
        </div>
    );
}
