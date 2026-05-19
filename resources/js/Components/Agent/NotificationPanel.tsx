import { useState } from 'react';
import { useAgentNotifications } from '@/hooks/useAgentNotifications';

interface Props {
    className?: string;
}

export default function NotificationPanel({ className = '' }: Props) {
    const { predictions, warnings, generations, isPolling, startPolling, stopPolling } = useAgentNotifications(15000);
    const [isOpen, setIsOpen] = useState(false);
    const [activeTab, setActiveTab] = useState<'predictions' | 'warnings' | 'generations'>('predictions');

    const totalUnread = warnings.filter(w => !w.is_read).length;

    const getTingkatColor = (tingkat: string) => {
        switch (tingkat) {
            case 'critical': return 'bg-red-500';
            case 'warning': return 'bg-yellow-500';
            default: return 'bg-blue-500';
        }
    };

    const formatTime = (dateStr: string) => {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now.getTime() - date.getTime();
        const minutes = Math.floor(diff / 60000);
        if (minutes < 1) return 'Baru saja';
        if (minutes < 60) return `${minutes}m lalu`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}j lalu`;
        return date.toLocaleDateString('id-ID');
    };

    return (
        <div className={`relative ${className}`}>
            <button
                onClick={() => {
                    setIsOpen(!isOpen);
                    if (!isOpen && !isPolling) startPolling();
                }}
                className="relative p-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {totalUnread > 0 && (
                    <span className="absolute top-0 right-0 -mt-1 -mr-1 flex h-5 w-5">
                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span className="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-xs items-center justify-center">
                            {totalUnread > 9 ? '9+' : totalUnread}
                        </span>
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                    <div className="border-b border-gray-200">
                        <div className="flex">
                            <button
                                onClick={() => setActiveTab('predictions')}
                                className={`flex-1 px-4 py-3 text-sm font-medium ${activeTab === 'predictions' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'}`}
                            >
                                Prediksi ({predictions.length})
                            </button>
                            <button
                                onClick={() => setActiveTab('warnings')}
                                className={`flex-1 px-4 py-3 text-sm font-medium ${activeTab === 'warnings' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'}`}
                            >
                                Peringatan ({warnings.length})
                            </button>
                            <button
                                onClick={() => setActiveTab('generations')}
                                className={`flex-1 px-4 py-3 text-sm font-medium ${activeTab === 'generations' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'}`}
                            >
                                Generator ({generations.length})
                            </button>
                        </div>
                    </div>

                    <div className="max-h-96 overflow-y-auto">
                        {activeTab === 'predictions' && (
                            <div className="divide-y divide-gray-100">
                                {predictions.length === 0 ? (
                                    <p className="p-4 text-sm text-gray-500 text-center">Belum ada prediksi terbaru</p>
                                ) : (
                                    predictions.map(p => (
                                        <div key={p.id} className="p-4 hover:bg-gray-50">
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <p className="font-medium text-gray-900">{p.prodi?.nama_prodi || `Prodi #${p.prodi_id}`}</p>
                                                    <p className="text-sm text-gray-500 mt-1">
                                                        Skor: <span className="font-semibold text-indigo-600">{p.skor_prediksi}</span>
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    {p.probabilitas_unggul >= 50 && (
                                                        <span className="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">UNGGUL</span>
                                                    )}
                                                    {p.probabilitas_unggul < 50 && p.probabilitas_baik_sekali >= 50 && (
                                                        <span className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded">BAIK SEKALI</span>
                                                    )}
                                                    {p.probabilitas_unggul < 50 && p.probabilitas_baik_sekali < 50 && (
                                                        <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 rounded">BAIK</span>
                                                    )}
                                                </div>
                                            </div>
                                            <p className="text-xs text-gray-400 mt-2">{formatTime(p.created_at)}</p>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                        {activeTab === 'warnings' && (
                            <div className="divide-y divide-gray-100">
                                {warnings.length === 0 ? (
                                    <p className="p-4 text-sm text-gray-500 text-center">Tidak ada peringatan</p>
                                ) : (
                                    warnings.map(w => (
                                        <div key={w.id} className="p-4 hover:bg-gray-50">
                                            <div className="flex items-start gap-3">
                                                <span className={`w-2 h-2 rounded-full mt-2 ${getTingkatColor(w.tingkat)}`} />
                                                <div className="flex-1">
                                                    <p className="font-medium text-gray-900 text-sm">{w.jenis_peringatan}</p>
                                                    <p className="text-sm text-gray-600 mt-1">{w.pesan}</p>
                                                    <p className="text-xs text-gray-400 mt-2">{formatTime(w.created_at)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}

                        {activeTab === 'generations' && (
                            <div className="divide-y divide-gray-100">
                                {generations.length === 0 ? (
                                    <p className="p-4 text-sm text-gray-500 text-center">Belum ada dokumen digenerate</p>
                                ) : (
                                    generations.map(g => (
                                        <div key={g.id} className="p-4 hover:bg-gray-50">
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <p className="font-medium text-gray-900">{g.jenis_dokumen.toUpperCase()}</p>
                                                    <p className="text-sm text-gray-500">{g.prodi?.nama_prodi || `Prodi #${g.prodi_id}`}</p>
                                                </div>
                                                <span className={`px-2 py-1 text-xs font-medium rounded ${g.status === 'selesai' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`}>
                                                    {g.status}
                                                </span>
                                            </div>
                                            <p className="text-xs text-gray-400 mt-2">{formatTime(g.created_at)}</p>
                                        </div>
                                    ))
                                )}
                            </div>
                        )}
                    </div>

                    <div className="border-t border-gray-200 p-3 flex justify-between items-center">
                        <span className="text-xs text-gray-500">
                            {isPolling ? '● Polling aktif' : '○ Polling berhenti'}
                        </span>
                        <button
                            onClick={isPolling ? stopPolling : startPolling}
                            className="text-xs text-indigo-600 hover:text-indigo-700"
                        >
                            {isPolling ? 'Hentikan' : 'Mulai'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}