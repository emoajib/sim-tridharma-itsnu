import { Link } from '@inertiajs/react';

function StatCard({ label, value, color, href, isTheme3 }: { label: string; value: number | string; color: string; href?: string; isTheme3?: boolean }) {
    if (isTheme3) {
        const card3 = (
            <div className="kpi-card">
                <p className="kpi-label">{label}</p>
                <p className="kpi-value">{value}</p>
            </div>
        );
        return href ? <Link href={href} className="block transition hover:scale-105">{card3}</Link> : card3;
    }

    const card = (
        <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100 group hover:border-indigo-200 transition-all">
            <p className="text-sm font-bold text-gray-400 uppercase tracking-widest">{label}</p>
            <p className="mt-2 text-4xl font-black text-gray-800">{value}</p>
            <div className={`mt-4 h-1 w-12 rounded-full ${color}`}></div>
        </div>
    );
    return href ? <Link href={href} className="block transition-transform hover:-translate-y-1">{card}</Link> : card;
}

interface SpmiOverview {
    total_temuan: number;
    open_temuan: number;
    in_progress_temuan: number;
    closed_temuan: number;
    close_rate: number;
    skor_mutu: number;
    capa_overdue_count: number;
    capa_approaching_count: number;
}

interface SpmiCharts {
    temuan_per_standar: Array<{ standar_id: number; kode_standar: string; nama_standar: string; count: number }>;
    temuan_per_bulan: Array<{ bulan: string; count: number }>;
    severity_distribution: { ringan: number; sedang: number; berat: number; kritis: number };
}

interface SpmiPpepp {
    stages: Array<{ key: string; label: string; count: number; percentage: number; icon: string; color: string }>;
    total_audits: number;
}

interface Props {
    spmi_overview?: SpmiOverview;
    spmi_charts?: SpmiCharts;
    spmi_ppepp?: SpmiPpepp;
    isTheme3: boolean;
}

export default function SpmiTab({ spmi_overview, spmi_charts, spmi_ppepp, isTheme3 }: Props) {
    return (
        <div className="mb-12">
            {/* KPI Cards */}
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-8">
                <StatCard label="Total Temuan" value={spmi_overview?.total_temuan ?? 0} color="bg-indigo-500" href={route('spmi.audit')} isTheme3={isTheme3} />
                <StatCard label="Open" value={spmi_overview?.open_temuan ?? 0} color="bg-amber-500" isTheme3={isTheme3} />
                <StatCard label="Close Rate" value={spmi_overview?.close_rate != null ? `${spmi_overview.close_rate}%` : '0%'} color="bg-emerald-500" isTheme3={isTheme3} />
                <StatCard label="Skor Mutu" value={spmi_overview?.skor_mutu ?? 0} color="bg-purple-500" isTheme3={isTheme3} />
                <StatCard label="CAPA Overdue" value={spmi_overview?.capa_overdue_count ?? 0} color="bg-red-500" href={route('spmi.capa')} isTheme3={isTheme3} />
            </div>

            {/* PPEPP Progress */}
            {spmi_ppepp && spmi_ppepp.stages && spmi_ppepp.stages.length > 0 && (
                <div className="mb-8 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">
                        🔄 Siklus PPEPP — {spmi_ppepp.total_audits} Total Audit
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                        {spmi_ppepp.stages.map((stage) => (
                            <div key={stage.key} className="text-center">
                                <div className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{stage.label}</div>
                                <div className="relative h-3 bg-gray-100 rounded-full overflow-hidden mb-2">
                                    <div className="h-full rounded-full transition-all duration-500" style={{ width: `${stage.percentage}%`, backgroundColor: stage.color }} />
                                </div>
                                <div className="text-sm font-black" style={{ color: stage.color }}>{stage.percentage}%</div>
                                <div className="text-[10px] text-gray-400 font-medium">{stage.count} temuan</div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Severity + Temuan per Bulan */}
            {spmi_charts && (
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">
                            🥧 Distribusi Severity
                        </h3>
                        {(() => {
                            const sd = spmi_charts?.severity_distribution;
                            if (!sd) return <p className="text-gray-400 italic text-sm">Tidak ada data.</p>;
                            const items = [
                                { label: 'Ringan', count: sd.ringan, color: 'bg-green-500' },
                                { label: 'Sedang', count: sd.sedang, color: 'bg-yellow-500' },
                                { label: 'Berat', count: sd.berat, color: 'bg-orange-500' },
                                { label: 'Kritis', count: sd.kritis, color: 'bg-red-500' },
                            ];
                            const total = items.reduce((s, i) => s + i.count, 0) || 1;
                            return (
                                <div className="space-y-3">
                                    {items.map((item) => (
                                        <div key={item.label} className="flex items-center gap-3">
                                            <div className="w-16 text-xs font-bold text-gray-600">{item.label}</div>
                                            <div className="flex-1 h-4 bg-gray-100 rounded-full overflow-hidden">
                                                <div className={`h-full rounded-full ${item.color}`} style={{ width: `${(item.count / total) * 100}%` }} />
                                            </div>
                                            <div className="w-10 text-right text-xs font-black text-gray-700">{item.count}</div>
                                        </div>
                                    ))}
                                </div>
                            );
                        })()}
                    </div>
                    <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">
                            📈 Tren Temuan per Bulan (12 bulan)
                        </h3>
                        {spmi_charts?.temuan_per_bulan && spmi_charts.temuan_per_bulan.length > 0 ? (
                            <div className="space-y-2">
                                {spmi_charts.temuan_per_bulan.slice(-6).map((item) => (
                                    <div key={item.bulan} className="flex items-center gap-3">
                                        <div className="w-16 text-[10px] font-bold text-gray-500">{item.bulan}</div>
                                        <div className="flex-1 h-5 bg-gray-100 rounded-full overflow-hidden">
                                            <div className="h-full rounded-full bg-indigo-500" style={{ width: `${Math.min((item.count / Math.max(...spmi_charts.temuan_per_bulan.map((b) => b.count), 1)) * 100, 100)}%` }} />
                                        </div>
                                        <div className="w-6 text-right text-xs font-black text-gray-700">{item.count}</div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-gray-400 italic text-sm">Belum ada data temuan.</p>
                        )}
                    </div>
                </div>
            )}

            {/* Link ke SPMI Dashboard */}
            <div className="text-center">
                <Link href={route('spmi.dashboard')} className="inline-flex px-6 py-3 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                    Buka Dashboard SPMI Lengkap →
                </Link>
            </div>
        </div>
    );
}
