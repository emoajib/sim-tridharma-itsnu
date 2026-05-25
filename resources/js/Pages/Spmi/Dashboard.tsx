import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import {
    AlertTriangle,
    Clock,
    CheckCircle2,
    TrendingUp,
    RefreshCw,
    AlertCircle,
    FileText,
    PlayCircle,
    Search,
    Shield,
    TrendingUp as TrendingUpIcon,
    BarChart3,
    PieChart as PieChartIcon,
    Star,
    Users,
} from 'lucide-react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
    Legend,
} from 'recharts';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { Skeleton, SkeletonCard, SkeletonChart } from '@/Components/Skeleton';
import KpiCard from '@/Components/SPMI/KpiCard';
import EarlyWarning from '@/Components/SPMI/EarlyWarning';
import StatusBadge from '@/Components/SPMI/StatusBadge';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface Overview {
    total_temuan: number;
    open_temuan: number;
    in_progress_temuan: number;
    closed_temuan: number;
    close_rate: number;
    skor_mutu: number;
    capa_overdue_count: number;
    capa_approaching_count: number;
}

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

interface PpeppStage {
    key: string;
    label: string;
    count: number;
    percentage: number;
    icon: string;
    color: string;
}

interface PpeppProgress {
    stages: PpeppStage[];
    total_audits: number;
}

interface EarlyWarningItem {
    type: 'kritis' | 'overdue' | 'mendekat' | 'info';
    message: string;
    prodi?: string;
    days?: number;
}

interface RankingProdi {
    rank: number;
    nama_prodi: string;
    skor_mutu: number;
    total_temuan: number;
    kriteria?: string;
}

interface Filters {
    prodi_id?: string | null;
    periode_id?: string | null;
}

