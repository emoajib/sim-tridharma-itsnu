import {
    PlusCircle,
    Edit3,
    ArrowRightCircle,
    CheckCircle2,
    XCircle,
    UserCheck,
} from 'lucide-react';

interface TimelineItem {
    date: string;
    action: string;
    user: string;
    description?: string;
    type: 'created' | 'updated' | 'transition' | 'verified' | 'rejected' | 'assigned';
}

interface TimelineProps {
    items: TimelineItem[];
}

const typeConfig: Record<string, { icon: React.ReactNode; dotColor: string; bgColor: string }> = {
    created: {
        icon: <PlusCircle className="h-4 w-4" />,
        dotColor: 'border-green-500',
        bgColor: 'bg-green-100 text-green-600',
    },
    updated: {
        icon: <Edit3 className="h-4 w-4" />,
        dotColor: 'border-blue-500',
        bgColor: 'bg-blue-100 text-blue-600',
    },
    transition: {
        icon: <ArrowRightCircle className="h-4 w-4" />,
        dotColor: 'border-yellow-500',
        bgColor: 'bg-yellow-100 text-yellow-600',
    },
    verified: {
        icon: <CheckCircle2 className="h-4 w-4" />,
        dotColor: 'border-emerald-500',
        bgColor: 'bg-emerald-100 text-emerald-600',
    },
    rejected: {
        icon: <XCircle className="h-4 w-4" />,
        dotColor: 'border-red-500',
        bgColor: 'bg-red-100 text-red-600',
    },
    assigned: {
        icon: <UserCheck className="h-4 w-4" />,
        dotColor: 'border-indigo-500',
        bgColor: 'bg-indigo-100 text-indigo-600',
    },
};

function formatDate(dateStr: string): string {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
}

export default function Timeline({ items }: TimelineProps) {
    if (items.length === 0) {
        return (
            <div className="py-8 text-center text-sm text-gray-400">
                Belum ada aktivitas.
            </div>
        );
    }

    return (
        <div className="relative pl-8">
            {/* Vertical line */}
            <div className="absolute left-[15px] top-2 bottom-2 w-0.5 bg-gray-200" />

            <div className="space-y-6">
                {items.map((item, idx) => {
                    const config = typeConfig[item.type] || typeConfig.transition;

                    return (
                        <div key={idx} className="relative">
                            {/* Dot */}
                            <div
                                className={`absolute -left-7 flex h-8 w-8 items-center justify-center rounded-full border-2 bg-white ${config.dotColor} ${config.bgColor}`}
                            >
                                {config.icon}
                            </div>

                            {/* Content */}
                            <div className="rounded-lg border border-gray-100 bg-white p-4 shadow-sm">
                                <div className="flex items-start justify-between gap-4">
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-semibold text-gray-900">
                                            {item.action}
                                        </p>
                                        {item.description && (
                                            <p className="mt-1 text-sm text-gray-500">
                                                {item.description}
                                            </p>
                                        )}
                                        <p className="mt-1.5 text-xs font-medium text-gray-400">
                                            oleh {item.user}
                                        </p>
                                    </div>
                                    <time className="shrink-0 text-xs font-medium text-gray-400">
                                        {formatDate(item.date)}
                                    </time>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
