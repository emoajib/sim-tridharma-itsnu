import React, { Suspense, useState, useEffect, useCallback } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { Skeleton, SkeletonCard, SkeletonChart } from '@/Components/Skeleton';

// ─── Lazy-loaded sections ─────────────────────────────────────────────────────
const StatsCards = React.lazy(() => import('./StatsCards'));
const CycleList = React.lazy(() => import('./CycleList'));
const IndicatorGrid = React.lazy(() => import('./IndicatorGrid'));
const VerificationSection = React.lazy(() => import('./VerificationSection'));

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

// ─── Fallback ─────────────────────────────────────────────────────────────────
const SectionFallback = () => (
    <div className="animate-pulse space-y-6">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            {Array.from({ length: 6 }).map((_, i) => (
                <SkeletonCard key={i} />
            ))}
        </div>
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <SkeletonChart />
            <SkeletonChart />
        </div>
    </div>
);

// ─── Helpers ──────────────────────────────────────────────────────────────────

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

const PPEPP_KEY_MAP: Record<string, { letter: string }> = {
    penetapan: { letter: 'P' },
    pelaksanaan: { letter: 'P' },
    evaluasi: { letter: 'E' },
    pengendalian: { letter: 'P' },
    peningkatan: { letter: 'P' },
};

function formatBulan(bulanStr: string): string {
    try {
        const [year, month] = bulanStr.split('-');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${months[parseInt(month, 10) - 1]} ${year}`;
    } catch {
        return bulanStr;
    }
}

function severityToChartArray(dist: SeverityDistribution): Array<{ name: string; value: number; color: string }> {
    return Object.entries(dist)
        .filter(([, v]) => v > 0)
        .map(([key, value]) => ({
            name: SEVERITY_LABELS[key] || key,
            value,
            color: SEVERITY_COLORS[key] || '#9ca3af',
        }));
}

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

export default function Dashboard(props: Props) {
    const [loading, setLoading] = useState(false);
    const [polling, setPolling] = useState(true);
    const [localOverview, setLocalOverview] = useState<Overview>(props.overview);
    const [localCharts, setLocalCharts] = useState<ChartData>(props.charts);

    useEffect(() => {
        setLocalOverview(props.overview);
        setLocalCharts(props.charts);
        setLoading(false);
    }, [props.overview, props.charts]);

    function changeFilter(key: string, value: string) {
        setLoading(true);
        router.get(
            route('spmi.dashboard'),
            { ...props.filters, [key]: value || '' },
            { preserveState: true, replace: true }
        );
    }

    const pollData = useCallback(async () => {
        if (!polling) return;
        try {
            const params = new URLSearchParams();
            if (props.filters.prodi_id) params.set('prodi_id', props.filters.prodi_id);
            if (props.filters.periode_id) params.set('periode_id', props.filters.periode_id);
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
            // silent fail
        }
    }, [polling, props.filters.prodi_id, props.filters.periode_id]);

    useEffect(() => {
        const interval = setInterval(pollData, 60000);
        return () => clearInterval(interval);
    }, [pollData]);

    const severityPieData = severityToChartArray(localCharts.severity_distribution);
    const totalTemuanForPie = severityPieData.reduce((sum, d) => sum + d.value, 0);

    if (loading) {
        return (
            <AuthenticatedLayout
                header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard Penjaminan Mutu SPMI</h2>}
            >
                <Head title="SPMI Dashboard" />
                <div className="py-12">
                    <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <SectionFallback />
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
                        {/* Breadcrumb */}
                        <nav className="mb-4 text-sm text-gray-500">
                            <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                                Dashboard
                            </Link>
                            <span className="mx-2">/</span>
                            <span className="text-gray-700">SPMI</span>
                        </nav>

                        {/* Sticky Filter Bar */}
                        <div className="sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8 mb-6 border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div className="flex flex-wrap items-center gap-4">
                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-gray-400">Program Studi</label>
                                        <select
                                            value={props.filters.prodi_id || ''}
                                            onChange={(e) => changeFilter('prodi_id', e.target.value)}
                                            className="min-w-[200px] rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Semua Prodi</option>
                                            {props.prodi_list.map((p) => (
                                                <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-gray-400">Periode</label>
                                        <select
                                            value={props.filters.periode_id || ''}
                                            onChange={(e) => changeFilter('periode_id', e.target.value)}
                                            className="min-w-[180px] rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Semua Periode</option>
                                            {props.periode_list.map((p) => (
                                                <option key={p.id} value={p.id}>{p.nama_periode}</option>
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

                        {/* Sections */}
                        <Suspense fallback={<SectionFallback />}>
                            <StatsCards overview={localOverview} />
                            <CycleList ppepp={props.ppepp} />
                            <IndicatorGrid
                                charts={localCharts}
                                formatBulan={formatBulan}
                                severityToChartArray={severityToChartArray}
                                ChartTooltip={ChartTooltip}
                                severityPieData={severityPieData}
                                totalTemuanForPie={totalTemuanForPie}
                                ranking_prodi={props.ranking_prodi}
                            />
                            <VerificationSection early_warnings={props.early_warnings} polling={polling} />
                        </Suspense>
                    </div>
                </div>
            </AuthenticatedLayout>
        </ErrorBoundary>
    );
}
