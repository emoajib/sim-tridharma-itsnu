import { LineChart as RechartsLineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

interface TrendData {
    tahun: string;
    publikasi?: number;
    pkm?: number;
    penelitian?: number;
    kerjasama?: number;
}

interface Props {
    data: TrendData[];
    title?: string;
    showPublication?: boolean;
    showPkm?: boolean;
    showPenelitian?: boolean;
    showKerjasama?: boolean;
    className?: string;
}

const defaultData = [
    { tahun: '2020', publikasi: 12, pkm: 8, penelitian: 5, kerjasama: 3 },
    { tahun: '2021', publikasi: 15, pkm: 10, penelitian: 7, kerjasama: 4 },
    { tahun: '2022', publikasi: 18, pkm: 12, penelitian: 8, kerjasama: 5 },
    { tahun: '2023', publikasi: 22, pkm: 14, penelitian: 10, kerjasama: 6 },
    { tahun: '2024', publikasi: 28, pkm: 16, penelitian: 12, kerjasama: 8 },
];

const COLORS = {
    publikasi: '#6366f1',
    pkm: '#10b981',
    penelitian: '#f59e0b',
    kerjasama: '#ec4899',
};

export default function LineChartTrend({ 
    data, 
    title = 'Tren Kinerja Tridharma',
    showPublication = true,
    showPkm = true,
    showPenelitian = false,
    showKerjasama = false,
    className = '' 
}: Props) {
    const chartData = (data && data.length > 0) ? data : defaultData;

    const lines = [];
    if (showPublication) lines.push(<Line key="publikasi" type="monotone" dataKey="publikasi" stroke={COLORS.publikasi} strokeWidth={2} name="Publikasi" dot={{ r: 4 }} />);
    if (showPkm) lines.push(<Line key="pkm" type="monotone" dataKey="pkm" stroke={COLORS.pkm} strokeWidth={2} name="PkM" dot={{ r: 4 }} />);
    if (showPenelitian) lines.push(<Line key="penelitian" type="monotone" dataKey="penelitian" stroke={COLORS.penelitian} strokeWidth={2} name="Penelitian" dot={{ r: 4 }} />);
    if (showKerjasama) lines.push(<Line key="kerjasama" type="monotone" dataKey="kerjasama" stroke={COLORS.kerjasama} strokeWidth={2} name="Kerjasama" dot={{ r: 4 }} />);

    return (
        <div className={`bg-white rounded-lg p-4 shadow-sm ${className}`}>
            <div className="mb-4 flex items-center gap-2">
                <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <h3 className="font-semibold text-gray-900">{title}</h3>
            </div>

            <div className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                    <RechartsLineChart data={chartData} margin={{ top: 5, right: 20, left: 0, bottom: 5 }}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="tahun" />
                        <YAxis />
                        <Tooltip 
                            contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}
                        />
                        <Legend />
                        {lines}
                    </RechartsLineChart>
                </ResponsiveContainer>
            </div>

            <div className="mt-4 text-xs text-gray-500 text-center">
                Klik pada titik data untuk melihat detail per tahun
            </div>
        </div>
    );
}