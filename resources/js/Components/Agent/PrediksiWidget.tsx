import { useState } from 'react';
import { router } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, ReferenceLine, Cell } from 'recharts';

interface Props {
    skor_prediksi?: number;
    confidence_interval?: [number, number];
    prob_unggul?: number;
    prob_baik_sekali?: number;
    prob_baik?: number;
    prodi_name?: string;
    last_updated?: string;
    showRunButton?: boolean;
}

const getSkorColor = (skor: number) => {
    if (skor >= 3.5) return '#16a34a';
    if (skor >= 2.8) return '#3b82f6';
    if (skor >= 2.0) return '#eab308';
    return '#dc2626';
};

const getSkorLabel = (skor: number) => {
    if (skor >= 3.5) return 'UNGGUL';
    if (skor >= 2.8) return 'BAIK SEKALI';
    if (skor >= 2.0) return 'BAIK';
    return 'TERAKREDITASI';
};

export default function PrediksiWidget({
    skor_prediksi = 0,
    confidence_interval = [0, 0],
    prob_unggul = 0,
    prob_baik_sekali = 0,
    prob_baik = 0,
    prodi_name = '',
    last_updated = '',
    showRunButton = true,
}: Props) {
    const [loading, setLoading] = useState(false);

    const chartData = [
        { name: 'Skor Saat Ini', value: skor_prediksi, fill: getSkorColor(skor_prediksi) },
        { name: 'Target', value: 4.0, fill: '#9ca3af' },
    ];

    const runPrediction = () => {
        setLoading(true);
        router.post('/api/agents/prediksi/run', {
            prodi_id: 1,
            periode_id: 1,
        }, {
            onFinish: () => setLoading(false),
        });
    };

    return (
        <div className="rounded-lg bg-white p-4 shadow-sm">
            <div className="mb-4 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 className="font-semibold text-gray-900">Prediksi Skor Akreditasi</h3>
                </div>
                {prodi_name && (
                    <span className="text-xs text-gray-500">{prodi_name}</span>
                )}
            </div>

            {/* Skor Display */}
            <div className="mb-4 text-center">
                <div className="text-4xl font-bold" style={{ color: getSkorColor(skor_prediksi) }}>
                    {skor_prediksi.toFixed(2)}
                    <span className="text-lg font-normal text-gray-400">/4.0</span>
                </div>
                <span className={`inline-block rounded-full px-3 py-1 text-sm font-semibold mt-2 ${
                    skor_prediksi >= 3.5 ? 'bg-green-100 text-green-700' :
                    skor_prediksi >= 2.8 ? 'bg-blue-100 text-blue-700' :
                    skor_prediksi >= 2.0 ? 'bg-yellow-100 text-yellow-700' :
                    'bg-red-100 text-red-700'
                }`}>
                    {getSkorLabel(skor_prediksi)}
                </span>
            </div>

            {/* Horizontal Bar Chart */}
            <div className="h-32 mb-4">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={chartData} layout="vertical" barCategoryGap="20%">
                        <XAxis type="number" domain={[0, 4]} hide />
                        <YAxis type="category" dataKey="name" tick={{ fontSize: 12 }} width={80} />
                        <Tooltip 
                            formatter={(value) => Number(value).toFixed(2)}
                            contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}
                        />
                        <ReferenceLine x={3.5} stroke="#16a34a" strokeDasharray="3 3" label={{ value: 'Unggul', position: 'top', fontSize: 10 }} />
                        <Bar dataKey="value" radius={[0, 4, 4, 0]}>
                            {chartData.map((entry, index) => (
                                <Cell key={`cell-${index}`} fill={entry.fill} />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>

            {/* Probabilitas */}
            <div className="mb-4 space-y-2">
                <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">Prob. Unggul</span>
                    <span className="font-bold text-green-600">{(prob_unggul * 100).toFixed(1)}%</span>
                </div>
                <div className="h-2 w-full rounded-full bg-gray-100">
                    <div className="h-2 rounded-full bg-green-500" style={{ width: `${prob_unggul * 100}%` }}></div>
                </div>

                <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">Prob. Baik Sekali</span>
                    <span className="font-bold text-blue-600">{(prob_baik_sekali * 100).toFixed(1)}%</span>
                </div>
                <div className="h-2 w-full rounded-full bg-gray-100">
                    <div className="h-2 rounded-full bg-blue-500" style={{ width: `${prob_baik_sekali * 100}%` }}></div>
                </div>

                <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">Prob. Baik</span>
                    <span className="font-bold text-yellow-600">{(prob_baik * 100).toFixed(1)}%</span>
                </div>
                <div className="h-2 w-full rounded-full bg-gray-100">
                    <div className="h-2 rounded-full bg-yellow-500" style={{ width: `${prob_baik * 100}%` }}></div>
                </div>
            </div>

            {/* Footer */}
            <div className="flex items-center justify-between pt-3 border-t">
                {last_updated && (
                    <span className="text-xs text-gray-400">
                        Update: {new Date(last_updated).toLocaleDateString('id-ID')}
                    </span>
                )}
                {showRunButton && (
                    <button
                        onClick={runPrediction}
                        disabled={loading}
                        className="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {loading ? 'Menjalankan...' : 'Jalankan'}
                    </button>
                )}
            </div>
        </div>
    );
}