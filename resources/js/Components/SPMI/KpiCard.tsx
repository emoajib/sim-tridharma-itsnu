import { Link } from '@inertiajs/react';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';

interface KpiCardProps {
    title: string;
    value: string | number;
    icon?: React.ReactNode;
    trend?: { value: number; direction: 'up' | 'down' | 'flat' };
    color?: 'blue' | 'green' | 'yellow' | 'red' | 'purple';
    link?: string;
}

const colorVariants: Record<string, { border: string; bg: string; iconBg: string }> = {
    blue: {
        border: 'border-l-blue-500',
        bg: 'bg-blue-50',
        iconBg: 'bg-blue-100 text-blue-600',
    },
    green: {
        border: 'border-l-green-500',
        bg: 'bg-green-50',
        iconBg: 'bg-green-100 text-green-600',
    },
    yellow: {
        border: 'border-l-yellow-500',
        bg: 'bg-yellow-50',
        iconBg: 'bg-yellow-100 text-yellow-600',
    },
    red: {
        border: 'border-l-red-500',
        bg: 'bg-red-50',
        iconBg: 'bg-red-100 text-red-600',
    },
    purple: {
        border: 'border-l-purple-500',
        bg: 'bg-purple-50',
        iconBg: 'bg-purple-100 text-purple-600',
    },
};

function TrendIndicator({ trend }: { trend: { value: number; direction: 'up' | 'down' | 'flat' } }) {
    if (trend.direction === 'up') {
        return (
            <span className="inline-flex items-center gap-0.5 text-xs font-semibold text-green-600">
                <TrendingUp className="h-3 w-3" />
                {trend.value}%
            </span>
        );
    }
    if (trend.direction === 'down') {
        return (
            <span className="inline-flex items-center gap-0.5 text-xs font-semibold text-red-600">
                <TrendingDown className="h-3 w-3" />
                {trend.value}%
            </span>
        );
    }
    return (
        <span className="inline-flex items-center gap-0.5 text-xs font-semibold text-gray-400">
            <Minus className="h-3 w-3" />
            {trend.value}%
        </span>
    );
}

export default function KpiCard({ title, value, icon, trend, color = 'blue', link }: KpiCardProps) {
    const variants = colorVariants[color] || colorVariants.blue;

    const card = (
        <div
            className={`flex items-start gap-4 rounded-xl border border-gray-100 border-l-4 bg-white p-5 shadow-sm transition-all hover:shadow-md ${variants.border}`}
        >
            {icon && (
                <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${variants.iconBg}`}>
                    {icon}
                </div>
            )}
            <div className="min-w-0 flex-1">
                <p className="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    {title}
                </p>
                <p className="mt-1 text-2xl font-black text-gray-900 tabular-nums">
                    {value}
                </p>
                {trend && (
                    <div className="mt-1">
                        <TrendIndicator trend={trend} />
                    </div>
                )}
            </div>
        </div>
    );

    if (link) {
        return (
            <Link href={link} className="block transition-transform hover:-translate-y-0.5">
                {card}
            </Link>
        );
    }

    return card;
}
