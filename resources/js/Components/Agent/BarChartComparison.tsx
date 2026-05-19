import { BarChart as RechartsBarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

interface KriteriaItem {
    kode: string;
    nama: string;
    skor: number;
    target: number;
}

interface Props {
    data: KriteriaItem[];
    title?: string;
    className?: string;
}

const defaultData = [
    { kode: 'C1', nama: 'Visi & Misi', skor: 85, target: 85 },
    { kode: 'C2', nama: 'Tata Kelola', skor: 75, target: 80 },
    { kode: 'C3', nama: 'Mahasiswa', skor: 90, target: 85 },
    { kode: 'C4', nama: 'Kurikulum', skor: 72, target: 75 },
    { kode: 'C5', nama: 'Sarpras', skor: 65, target: 80 },
    { kode: 'C6', nama: 'Keuangan', skor: 80, target: 80 },
    { kode: 'C7', nama: 'Pendidikan', skor: 88, target: 85 },
    { kode: 'C8', nama: 'Penelitian', skor: 75, target: 80 },
    { kode: 'C9', nama: 'PKM', skor: 70, target: 75 },
];

export default function BarChartComparison({ 
    data, 
    title = 'Capaian vs Target per Kriteria',
    className = '' 
}: Props) {
    const chartData = (data && data.length > 0) ? data : defaultData;

    return (
        <div className={`bg-white rounded-lg p-4 shadow-sm ${className}`}>
            <div className="mb-4 flex items-center gap-2">
                <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 className="font-semibold text-gray-900">{title}</h3>
            </div>

            <div className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                    <RechartsBarChart data={chartData} layout="vertical" margin={{ left: 20, right: 20 }}>
                        <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                        <XAxis type="number" domain={[0, 100]} tickFormatter={(v) => `${v}%`} />
                        <YAxis type="category" dataKey="kode" width={40} />
                        <Tooltip 
                            formatter={(value: any) => `${Number(value).toFixed(1)}%`}
                            contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}
                        />
                        <Legend 
                            wrapperStyle={{ paddingTop: '10px' }}
                            formatter={(value) => value === 'skor' ? 'Capaian' : 'Target'}
                        />
                        <Bar dataKey="target" fill="#e5e7eb" name="target" radius={[0, 4, 4, 0]} />
                        <Bar dataKey="skor" fill="#6366f1" name="skor" radius={[0, 4, 4, 0]} />
                    </RechartsBarChart>
                </ResponsiveContainer>
            </div>

            <div className="mt-4 text-xs text-gray-500 text-center">
                Klik pada bar untuk melihat detail bukti dan indikator
            </div>
        </div>
    );
}