import { Link } from '@inertiajs/react';

interface Props {
    critical?: number;
    warning?: number;
    info?: number;
    unread?: number;
    showLabel?: boolean;
}

export default function PeringatanBadge({ critical = 0, warning = 0, info = 0, unread = 0, showLabel = true }: Props) {
    const total = critical + warning + info;

    if (total === 0) {
        return (
            <div className="flex items-center gap-2 text-sm text-gray-500">
                <svg className="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {showLabel && <span>Tidak ada peringatan</span>}
            </div>
        );
    }

    return (
        <Link
            href={route('peringatan')}
            className="flex items-center gap-2 rounded-lg bg-white px-3 py-2 shadow-sm transition hover:shadow-md"
        >
            <div className="relative">
                <svg className="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {unread > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {unread > 9 ? '9+' : unread}
                    </span>
                )}
            </div>
            <div className="flex items-center gap-2">
                {critical > 0 && (
                    <span className="flex items-center gap-1 text-sm">
                        <span className="h-2 w-2 rounded-full bg-red-500"></span>
                        <span className="font-medium text-red-700">{critical}</span>
                    </span>
                )}
                {warning > 0 && (
                    <span className="flex items-center gap-1 text-sm">
                        <span className="h-2 w-2 rounded-full bg-yellow-500"></span>
                        <span className="font-medium text-yellow-700">{warning}</span>
                    </span>
                )}
                {info > 0 && (
                    <span className="flex items-center gap-1 text-sm">
                        <span className="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span className="font-medium text-blue-700">{info}</span>
                    </span>
                )}
            </div>
            {showLabel && (
                <span className="text-xs text-gray-500">
                    {total} peringatan
                </span>
            )}
        </Link>
    );
}