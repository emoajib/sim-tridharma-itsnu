import { Link } from '@inertiajs/react';

interface PeringatanStats {
    critical: number;
    warning: number;
    info: number;
    unread: number;
    total: number;
}

interface Props {
    peringatanStats?: PeringatanStats;
}

export default function PeringatanTab({ peringatanStats }: Props) {
    return (
        <div className="mb-12">
            <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Peringatan & Notifikasi</h3>
            <div className="rounded-xl bg-white p-8 shadow-sm border border-gray-100 text-center">
                {peringatanStats ? (
                    <div className="flex items-center justify-center gap-8">
                        <div className="text-center">
                            <div className="text-5xl font-black text-red-600">{peringatanStats.critical}</div>
                            <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Critical</div>
                        </div>
                        <div className="text-center">
                            <div className="text-5xl font-black text-amber-600">{peringatanStats.warning}</div>
                            <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Warning</div>
                        </div>
                        <div className="text-center">
                            <div className="text-5xl font-black text-blue-600">{peringatanStats.info}</div>
                            <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Info</div>
                        </div>
                        <div className="text-center">
                            <div className="text-5xl font-black text-gray-600">{peringatanStats.unread}</div>
                            <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Unread</div>
                        </div>
                    </div>
                ) : (
                    <p className="text-gray-400 italic font-medium">Belum ada data peringatan.</p>
                )}
                <div className="mt-6">
                    <Link href={route('peringatan')} className="inline-flex px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                        Lihat Detail Peringatan →
                    </Link>
                </div>
            </div>
        </div>
    );
}
