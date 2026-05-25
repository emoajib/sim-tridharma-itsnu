interface SeverityBadgeProps {
    severity: 'ringan' | 'sedang' | 'berat' | 'kritis';
    size?: 'sm' | 'md';
}

const colorMap: Record<string, string> = {
    ringan: 'bg-green-100 text-green-800',
    sedang: 'bg-yellow-100 text-yellow-800',
    berat: 'bg-orange-100 text-orange-800',
    kritis: 'bg-red-100 text-red-800',
};

const labelMap: Record<string, string> = {
    ringan: 'Ringan',
    sedang: 'Sedang',
    berat: 'Berat',
    kritis: 'Kritis',
};

export default function SeverityBadge({ severity, size = 'sm' }: SeverityBadgeProps) {
    const sizeClasses = size === 'md' ? 'px-3 py-1 text-sm' : 'px-2 text-xs';

    return (
        <span
            className={`inline-flex items-center rounded-full font-semibold leading-5 ${sizeClasses} ${colorMap[severity] || 'bg-gray-100 text-gray-800'}`}
        >
            {labelMap[severity] || severity}
        </span>
    );
}
