interface SimilarityBadgeProps {
    score: number;
    size?: 'sm' | 'md';
}

export default function SimilarityBadge({ score, size = 'sm' }: SimilarityBadgeProps) {
    const colorClass = score >= 0.8
        ? 'bg-green-100 text-green-800'
        : score >= 0.6
            ? 'bg-yellow-100 text-yellow-800'
            : 'bg-red-100 text-red-800';

    const sizeClass = size === 'sm' ? 'px-2 text-xs' : 'px-3 text-sm';

    return (
        <span className={`inline-flex rounded-full font-semibold leading-5 ${colorClass} ${sizeClass}`}>
            {(score * 100).toFixed(0)}%
        </span>
    );
}
