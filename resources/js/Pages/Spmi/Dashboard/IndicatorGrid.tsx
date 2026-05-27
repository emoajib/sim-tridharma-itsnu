import { BarChart3, PieChart as PieChartIcon, Star, Users } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts';

interface TemuanPerStandar {
    standar_id: number;
    kode_standar: string;
    nama_standar: string;
    count: number;
}

interface TemuanPerBulan {
    bulan: string;
    count: number;
}

interface SeverityDistribution {
    ringan: number;
    sedang: number;
    berat: number;
    kritis: number;
}

interface ChartData {
    temuan_per_standar: TemuanPerStandar[];
    temuan_per_bulan: TemuanPerBulan[];
    severity_distribution: SeverityDistribution;
}

interface RankingProdi {
    rank: number;
    nama_prodi: string;
    skor_mutu: number;
    total_temuan: number;
    kriteria?: string;
}

interface Props {
    charts: ChartData;
    formatBulan: (bulan: string) => string;
    severityToChartArray: (dist: SeverityDistribution) => Array<{ name: string; value: number; color: string }>;
    ChartTooltip: React.FC<any>;
    severityPieData: Array<{ name: string; value: number; color: string }>;
    totalTemuanForPie: number;
    ranking_prodi?: RankingProdi[];
}

export default function IndicatorGrid({ charts, formatBulan, ChartTooltip, severityPieData, totalTemuanForPie, ranking_prodi = [] }: Props) {
    return (
        <>
            {/* Charts Row */}
            <div className="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Bar Chart: Temuan per Bulan */}
                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center gap-2">
                        <BarChart3 className="h-4 w-4 text-indigo-500" />
                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Tren Temuan per Bulan</h3>
                    </div>
                    {charts.temuan_per_bulan.length === 0 ? (
                        <div className="flex h-[250px] items-center justify-center text-sm text-gray-400">Belum ada data.</div>
                    ) : (
                        <ResponsiveContainer width="100%" height={250}>
                            <BarChart data={charts.temuan_per_bulan} margin={{ top: 5, right: 5, left: -10, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                                <XAxis dataKey="bulan" tickFormatter={formatBulan} tick={{ fontSize: 10, fill: '#9ca3af' }} axisLine={{ stroke: '#e5e7eb' }} tickLine={false} />
                                <YAxis tick={{ fontSize: 10, fill: '#9ca3af' }} axisLine={{ stroke: '#e5e7eb' }} tickLine={false} allowDecimals={false} />
                                <Tooltip content={<ChartTooltip />} />
                                <Bar dataKey="count" name="Total Temuan" fill="#6366f1" radius={[3, 3, 0, 0]} maxBarSize={32} />
                            </BarChart>
                        </ResponsiveContainer>
                    )}
                </div>

                {/* Pie Chart: Severity Distribution */}
                <div className="overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center gap-2">
                        <PieChartIcon className="h-4 w-4 text-indigo-500" />
                        <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Distribusi Severity</h3>
                    </div>
                    {severityPieData.length === 0 ? (
                        <div className="flex h-[250px] items-center justify-center text-sm text-gray-400">Belum ada data.</div>
                    ) : (
                        <div className="flex flex-col items-center">
                            <ResponsiveContainer width="100%" height={220}>
                                <PieChart>
                                    <Pie data={severityPieData} cx="50%" cy="50%" innerRadius={55} outerRadius={85} paddingAngle={3} dataKey="value">
                                        {severityPieData.map((entry, idx) => (
                                            <Cell key={idx} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    <Tooltip content={<ChartTooltip />} />
                                    <Legend formatter={(value: string) => <span className="text-xs font-medium text-gray-600">{value}</span>} iconSize={10} />
                                </PieChart>
                            </ResponsiveContainer>
                            <p className="mt-1 text-[10px] font-medium text-gray-400">Total: {totalTemuanForPie} temuan</p>
                        </div>
                    )}
                </div>
            </div>

            {/* Temuan per Standar Mutu */}
            <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div className="border-b border-gray-100 px-6 py-4">
                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Temuan per Standar Mutu</h3>
                </div>
                <div className="p-6">
                    {charts.temuan_per_standar.length === 0 ? (
                        <div className="flex h-[100px] items-center justify-center text-sm text-gray-400">Belum ada data temuan per standar.</div>
                    ) : (
                        <div className="space-y-4">
                            {charts.temuan_per_standar.map((item) => {
                                const maxCount = Math.max(...charts.temuan_per_standar.map((s) => s.count), 1);
                                const barWidth = (item.count / maxCount) * 100;
                                const barColor = item.count >= 10 ? 'bg-red-500' : item.count >= 5 ? 'bg-orange-500' : 'bg-green-500';

                                return (
                                    <div key={item.standar_id} className="group">
                                        <div className="mb-1 flex items-center justify-between">
                                            <div className="flex items-center gap-2 min-w-0">
                                                <span className="text-xs font-bold text-gray-700 truncate">{item.kode_standar}</span>
                                                <span className="hidden text-[10px] text-gray-400 truncate sm:inline">{item.nama_standar}</span>
                                            </div>
                                            <span className="shrink-0 text-xs font-black text-gray-900 tabular-nums">{item.count}</span>
                                        </div>
                                        <div className="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                                            <div className={`h-full rounded-full transition-all duration-500 group-hover:opacity-80 ${barColor}`} style={{ width: `${barWidth}%` }} />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>

            {/* Ranking Prodi */}
            <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Ranking Mutu Program Studi</h3>
                    <Users className="h-4 w-4 text-gray-300" />
                </div>
                <div className="p-6">
                    {ranking_prodi.length === 0 ? (
                        <div className="flex h-[100px] items-center justify-center text-sm text-gray-400">Pilih prodi dan periode untuk melihat ranking.</div>
                    ) : (
                        <div className="divide-y divide-gray-50">
                            {ranking_prodi.map((prodi) => {
                                const stars = Math.round(prodi.skor_mutu / 20);
                                const hasKritis = prodi.kriteria === 'kritis';

                                return (
                                    <div key={prodi.rank} className={`flex items-center gap-4 px-2 py-3 transition-all hover:bg-gray-50 ${hasKritis ? 'bg-red-50/30' : ''}`}>
                                        <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-black text-indigo-600">
                                            {prodi.rank}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-bold text-gray-900 truncate">{prodi.nama_prodi}</span>
                                                {hasKritis && (
                                                    <span className="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">KRITIS</span>
                                                )}
                                            </div>
                                            <div className="mt-0.5 flex items-center gap-1">
                                                {Array.from({ length: 5 }).map((_, i) => (
                                                    <Star key={i} className={`h-3 w-3 ${i < stars ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200'}`} />
                                                ))}
                                                <span className="ml-1 text-[10px] font-medium text-gray-400">{prodi.skor_mutu.toFixed(2)}</span>
                                            </div>
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="text-xs font-bold text-gray-500">{prodi.total_temuan}</p>
                                            <p className="text-[10px] font-medium text-gray-400">temuan</p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
