import { AlertTriangle, Clock, AlertCircle, Info } from 'lucide-react';

interface EarlyWarningItem {
    type: 'kritis' | 'overdue' | 'mendekat' | 'info';
    message: string;
    prodi?: string;
    days?: number;
}

interface EarlyWarningProps {
    warnings: EarlyWarningItem[];
}

const typeConfig: Record<string, { icon: React.ReactNode; bgClass: string; borderClass: string; label: string }> = {
    kritis: {
        icon: <AlertTriangle className="h-5 w-5 text-red-600" />,
        bgClass: 'bg-red-50',
        borderClass: 'border-red-500',
        label: 'Kritis',
    },
    overdue: {
        icon: <Clock className="h-5 w-5 text-yellow-600" />,
        bgClass: 'bg-yellow-50',
        borderClass: 'border-yellow-500',
        label: 'Overdue',
    },
    mendekat: {
        icon: <AlertCircle className="h-5 w-5 text-blue-600" />,
        bgClass: 'bg-blue-50',
        borderClass: 'border-blue-500',
        label: 'Mendekati Deadline',
    },
    info: {
        icon: <Info className="h-5 w-5 text-gray-600" />,
        bgClass: 'bg-gray-50',
        borderClass: 'border-gray-500',
        label: 'Informasi',
    },
};

export default function EarlyWarning({ warnings }: EarlyWarningProps) {
    if (warnings.length === 0) {
        return (
            <div className="rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle2 className="h-6 w-6 text-green-600" />
                </div>
                <p className="mt-3 text-sm font-medium text-gray-600">
                    Tidak ada peringatan. Semua berjalan normal.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {warnings.map((warning, idx) => {
                const config = typeConfig[warning.type] || typeConfig.info;

                return (
                    <div
                        key={idx}
                        className={`flex items-start gap-4 rounded-xl border-l-4 p-4 shadow-sm transition-all hover:shadow-md ${config.bgClass} ${config.borderClass}`}
                    >
                        <div className="mt-0.5 shrink-0">{config.icon}</div>
                        <div className="min-w-0 flex-1">
                            <p className="text-sm font-semibold text-gray-900">
                                {warning.message}
                            </p>
                            <div className="mt-1 flex flex-wrap items-center gap-2">
                                {warning.prodi && (
                                    <span className="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 shadow-sm">
                                        {warning.prodi}
                                    </span>
                                )}
                                {warning.days !== undefined && (
                                    <span className="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold text-gray-500 shadow-sm">
                                        {warning.days} hari
                                    </span>
                                )}
                                <span className="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-sm"
                                    style={{ color: warning.type === 'kritis' ? '#dc2626' : warning.type === 'overdue' ? '#eab308' : '#3b82f6' }}
                                >
                                    {config.label}
                                </span>
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

function CheckCircle2({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth="1.5"
            stroke="currentColor"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </svg>
    );
}
