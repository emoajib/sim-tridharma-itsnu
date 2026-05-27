import React, { Suspense, useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import PeringatanBadge from '@/Components/Agent/PeringatanBadge';
import { SkeletonCard, SkeletonChart } from '@/Components/Skeleton';

// ─── Lazy-loaded tab components ───────────────────────────────────────────────
const OverviewTab = React.lazy(() => import('./OverviewTab'));
const PrediksiTab = React.lazy(() => import('./PrediksiTab'));
const KinerjaTab = React.lazy(() => import('./KinerjaTab'));
const PeringatanTab = React.lazy(() => import('./PeringatanTab'));
const SpmiTab = React.lazy(() => import('./SpmiTab'));

// ─── Types ────────────────────────────────────────────────────────────────────
interface Periode {
    id: number;
    nama_periode: string;
}

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface RecentItem {
    id: number;
    dosen?: { nama_depan: string; nama_belakang?: string };
    judul_penelitian?: string;
    judul_publikasi?: string;
    judul_pkm?: string;
    nama_kegiatan?: string;
    created_at: string;
}

interface PeringatanStats {
    critical: number;
    warning: number;
    info: number;
    unread: number;
    total: number;
}

interface LatestPrediction {
    id: number;
    skor_prediksi: number;
    prob_unggul: number;
    prob_baik_sekali: number;
    prob_baik: number;
    confidence_interval: string;
    created_at: string;
}

interface ProdiAccreditation {
    id: number;
    nama: string;
    fakultas: string;
    status_saat_ini: string;
    skor_simulasi: number;
    trend: number;
}

interface InstitutionAccreditation {
    nama: string;
    status_saat_ini: string;
    skor_simulasi: number;
    target: string;
    last_sync: string;
}

interface Props {
    stats: { dosen_count: number; prodi_count: number; fakultas_count: number };
    portofolioStats: {
        pendidikan_count: number; penelitian_count: number; publikasi_count: number;
        pkm_count: number; penunjang_count: number; bkd_count: number;
        bimbingan_count: number; dokumen_count: number;
    };
    bkdStats: { total: number; disetujui: number; draft: number; diajukan: number; rata_sks: number };
    recentPendidikan: RecentItem[];
    recentPenelitian: RecentItem[];
    periode_list: Periode[];
    selectedPeriode: Periode | null;
    lembaga_list: Lembaga[];
    selectedInstrumenId: number;
    peringatanStats?: PeringatanStats;
    latestPrediction?: LatestPrediction | null;
    kriteriaStats: any[];
    prodiAccreditation: ProdiAccreditation[];
    institutionAccreditation: InstitutionAccreditation | null;
    filters: { periode_id?: string; instrumen_id?: string };
    dashboardDefaultTab?: string;
    activeRole: string;
    scopeName: string;
    spmi_overview?: {
        total_temuan: number;
        open_temuan: number;
        in_progress_temuan: number;
        closed_temuan: number;
        close_rate: number;
        skor_mutu: number;
        capa_overdue_count: number;
        capa_approaching_count: number;
    };
    spmi_charts?: {
        temuan_per_standar: Array<{ standar_id: number; kode_standar: string; nama_standar: string; count: number }>;
        temuan_per_bulan: Array<{ bulan: string; count: number }>;
        severity_distribution: { ringan: number; sedang: number; berat: number; kritis: number };
    };
    spmi_ppepp?: {
        stages: Array<{ key: string; label: string; count: number; percentage: number; icon: string; color: string }>;
        total_audits: number;
    };
}

// ─── Fallback skeleton for lazy tabs ──────────────────────────────────────────
const TabFallback = () => (
    <div className="animate-pulse space-y-6">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <SkeletonCard />
            <SkeletonCard />
            <SkeletonCard />
            <SkeletonCard />
        </div>
        <SkeletonChart />
    </div>
);

// ─── Main Component ───────────────────────────────────────────────────────────
export default function Dashboard(props: Props) {
    const { props: pageProps, url } = usePage();
    const appSettings = pageProps.appSettings as any;
    const isTheme3 = appSettings?.theme_mode === 'theme3';

    // Loading state
    const isPageLoading = !props.stats || props.stats.prodi_count === undefined;

    // Route-based Tab System
    const tabFromUrl = new URL(url, window.location.origin).searchParams.get('tab');
    const activeTab = tabFromUrl || props.dashboardDefaultTab || 'overview';

    function changeTab(tab: string) {
        if (tab === activeTab) return;
        router.get(
            route('dashboard'),
            { ...props.filters, tab },
            { preserveState: true, replace: true }
        );
    }

    function changeFilter(key: string, value: string) {
        router.get(
            route('dashboard'),
            { ...props.filters, [key]: value, tab: activeTab },
            { preserveState: true, replace: true }
        );
    }

    if (isPageLoading) {
        return (
            <AuthenticatedLayout>
                <Head title="Dashboard" />
                <div className="py-2">
                    <div className="mx-auto max-w-7xl">
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-8">
                            <SkeletonCard /><SkeletonCard /><SkeletonCard /><SkeletonCard />
                        </div>
                        <SkeletonChart />
                        <div className="mt-8"><SkeletonChart /></div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col">
                    <h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">
                        Command Center Dashboard
                    </h2>
                    <p className="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">
                        {props.activeRole} • {props.scopeName}
                    </p>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className={isTheme3 ? 'main-content py-6' : 'py-2'}>
                <div className="mx-auto max-w-7xl">
                    {/* Header Institusi */}
                    {props.institutionAccreditation && (
                        <div className="mb-6">
                            {isTheme3 ? (
                                <div className="priority-card overflow-hidden">
                                    <div className="priority-card-header">🏛️ Akreditasi Perguruan Tinggi (AIPT)</div>
                                    <div className="priority-card-body flex flex-col md:flex-row md:items-center justify-between gap-8">
                                        <div>
                                            <h3 className="text-3xl font-black">{props.institutionAccreditation.nama}</h3>
                                            <p className="text-gray-500 text-sm mt-1 font-medium">
                                                Status Saat Ini: <span className="font-bold underline">{props.institutionAccreditation.status_saat_ini}</span> • Target: <span className="font-bold underline text-emerald-300">{props.institutionAccreditation.target}</span>
                                            </p>
                                            <p className="mt-4 text-[10px] uppercase tracking-widest opacity-60">Sinkronisasi terakhir: {props.institutionAccreditation.last_sync}</p>
                                        </div>
                                        <div className="flex items-center gap-10">
                                            <div className="text-center">
                                                <p className="text-[10px] uppercase tracking-widest opacity-70 mb-1 font-bold">Skor Simulasi</p>
                                                <p className="text-5xl font-black">{props.institutionAccreditation.skor_simulasi.toFixed(2)}</p>
                                            </div>
                                            <div className="h-16 w-px bg-white/20 hidden md:block"></div>
                                            <div className="text-center">
                                                <p className="text-[10px] uppercase tracking-widest opacity-70 mb-2 font-bold">Prediksi Predikat</p>
                                                <span className="inline-flex px-4 py-1.5 rounded-full text-sm font-black bg-white text-indigo-700 shadow-lg">UNGGUL</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-xl bg-white border border-gray-200 shadow-sm py-3 px-5 flex items-center justify-between">
                                    <div className="flex items-center gap-4">
                                        <div>
                                            <span className="text-base font-bold text-gray-900">{props.institutionAccreditation.nama}</span>
                                            <div className="flex items-center gap-2 mt-0.5">
                                                <span className="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border border-emerald-200 text-emerald-700 bg-emerald-50">
                                                    {props.institutionAccreditation.status_saat_ini}
                                                </span>
                                                <span className="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border border-indigo-200 text-indigo-700 bg-indigo-50">
                                                    Target: {props.institutionAccreditation.target}
                                                </span>
                                                <span className="text-[10px] text-gray-400 font-medium">Sync: {props.institutionAccreditation.last_sync}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <div className="text-right">
                                            <span className="text-3xl font-black text-gray-900 tabular-nums">
                                                {props.institutionAccreditation.skor_simulasi.toFixed(2)}
                                            </span>
                                        </div>
                                        <span className="inline-flex px-3 py-1 rounded-full text-xs font-black bg-indigo-600 text-white shadow-sm">UNGGUL</span>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Filter Section */}
                    <div className="mb-10 flex flex-wrap items-center justify-between gap-6 bg-white/50 p-4 rounded-xl border border-gray-100">
                        <div className="flex flex-wrap items-center gap-6">
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Lembaga Penilai</label>
                                <div className="flex bg-gray-100 p-1 rounded-lg">
                                    {props.lembaga_list.map((l) => (
                                        <button
                                            key={l.id}
                                            onClick={() => changeFilter('instrumen_id', String(l.id))}
                                            className={`px-4 py-1.5 text-xs font-black rounded-md transition-all ${
                                                props.selectedInstrumenId === l.id
                                                    ? 'bg-white text-indigo-600 shadow-sm scale-105'
                                                    : 'text-gray-500 hover:text-gray-700'
                                            }`}
                                        >
                                            {l.singkatan}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Periode Akademik</label>
                                <select
                                    value={props.selectedPeriode?.id || ''}
                                    onChange={(e) => changeFilter('periode_id', e.target.value)}
                                    className="rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:ring-indigo-500 focus:border-indigo-500 min-w-[200px]"
                                >
                                    <option value="">Semua Periode</option>
                                    {props.periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Sticky Toolbar */}
                    <div className="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-gray-200 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 mb-6 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            {props.peringatanStats && (
                                <PeringatanBadge
                                    critical={props.peringatanStats.critical}
                                    warning={props.peringatanStats.warning}
                                    info={props.peringatanStats.info}
                                    unread={props.peringatanStats.unread}
                                />
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <button onClick={() => window.print()} className="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold uppercase tracking-wider hover:bg-blue-700 transition-all shadow-sm">
                                Export PDF
                            </button>
                            <Link href={route('admin.templates.index')} className="px-4 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-black transition-all shadow-sm">
                                Template XL
                            </Link>
                            <Link href={route('portofolio.publikasi')} className="px-4 py-2 rounded-lg bg-rose-600 text-white text-xs font-bold uppercase tracking-wider hover:bg-rose-700 transition-all shadow-sm">
                                Import SINTA
                            </Link>
                        </div>
                    </div>

                    {/* Tab Navigation */}
                    <div className="mb-6">
                        <div className="flex bg-gray-100 p-1 rounded-lg w-fit">
                            {[
                                { id: 'overview', label: '📊 Overview' },
                                { id: 'prediksi', label: '🤖 Prediksi AI' },
                                { id: 'kinerja', label: '📈 Kinerja' },
                                { id: 'peringatan', label: '⚠️ Peringatan' },
                                { id: 'spmi', label: '📋 SPMI' },
                            ].map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => changeTab(tab.id)}
                                    className={`px-4 py-1.5 text-xs font-black rounded-md transition-all ${
                                        activeTab === tab.id
                                            ? 'bg-white text-indigo-600 shadow-sm scale-105'
                                            : 'text-gray-500 hover:text-gray-700'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Tab Content */}
                    <Suspense fallback={<TabFallback />}>
                        {activeTab === 'overview' && (
                            <OverviewTab
                                stats={props.stats}
                                prodiAccreditation={props.prodiAccreditation}
                                lembaga_list={props.lembaga_list}
                                selectedInstrumenId={props.selectedInstrumenId}
                                isTheme3={isTheme3}
                            />
                        )}
                        {activeTab === 'prediksi' && (
                            <PrediksiTab
                                latestPrediction={props.latestPrediction}
                                kriteriaStats={props.kriteriaStats}
                                lembaga_list={props.lembaga_list}
                                selectedInstrumenId={props.selectedInstrumenId}
                                filters={props.filters}
                            />
                        )}
                        {activeTab === 'kinerja' && (
                            <KinerjaTab
                                portofolioStats={props.portofolioStats}
                                isTheme3={isTheme3}
                            />
                        )}
                        {activeTab === 'peringatan' && (
                            <PeringatanTab peringatanStats={props.peringatanStats} />
                        )}
                        {activeTab === 'spmi' && (
                            <SpmiTab
                                spmi_overview={props.spmi_overview}
                                spmi_charts={props.spmi_charts}
                                spmi_ppepp={props.spmi_ppepp}
                                isTheme3={isTheme3}
                            />
                        )}
                    </Suspense>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
