import { Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    skor_prediksi: number;
    prob_unggul: number;
    prob_baik_sekali: number;
    prob_baik: number;
    last_updated?: string;
    showRunButton?: boolean;
    filters?: { periode_id?: number; instrumen_id?: number };
}

export default function PrediksiWidget({ skor_prediksi, prob_unggul, prob_baik_sekali, prob_baik, last_updated, showRunButton, filters }: Props) {
    const [running, setRunning] = useState(false);

    const runAgent = () => {
        setRunning(true);
        router.post(route('peringatan.run'), {
            ...filters
        }, {
            onFinish: () => setRunning(false),
        });
    };

    const getStatusColor = (skor: number) => {
        if (skor >= 3.61) return 'text-emerald-600';
        if (skor >= 3.01) return 'text-blue-600';
        return 'text-amber-600';
    };

    const getPredikat = (skor: number) => {
        if (skor >= 3.61) return 'UNGGUL';
        if (skor >= 3.01) return 'BAIK SEKALI';
        if (skor > 0) return 'BAIK';
        return 'TERAKREDITASI';
    };

    return (
        <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm h-full">
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider">Prediksi Skor Akreditasi</h3>
                    <p className="text-[10px] text-gray-400">Metode: Linear Trend TS-2 to TS</p>
                </div>
                {showRunButton && (
                    <button
                        onClick={runAgent}
                        disabled={running}
                        className={`rounded-lg px-4 py-2 text-xs font-bold text-white shadow-md transition-all ${
                            running ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 active:scale-95'
                        }`}
                    >
                        {running ? 'Menganalisis...' : '🚀 Jalankan AI'}
                    </button>
                )}
            </div>

            <div className="flex items-center gap-8">
                <div className="text-center flex-1">
                    <div className={`text-5xl font-black ${getStatusColor(skor_prediksi)}`}>
                        {skor_prediksi.toFixed(2)}<span className="text-sm text-gray-400 font-normal">/4.0</span>
                    </div>
                    <div className={`mt-2 inline-flex rounded-full px-3 py-1 text-xs font-black bg-gray-100 ${getStatusColor(skor_prediksi)}`}>
                        {getPredikat(skor_prediksi)}
                    </div>
                </div>

                <div className="w-px h-16 bg-gray-100"></div>

                <div className="flex-1 space-y-3">
                    <div className="flex items-center justify-between">
                        <span className="text-xs text-gray-500">Prob. Unggul</span>
                        <span className="text-xs font-bold text-emerald-600">{(prob_unggul * 100).toFixed(1)}%</span>
                    </div>
                    <div className="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div className="h-full bg-emerald-500" style={{ width: `${prob_unggul * 100}%` }}></div>
                    </div>
                    
                    <div className="flex items-center justify-between pt-1">
                        <span className="text-xs text-gray-500">Prob. Baik Sekali</span>
                        <span className="text-xs font-bold text-blue-600">{(prob_baik_sekali * 100).toFixed(1)}%</span>
                    </div>
                    <div className="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div className="h-full bg-blue-500" style={{ width: `${prob_baik_sekali * 100}%` }}></div>
                    </div>
                </div>
            </div>

            {last_updated && (
                <div className="mt-6 pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span className="text-[10px] text-gray-400 italic">Terakhir diupdate: {new Date(last_updated).toLocaleString('id-ID')}</span>
                    <Link href={route('peringatan')} className="text-[10px] font-bold text-indigo-600 hover:underline">Lihat Detail Analisis &rarr;</Link>
                </div>
            )}
        </div>
    );
}
