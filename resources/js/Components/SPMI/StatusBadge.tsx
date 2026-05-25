interface StatusBadgeProps {
    status: string;
    workflowType: 'audit' | 'capa' | 'dokumen';
    size?: 'sm' | 'md';
}

const auditColorMap: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    assigned: 'bg-indigo-100 text-indigo-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    awaiting_verification: 'bg-purple-100 text-purple-800',
    verified: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    closed: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-gray-100 text-gray-800',
};

const capaColorMap: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    awaiting_verification: 'bg-purple-100 text-purple-800',
    verified: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    closed: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-gray-100 text-gray-800',
};

const dokumenColorMap: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    archived: 'bg-gray-100 text-gray-800',
};

function getColorMap(workflowType: string): Record<string, string> {
    switch (workflowType) {
        case 'audit':
            return auditColorMap;
        case 'capa':
            return capaColorMap;
        case 'dokumen':
            return dokumenColorMap;
        default:
            return auditColorMap;
    }
}

function capitalizeLabel(status: string): string {
    return status
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function StatusBadge({ status, workflowType, size = 'sm' }: StatusBadgeProps) {
    const colorMap = getColorMap(workflowType);
    const sizeClasses = size === 'md' ? 'px-3 py-1 text-sm' : 'px-2 text-xs';

    return (
        <span
            className={`inline-flex items-center rounded-full font-semibold leading-5 ${sizeClasses} ${colorMap[status] || 'bg-gray-100 text-gray-800'}`}
        >
            {capitalizeLabel(status)}
        </span>
    );
}
