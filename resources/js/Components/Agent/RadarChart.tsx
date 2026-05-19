import { Radar, RadarChart as RC, PolarGrid, PolarAngleAxis, PolarRadiusAxis, Tooltip, ResponsiveContainer } from 'recharts';

interface KriteriaItem {
    kode: string;
    nama: string;
    skor: number;
    target: number;
}

interface Props {
    data: KriteriaItem[];
    title?: string;
    showTarget?: boolean;
    onSelectKriteria?: (kriteria: KriteriaItem) => void;
}

const defaultData = [
    { kode: 'C1', nama: 'Visi & Misi', skor: 85, target: 100 },
    { kode: 'C2', nama: 'Tata Kelola', skor: 78, target: 100 },
    { kode: 'C3', nama: 'Mahasiswa', skor: 90, target: 100 },
    { kode: 'C4', nama: 'Kurikulum', skor: 72, target: 100 },
    { kode: 'C5', nama: 'Sarpras', skor: 65, target: 100 },
    { kode: 'C6', nama: 'Keuangan', skor: 80, target: 100 },
    { kode: 'C7', nama: 'Pendidikan', skor: 88, target: 100 },
    { kode: 'C8', nama: 'Penelitian', skor: 75, target: 100 },
    { kode: 'C9', nama: 'PKM', skor: 70, target: 100 },
];

export default function RadarChart({ data, title = 'Capaian per Kriteria (C1-C9)', showTarget = true, onSelectKriteria }: Props) {
    const chartData = (data && data.length > 0) ? data : defaultData;

    const terpenuhi = chartData.filter(d => (d.skor / d.target) * 100 >= 100).length;
    const hampir = chartData.filter(d => {
        const pct = (d.skor / d.target) * 100;
        return pct >= 60 && pct < 100;
    }).length;
    const kurang = chartData.filter(d => (d.skor / d.target) * 100 < 60).length;

    return (
        <div className="rounded-lg bg-white p-4 shadow-sm">
            <div className="mb-4 flex items-center gap-2">
                <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <h3 className="font-semibold text-gray-900">{title}</h3>
            </div>

            <div className="mb-4 flex flex-wrap gap-3 text-xs">
                <div className="flex items-center gap-1">
                    <span className="h-3 w-3 rounded-full bg-green-500"></span>
                    <span className="text-gray-600">Terpenuhi</span>
                </div>
                <div className="flex items-center gap-1">
                    <span className="h-3 w-3 rounded-full bg-yellow-500"></span>
                    <span className="text-gray-600">Hampir</span>
                </div>
                <div className="flex items-center gap-1">
                    <span className="h-3 w-3 rounded-full bg-red-500"></span>
                    <span className="text-gray-600">Kurang</span>
                </div>
            </div>

            <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                    <RC data={chartData}>
                        <PolarGrid stroke="#e5e7eb" />
                        <PolarAngleAxis dataKey="kode" tick={{ fontSize: 11 }} />
                        <PolarRadiusAxis angle={30} domain={[0, 100]} tick={false} />
                        <Tooltip 
                            formatter={(value: any) => `${Number(value).toFixed(1)}%`}
                            contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}
                        />
                        <Radar
                            name="Capaian"
                            dataKey="skor"
                            stroke="#6366f1"
                            fill="#6366f1"
                            fillOpacity={0.3}
                        />
                    </RC>
                </ResponsiveContainer>
            </div>

            <div className="mt-4 grid grid-cols-3 gap-2 border-t pt-4 text-center">
                <div>
                    <div className="text-lg font-bold text-green-600">{terpenuhi}</div>
                    <div className="text-xs text-gray-500">Terpenuhi</div>
                </div>
                <div>
                    <div className="text-lg font-bold text-yellow-600">{hampir}</div>
                    <div className="text-xs text-gray-500">Hampir</div>
                </div>
                <div>
                    <div className="text-lg font-bold text-red-600">{kurang}</div>
                    <div className="text-xs text-gray-500">Kurang</div>
                </div>
            </div>

            {onSelectKriteria && (
                <div className="mt-4 pt-4 border-t">
                    <div className="text-xs text-gray-500 mb-2">Klik untuk lihat detail:</div>
                    <div className="flex flex-wrap gap-1">
                        {chartData.map((item) => (
                            <button
                                key={item.kode}
                                onClick={() => onSelectKriteria(item)}
                                className={`px-2 py-1 text-xs rounded transition-colors ${
                                    (item.skor / item.target) * 100 >= 100
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                        : (item.skor / item.target) * 100 >= 60
                                        ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                        : 'bg-red-100 text-red-700 hover:bg-red-200'
                                }`}
                            >
                                {item.kode}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}