interface Props {
    overview: Overview;
    charts: ChartData;
    ppepp: PpeppProgress;
    early_warnings?: EarlyWarningItem[];
    ranking_prodi?: RankingProdi[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    filters: Filters;
}

// ─── Severity Pie Colors ──────────────────────────────────────────────────────

const SEVERITY_COLORS: Record<string, string> = {
    ringan: '#22c55e',
    sedang: '#eab308',
    berat: '#f97316',
    kritis: '#ef4444',
};

const SEVERITY_LABELS: Record<string, string> = {
    ringan: 'Ringan',
    sedang: 'Sedang',
    berat: 'Berat',
    kritis: 'Kritis',
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatBulan(bulanStr: string): string {
    try {
        const [year, month] = bulanStr.split('-');
        const months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];
        return `${months[parseInt(month, 10) - 1]} ${year}`;
    } catch {
        return bulanStr;
    }
}

function getSeverityAvgLabel(avg: number): string {
    if (avg >= 3) return 'Tinggi';
    if (avg >= 2) return 'Sedang';
    return 'Rendah';
}

// ─── PPEPP Helpers ───────────────────────────────────────────────────────────

const PPEPP_KEY_MAP: Record<string, { letter: string; icon: React.ReactNode }> = {
    penetapan: {
        letter: 'P',
        icon: <FileText className="h-4 w-4" />,
    },
    pelaksanaan: {
        letter: 'P',
        icon: <PlayCircle className="h-4 w-4" />,
    },
    evaluasi: {
        letter: 'E',
        icon: <Search className="h-4 w-4" />,
    },
    pengendalian: {
        letter: 'P',
        icon: <Shield className="h-4 w-4" />,
    },
    peningkatan: {
        letter: 'P',
        icon: <TrendingUpIcon className="h-4 w-4" />,
    },
};

// ─── Severity Pie Data ────────────────────────────────────────────────────────

function severityToChartArray(dist: SeverityDistribution): Array<{ name: string; value: number; color: string }> {
    return Object.entries(dist)
        .filter(([, v]) => v > 0)
        .map(([key, value]) => ({
            name: SEVERITY_LABELS[key] || key,
            value,
            color: SEVERITY_COLORS[key] || '#9ca3af',
        }));
}

// ─── Custom Tooltip ───────────────────────────────────────────────────────────

function ChartTooltip({ active, payload, label }: any) {
    if (!active || !payload?.length) return null;
    return (
        <div className="rounded-lg border border-gray-100 bg-white px-3 py-2 shadow-lg">
            <p className="text-xs font-semibold text-gray-900">{label}</p>
            {payload.map((entry: any, idx: number) => (
                <p key={idx} className="text-xs font-medium" style={{ color: entry.color }}>
                    {entry.name}: {entry.value}
                </p>
            ))}
        </div>
    );
}

// ─── Main Component ───────────────────────────────────────────────────────────

export default function Dashboard({
    overview,
    charts,
    ppepp,
    early_warnings = [],
    ranking_prodi = [],
    prodi_list,
    periode_list,
    filters,
}: Props) {
    const [loading, setLoading] = useState(false);
    const [polling, setPolling] = useState(true);
    const [localOverview, setLocalOverview] = useState<Overview>(overview);
    const [localCharts, setLocalCharts] = useState<ChartData>(charts);

    // ── Sync props to local state on initial mount / page navigation ──
    useEffect(() => {
        setLocalOverview(overview);
        setLocalCharts(charts);
        setLoading(false);
    }, [overview, charts]);

    // ── Filter change handler ──
    function changeFilter(key: string, value: string) {
        setLoading(true);
        router.get(
            route('spmi.dashboard'),
            {
                ...filters,
                [key]: value || '',
            },
            { preserveState: true, replace: true }
        );
    }

    // ── Polling: refresh via JSON every 60s ──
    const pollData = useCallback(async () => {
        if (!polling) return;
        try {
            const params = new URLSearchParams();
            if (filters.prodi_id) params.set('prodi_id', filters.prodi_id);
            if (filters.periode_id) params.set('periode_id', filters.periode_id);
            const res = await fetch(route('spmi.dashboard') + '?' + params.toString(), {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;
            const json = await res.json();
            if (json.success && json.data) {
                setLocalOverview((prev) => ({ ...prev, ...json.data.overview }));
                if (json.data.charts) {
                    setLocalCharts((prev) => ({ ...prev, ...json.data.charts }));
                }
            }
        } catch {
            // silent fail — polling should not disrupt UX
        }
    }, [polling, filters.prodi_id, filters.periode_id]);

    useEffect(() => {
        const interval = setInterval(pollData, 60000);
        return () => clearInterval(interval);
    }, [pollData]);

    // ── Derived data ──
    const severityPieData = severityToChartArray(localCharts.severity_distribution);
    const totalTemuanForPie = severityPieData.reduce((sum, d) => sum + d.value, 0);

    // ── Loading state ──
    if (loading) {
        return (
            <AuthenticatedLayout
                header={
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Dashboard Penjaminan Mutu SPMI
                    </h2>
                }
            >
                <Head title="SPMI Dashboard" />
                <div className="py-12">
                    <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                            {Array.from({ length: 6 }).map((_, i) => (
                                <SkeletonCard key={i} />
                            ))}
                        </div>
                        <div className="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <SkeletonChart />
                            <SkeletonChart />
                        </div>
                        <div className="mt-8">
                            <SkeletonChart />
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <ErrorBoundary>
            <AuthenticatedLayout
                header={
                    <div className="flex flex-col">
                        <h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">
                            Dashboard Penjaminan Mutu SPMI
                        </h2>
                        <p className="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">
                            Sistem Penjaminan Mutu Internal
                        </p>
                    </div>
                }
            >
                <Head title="SPMI Dashboard" />

                <div className="py-2">
                    <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {/* ─── Breadcrumb ─── */}
                        <nav className="mb-4 text-sm text-gray-500">
                            <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                                Dashboard
                            </Link>
                            <span className="mx-2">/</span>
                            <span className="text-gray-700">SPMI</span>
                        </nav>

                        {/* ─── Sticky Filter Bar ─── */}
                        <div className="sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8 mb-6 border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div className="flex flex-wrap items-center gap-4">
                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                            Program Studi
                                        </label>
                                        <select
                                            value={filters.prodi_id || ''}
                                            onChange={(e) => changeFilter('prodi_id', e.target.value)}
                                            className="min-w-[200px] rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Semua Prodi</option>
                                            {prodi_list.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.nama_prodi}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                            Periode
                                        </label>
                                        <select
                                            value={filters.periode_id || ''}
                                            onChange={(e) => changeFilter('periode_id', e.target.value)}
                                            className="min-w-[180px] rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Semua Periode</option>
                                            {periode_list.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.nama_periode}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <label className="inline-flex items-center gap-2 text-xs font-medium text-gray-500">
                                        <input
                                            type="checkbox"
                                            checked={polling}
                                            onChange={(e) => setPolling(e.target.checked)}
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        Auto-refresh
                                    </label>
                                    <button
                                        type="button"
                                        onClick={pollData}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50"
                                    >
                                        <RefreshCw className="h-3.5 w-3.5" />
                                        Refresh
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 1: KPI Cards (6 cards)
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                            <KpiCard
                                title="Total Temuan"
                                value={localOverview.total_temuan}
                                icon={<AlertTriangle className="h-5 w-5" />}
                                color="blue"
                            />
                            <KpiCard
                                title="Open Temuan"
                                value={localOverview.open_temuan}
                                icon={<Clock className="h-5 w-5" />}
                                color="yellow"
                            />
                            <KpiCard
                                title="Close Rate"
                                value={`${localOverview.close_rate}%`}
                                icon={<CheckCircle2 className="h-5 w-5" />}
                                color="green"
                                trend={{ value: localOverview.close_rate, direction: localOverview.close_rate >= 70 ? 'up' : localOverview.close_rate >= 40 ? 'flat' : 'down' }}
                            />
                            <KpiCard
                                title="Skor Mutu"
                                value={localOverview.skor_mutu.toFixed(2)}
                                icon={<TrendingUp className="h-5 w-5" />}
                                color="purple"
                            />
                            <KpiCard
                                title="CAPA Overdue"
                                value={localOverview.capa_overdue_count}
                                icon={<AlertTriangle className="h-5 w-5" />}
                                color="red"
                            />
                            <KpiCard
                                title="CAPA Mendekat"
                                value={localOverview.capa_approaching_count}
                                icon={<Clock className="h-5 w-5" />}
                                color="yellow"
                            />
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 2: PPEPP Cycle Progress
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    Siklus PPEPP
                                </h3>
                                <Link
                                    href={route('spmi.standar-mutu')}
                                    className="text-[10px] font-bold uppercase tracking-widest text-indigo-600 underline decoration-indigo-200 underline-offset-4 hover:text-indigo-800"
                                >
                                    Lihat Detail
                                </Link>
                            </div>
                            <div className="grid grid-cols-1 gap-4 p-6 sm:grid-cols-5">
                                {ppepp.stages.map((stage, idx) => {
                                    const stageMeta = PPEPP_KEY_MAP[stage.key] || {
                                        letter: stage.key.charAt(0).toUpperCase(),
                                        icon: null,
                                    };
                                    return (
                                        <div
                                            key={stage.key}
                                            className="flex flex-col items-center rounded-lg border border-gray-100 bg-gray-50/50 p-4 text-center transition-all hover:shadow-sm"
                                        >
                                            <div
                                                className="mb-2 flex h-10 w-10 items-center justify-center rounded-full text-white"
                                                style={{ backgroundColor: stage.color }}
                                            >
                                                <span className="text-sm font-black">
                                                    {stageMeta.letter}
                                                </span>
                                            </div>
                                            <p className="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                                                {stage.label}
                                            </p>
                                            <p className="mt-1 text-2xl font-black text-gray-900 tabular-nums">
                                                {stage.percentage}%
                                            </p>
                                            <StatusBadge
                                                status={
                                                    stage.percentage >= 80
                                                        ? 'verified'
                                                        : stage.percentage >= 50
                                                          ? 'in_progress'
                                                          : 'draft'
                                                }
                                                workflowType="audit"
                                                size="sm"
                                            />
                                            <div className="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                                <div
                                                    className="h-full rounded-full transition-all duration-500"
                                                    style={{
                                                        width: `${stage.percentage}%`,
                                                        backgroundColor: stage.color,
                                                    }}
                                                />
                                            </div>
                                            <p className="mt-1 text-[10px] font-medium text-gray-400">
                                                {stage.count} item
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="border-t border-gray-100 px-6 py-3 text-center text-[10px] font-medium text-gray-400">
                                Total Audit: {ppepp.total_audits} item tersebar di 5 tahap PPEPP
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 3: Charts (2 columns)
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {/* Bar Chart: Temuan per Bulan */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                                <div className="mb-4 flex items-center gap-2">
                                    <BarChart3 className="h-4 w-4 text-indigo-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Tren Temuan per Bulan
                                    </h3>
                                </div>
                                {localCharts.temuan_per_bulan.length === 0 ? (
                                    <div className="flex h-[250px] items-center justify-center text-sm text-gray-400">
                                        Belum ada data.
                                    </div>
                                ) : (
                                    <ResponsiveContainer width="100%" height={250}>
                                        <BarChart
                                            data={localCharts.temuan_per_bulan}
                                            margin={{ top: 5, right: 5, left: -10, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                                            <XAxis
                                                dataKey="bulan"
                                                tickFormatter={formatBulan}
                                                tick={{ fontSize: 10, fill: '#9ca3af' }}
                                                axisLine={{ stroke: '#e5e7eb' }}
                                                tickLine={false}
                                            />
                                            <YAxis
                                                tick={{ fontSize: 10, fill: '#9ca3af' }}
                                                axisLine={{ stroke: '#e5e7eb' }}
                                                tickLine={false}
                                                allowDecimals={false}
                                            />
                                            <Tooltip content={<ChartTooltip />} />
                                            <Bar
                                                dataKey="count"
                                                name="Total Temuan"
                                                fill="#6366f1"
                                                radius={[3, 3, 0, 0]}
                                                maxBarSize={32}
                                            />
                                        </BarChart>
                                    </ResponsiveContainer>
                                )}
                            </div>

                            {/* Pie Chart: Severity Distribution */}
                            <div className="overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                                <div className="mb-4 flex items-center gap-2">
                                    <PieChartIcon className="h-4 w-4 text-indigo-500" />
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                        Distribusi Severity
                                    </h3>
                                </div>
                                {severityPieData.length === 0 ? (
                                    <div className="flex h-[250px] items-center justify-center text-sm text-gray-400">
                                        Belum ada data.
                                    </div>
                                ) : (
                                    <div className="flex flex-col items-center">
                                        <ResponsiveContainer width="100%" height={220}>
                                            <PieChart>
                                                <Pie
                                                    data={severityPieData}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={55}
                                                    outerRadius={85}
                                                    paddingAngle={3}
                                                    dataKey="value"
                                                >
                                                    {severityPieData.map((entry, idx) => (
                                                        <Cell key={idx} fill={entry.color} />
                                                    ))}
                                                </Pie>
                                                <Tooltip content={<ChartTooltip />} />
                                                <Legend
                                                    formatter={(value: string) => (
                                                        <span className="text-xs font-medium text-gray-600">
                                                            {value}
                                                        </span>
                                                    )}
                                                    iconSize={10}
                                                />
                                            </PieChart>
                                        </ResponsiveContainer>
                                        <p className="mt-1 text-[10px] font-medium text-gray-400">
                                            Total: {totalTemuanForPie} temuan
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 4: Early Warning Panel
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8">
                            <div className="mb-4 flex items-center gap-2">
                                <AlertTriangle className="h-4 w-4 text-red-500" />
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    Early Warning System
                                </h3>
                                {polling && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">
                                        <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
                                        Live
                                    </span>
                                )}
                            </div>
                            <EarlyWarning warnings={early_warnings} />
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 5: Temuan per Standar Mutu (Horizontal bars)
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    Temuan per Standar Mutu
                                </h3>
                            </div>
                            <div className="p-6">
                                {localCharts.temuan_per_standar.length === 0 ? (
                                    <div className="flex h-[100px] items-center justify-center text-sm text-gray-400">
                                        Belum ada data temuan per standar.
                                    </div>
                                ) : (
                                    <div className="space-y-4">
                                        {localCharts.temuan_per_standar.map((item) => {
                                            const maxCount = Math.max(
                                                ...localCharts.temuan_per_standar.map((s) => s.count),
                                                1
                                            );
                                            const barWidth = (item.count / maxCount) * 100;
                                            // Color logic: if has_kritis would come from backend,
                                            // but since it's not available yet, use count thresholds
                                            const barColor =
                                                item.count >= 10
                                                    ? 'bg-red-500'
                                                    : item.count >= 5
                                                      ? 'bg-orange-500'
                                                      : 'bg-green-500';

                                            return (
                                                <div key={item.standar_id} className="group">
                                                    <div className="mb-1 flex items-center justify-between">
                                                        <div className="flex items-center gap-2 min-w-0">
                                                            <span className="text-xs font-bold text-gray-700 truncate">
                                                                {item.kode_standar}
                                                            </span>
                                                            <span className="hidden text-[10px] text-gray-400 truncate sm:inline">
                                                                {item.nama_standar}
                                                            </span>
                                                        </div>
                                                        <span className="shrink-0 text-xs font-black text-gray-900 tabular-nums">
                                                            {item.count}
                                                        </span>
                                                    </div>
                                                    <div className="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                                                        <div
                                                            className={`h-full rounded-full transition-all duration-500 group-hover:opacity-80 ${barColor}`}
                                                            style={{ width: `${barWidth}%` }}
                                                        />
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* ════════════════════════════════════════════════════════════════
                           SECTION 6: Ranking Prodi
                           ════════════════════════════════════════════════════════════════ */}
                        <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    Ranking Mutu Program Studi
                                </h3>
                                <Users className="h-4 w-4 text-gray-300" />
                            </div>
                            <div className="p-6">
                                {ranking_prodi.length === 0 ? (
                                    <div className="flex h-[100px] items-center justify-center text-sm text-gray-400">
                                        Pilih prodi dan periode untuk melihat ranking.
                                    </div>
                                ) : (
                                    <div className="divide-y divide-gray-50">
                                        {ranking_prodi.map((prodi) => {
                                            const stars = Math.round(prodi.skor_mutu / 20);
                                            const hasKritis = prodi.kriteria === 'kritis';

                                            return (
                                                <div
                                                    key={prodi.rank}
                                                    className={`flex items-center gap-4 px-2 py-3 transition-all hover:bg-gray-50 ${
                                                        hasKritis ? 'bg-red-50/30' : ''
                                                    }`}
                                                >
                                                    {/* Rank */}
                                                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-black text-indigo-600">
                                                        {prodi.rank}
                                                    </div>

                                                    {/* Name + Stars */}
                                                    <div className="min-w-0 flex-1">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-bold text-gray-900 truncate">
                                                                {prodi.nama_prodi}
                                                            </span>
                                                            {hasKritis && (
                                                                <span className="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">
                                                                    KRITIS
                                                                </span>
                                                            )}
                                                        </div>
                                                        <div className="mt-0.5 flex items-center gap-1">
                                                            {Array.from({ length: 5 }).map((_, i) => (
                                                                <Star
                                                                    key={i}
                                                                    className={`h-3 w-3 ${
                                                                        i < stars
                                                                            ? 'fill-yellow-400 text-yellow-400'
                                                                            : 'text-gray-200'
                                                                    }`}
                                                                />
                                                            ))}
                                                            <span className="ml-1 text-[10px] font-medium text-gray-400">
                                                                {prodi.skor_mutu.toFixed(2)}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {/* Temuan count */}
                                                    <div className="shrink-0 text-right">
                                                        <p className="text-xs font-bold text-gray-500">
                                                            {prodi.total_temuan}
                                                        </p>
                                                        <p className="text-[10px] font-medium text-gray-400">
                                                            temuan
                                                        </p>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        </ErrorBoundary>
    );
}
