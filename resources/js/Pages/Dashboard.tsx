import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import PeringatanBadge from '@/Components/Agent/PeringatanBadge';
import PrediksiWidget from '@/Components/Agent/PrediksiWidget';
import RadarChart from '@/Components/Agent/RadarChart';
import KriteriaDetailModal from '@/Components/Agent/KriteriaDetailModal';

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
}

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
        <div className={`rounded-xl bg-white p-6 shadow-sm border border-gray-100 group hover:border-indigo-200 transition-all`}>
            <p className="text-sm font-bold text-gray-400 uppercase tracking-widest">{label}</p>
            <p className={`mt-2 text-4xl font-black text-gray-800`}>{value}</p>
            <div className={`mt-4 h-1 w-12 rounded-full ${color}`}></div>
        </div>
    );
    return href ? <Link href={href} className="block transition-transform hover:-translate-y-1">{card}</Link> : card;
}

export default function Dashboard({ stats, portofolioStats, bkdStats, recentPendidikan, recentPenelitian, periode_list, selectedPeriode, lembaga_list, selectedInstrumenId, peringatanStats, latestPrediction, kriteriaStats, prodiAccreditation, institutionAccreditation, filters, activeRole, scopeName, dashboardDefaultTab }: Props) {
    const { props, url } = usePage();
    const appSettings = props.appSettings as any;
    const isTheme3 = appSettings?.theme_mode === 'theme3';
    const [selectedKriteria, setSelectedKriteria] = useState<any>(null);
    const [showKinerja, setShowKinerja] = useState(false);
    const [searchProdi, setSearchProdi] = useState('');
    const [showAllProdi, setShowAllProdi] = useState(false);

    // Route-based Tab System (#7-8) — reads ?tab= from URL for back/forward/refresh support
    const tabFromUrl = new URL(url, window.location.origin).searchParams.get('tab');
    const activeTab = tabFromUrl || dashboardDefaultTab || 'overview';

    function changeTab(tab: string) {
        if (tab === activeTab) return;
        router.get(route('dashboard'), 
            { ...filters, tab }, 
            { preserveState: true, replace: true }
        );
    }

    useEffect(() => {
        if (activeTab === 'kinerja') setShowKinerja(true);
    }, [activeTab]);

    const handleSelectKriteria = (kriteria: any) => {
        setSelectedKriteria({
            ...kriteria,
            indikator: [
                { id: 1, kode: `${kriteria.kode}.1`, nama: 'Indikator 1', target: 100, tercapai: kriteria.skor, status: kriteria.skor >= 100 ? 'hijau' : kriteria.skor >= 60 ? 'kuning' : 'merah' },
                { id: 2, kode: `${kriteria.kode}.2`, nama: 'Indikator 2', target: 100, tercapai: Math.floor(kriteria.skor * 0.9), status: kriteria.skor >= 90 ? 'hijau' : kriteria.skor >= 54 ? 'kuning' : 'merah' },
                { id: 3, kode: `${kriteria.kode}.3`, nama: 'Indikator 3', target: 100, tercapai: Math.floor(kriteria.skor * 0.8), status: kriteria.skor >= 80 ? 'hijau' : kriteria.skor >= 48 ? 'kuning' : 'merah' },
            ]
        });
    };

    function changeFilter(key: string, value: string) {
        router.get(route('dashboard'), 
            { 
                ...filters,
                [key]: value,
                tab: activeTab
            }, 
            { preserveState: true, replace: true }
        );
    }

    const getStatusColor = (status: string) => {
        const s = status.toLowerCase();
        if (s.includes('unggul')) return 'text-emerald-600 bg-emerald-50 border-emerald-200';
        if (s.includes('sekali')) return 'text-blue-600 bg-blue-50 border-blue-200';
        if (s.includes('baik')) return 'text-amber-600 bg-amber-50 border-amber-200';
        return 'text-gray-600 bg-gray-50 border-gray-200';
    };

    const filteredProdi = prodiAccreditation.filter(p =>
        p.nama.toLowerCase().includes(searchProdi.toLowerCase()) ||
        p.fakultas.toLowerCase().includes(searchProdi.toLowerCase())
    );
    const displayedProdi = showAllProdi ? filteredProdi : filteredProdi.slice(0, 5);

    return (
        <AuthenticatedLayout header={
            <div className="flex flex-col">
                <h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Command Center Dashboard</h2>
                <p className="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">
                    {activeRole} • {scopeName}
                </p>
            </div>
        }>
            <Head title="Dashboard" />

            <div className={isTheme3 ? "main-content py-6" : "py-2"}>
                <div className="mx-auto max-w-7xl">
                    
                    {/* Header Institusi (Only if BAN-PT is active) */}
                    {institutionAccreditation && (
                        <div className="mb-6">
                            {isTheme3 ? (
                                <div className="priority-card overflow-hidden">
                                    <div className="priority-card-header">🏛️ Akreditasi Perguruan Tinggi (AIPT)</div>
                                    <div className="priority-card-body flex flex-col md:flex-row md:items-center justify-between gap-8">
                                        <div>
                                            <h3 className="text-3xl font-black">{institutionAccreditation.nama}</h3>
                                            <p className="text-gray-500 text-sm mt-1 font-medium">
                                                Status Saat Ini: <span className="font-bold underline">{institutionAccreditation.status_saat_ini}</span> • Target: <span className="font-bold underline text-emerald-300">{institutionAccreditation.target}</span>
                                            </p>
                                            <p className="mt-4 text-[10px] uppercase tracking-widest opacity-60">Sinkronisasi terakhir: {institutionAccreditation.last_sync}</p>
                                        </div>
                                        <div className="flex items-center gap-10">
                                            <div className="text-center">
                                                <p className="text-[10px] uppercase tracking-widest opacity-70 mb-1 font-bold">Skor Simulasi</p>
                                                <p className="text-5xl font-black">{institutionAccreditation.skor_simulasi.toFixed(2)}</p>
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
                                            <span className="text-base font-bold text-gray-900">{institutionAccreditation.nama}</span>
                                            <div className="flex items-center gap-2 mt-0.5">
                                                <span className="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border border-emerald-200 text-emerald-700 bg-emerald-50">{institutionAccreditation.status_saat_ini}</span>
                                                <span className="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border border-indigo-200 text-indigo-700 bg-indigo-50">Target: {institutionAccreditation.target}</span>
                                                <span className="text-[10px] text-gray-400 font-medium">Sync: {institutionAccreditation.last_sync}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <div className="text-right">
                                            <span className="text-3xl font-black text-gray-900 tabular-nums">{institutionAccreditation.skor_simulasi.toFixed(2)}</span>
                                        </div>
                                        <span className="inline-flex px-3 py-1 rounded-full text-xs font-black bg-indigo-600 text-white shadow-sm">UNGGUL</span>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Filter Section - Standardized for all themes */}
                    <div className="mb-10 flex flex-wrap items-center justify-between gap-6 bg-white/50 p-4 rounded-xl border border-gray-100">
                        <div className="flex flex-wrap items-center gap-6">
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Lembaga Penilai</label>
                                <div className="flex bg-gray-100 p-1 rounded-lg">
                                    {lembaga_list.map((l) => (
                                        <button
                                            key={l.id}
                                            onClick={() => changeFilter('instrumen_id', String(l.id))}
                                            className={`px-4 py-1.5 text-xs font-black rounded-md transition-all ${
                                                selectedInstrumenId === l.id 
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
                                    value={selectedPeriode?.id || ''}
                                    onChange={(e) => changeFilter('periode_id', e.target.value)}
                                    className="rounded-lg border-gray-200 bg-gray-50 text-xs font-bold shadow-sm focus:ring-indigo-500 focus:border-indigo-500 min-w-[200px]"
                                >
                                    <option value="">Semua Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Sticky Toolbar (#2+#3) */}
                    <div className="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-gray-200 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 mb-6 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            {peringatanStats && (
                                <PeringatanBadge critical={peringatanStats.critical} warning={peringatanStats.warning} info={peringatanStats.info} unread={peringatanStats.unread} />
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

                    {/* Tab Navigation (#7-8) */}
                    <div className="mb-6">
                      <div className="flex bg-gray-100 p-1 rounded-lg w-fit">
                        {[
                          { id: 'overview', label: '📊 Overview' },
                          { id: 'prediksi', label: '🤖 Prediksi AI' },
                          { id: 'kinerja', label: '📈 Kinerja' },
                          { id: 'peringatan', label: '⚠️ Peringatan' },
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

                    {/* Overview Tab */}
                    {activeTab === 'overview' && (
                    <>
                    {/* KPI Row - Master Data (#4) */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-8">
                        <StatCard label="Fakultas" value={stats.fakultas_count} color="bg-indigo-500" href={route('master-data.fakultas')} isTheme3={isTheme3} />
                        <StatCard label="Program Studi" value={stats.prodi_count} color="bg-blue-500" href={route('master-data.prodi')} isTheme3={isTheme3} />
                        <StatCard label="Dosen Aktif" value={stats.dosen_count} color="bg-teal-500" href={route('master-data.dosen')} isTheme3={isTheme3} />
                        <StatCard label="Rata-rata Skor" value={prodiAccreditation.length > 0 ? (prodiAccreditation.reduce((sum, p) => sum + p.skor_simulasi, 0) / prodiAccreditation.length).toFixed(2) : '0.00'} color="bg-purple-500" isTheme3={isTheme3} />
                    </div>

                    {/* Ringkasan Akreditasi Semua Prodi (#6) */}
                    <div className="mb-12">
                        <div className="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                                Monitoring Prodi : {lembaga_list.find(l => l.id === selectedInstrumenId)?.nama_lembaga}
                            </h3>
                            <div className="flex items-center gap-3">
                                <input
                                    type="text"
                                    placeholder="Cari prodi..."
                                    value={searchProdi}
                                    onChange={(e) => { setSearchProdi(e.target.value); setShowAllProdi(false); }}
                                    className="rounded-lg border-gray-200 bg-gray-50 text-xs font-medium shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-1.5 w-48"
                                />
                                <Link href={route('master-data.prodi')} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4 whitespace-nowrap">
                                    Kelola Prodi
                                </Link>
                            </div>
                        </div>
                        <div className={isTheme3 ? "table-wrapper" : "rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden"}>
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-gray-50/50 border-b border-gray-100 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                        <th className="px-6 py-4">Program Studi</th>
                                        <th className="px-6 py-4">Fakultas</th>
                                        <th className="px-6 py-4 text-center">Status Akreditasi</th>
                                        <th className="px-6 py-4 text-center">Simulasi AI (Real-Data)</th>
                                        <th className="px-6 py-4 text-right">Trend Kinerja</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50">
                                    {filteredProdi.length === 0 ? (
                                        <tr><td colSpan={5} className="px-6 py-16 text-center text-gray-400 italic font-medium">Tidak ada prodi yang cocok dengan pencarian.</td></tr>
                                    ) : (
                                        displayedProdi.map((prodi) => (
                                            <tr key={prodi.id} className="hover:bg-indigo-50/30 transition-all group">
                                                <td className="px-6 py-5 whitespace-nowrap">
                                                    <div className="font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{prodi.nama}</div>
                                                </td>
                                                <td className="px-6 py-5 whitespace-nowrap text-xs font-bold text-gray-500 uppercase tracking-tight">{prodi.fakultas}</td>
                                                <td className="px-6 py-5 whitespace-nowrap text-center">
                                                    <span className={`inline-flex px-3 py-1 rounded-full text-[10px] font-black border ${getStatusColor(prodi.status_saat_ini)}`}>
                                                        {prodi.status_saat_ini}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-5 whitespace-nowrap text-center">
                                                    <span className={`text-xl font-black tabular-nums ${prodi.skor_simulasi > 3 ? 'text-emerald-600' : 'text-indigo-600'}`}>
                                                        {prodi.skor_simulasi > 0 ? prodi.skor_simulasi.toFixed(2) : '0.00'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-5 whitespace-nowrap text-right">
                                                    <span className={`inline-flex items-center gap-1 text-[10px] font-black px-2 py-0.5 rounded ${prodi.trend >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'}`}>
                                                        {prodi.trend >= 0 ? '▲' : '▼'} {(prodi.trend * 100).toFixed(1)}%
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        {filteredProdi.length > 5 && (
                            <div className="mt-3 text-center">
                                <button
                                    onClick={() => setShowAllProdi(!showAllProdi)}
                                    className="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4"
                                >
                                    {showAllProdi ? '▲ Sembunyikan' : `▼ Lihat semua ${filteredProdi.length} prodi`}
                                </button>
                            </div>
                        )}
                    </div>
                    </>
                    )}

                    {/* Prediksi AI Tab */}
                    {activeTab === 'prediksi' && (
                    <>
                    {/* AI Agent Analysis Grid */}
                    <div className="mb-12">
                        <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Analisis Agent AI : {lembaga_list.find(l => l.id === selectedInstrumenId)?.singkatan}</h3>
                        
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-12 items-stretch">
                            <div className="lg:col-span-7">
                                <div className="h-full">
                                    {latestPrediction ? (
                                        <PrediksiWidget
                                            skor_prediksi={latestPrediction.skor_prediksi}
                                            prob_unggul={latestPrediction.prob_unggul}
                                            prob_baik_sekali={latestPrediction.prob_baik_sekali}
                                            prob_baik={latestPrediction.prob_baik}
                                            last_updated={latestPrediction.created_at}
                                            showRunButton={true}
                                            filters={{
                                                periode_id: filters.periode_id ? Number(filters.periode_id) : undefined,
                                                instrumen_id: filters.instrumen_id ? Number(filters.instrumen_id) : undefined
                                            }}
                                        />
                                    ) : (
                                        <PrediksiWidget 
                                            skor_prediksi={0} 
                                            prob_unggul={0} 
                                            prob_baik_sekali={0} 
                                            prob_baik={0} 
                                            showRunButton={true} 
                                            filters={{
                                                periode_id: filters.periode_id ? Number(filters.periode_id) : undefined,
                                                instrumen_id: filters.instrumen_id ? Number(filters.instrumen_id) : undefined
                                            }}
                                        />
                                    )}
                                </div>
                            </div>

                            <div className="lg:col-span-5">
                                <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100 h-full flex flex-col items-center justify-center">
                                    <RadarChart 
                                        data={kriteriaStats} 
                                        title="Capaian Kriteria (9 Kriteria / 4 Aspek)" 
                                        showTarget={true}
                                        onSelectKriteria={handleSelectKriteria}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    </>
                    )}

                    {/* Kinerja Tab */}
                    {activeTab === 'kinerja' && (
                    <>
                    <div className="mb-12">
                        <div className="flex items-center gap-2 mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                            <span>▼ Akumulasi Kinerja Tridharma</span>
                        </div>
                        <div className={isTheme3 ? "kpi-grid" : "grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8"}>
                            <StatCard label="Pendidikan" value={portofolioStats.pendidikan_count} color="bg-blue-400" href={route('portofolio.pendidikan')} isTheme3={isTheme3} />
                            <StatCard label="Penelitian" value={portofolioStats.penelitian_count} color="bg-emerald-400" href={route('portofolio.penelitian')} isTheme3={isTheme3} />
                            <StatCard label="Publikasi" value={portofolioStats.publikasi_count} color="bg-purple-400" href={route('portofolio.publikasi')} isTheme3={isTheme3} />
                            <StatCard label="PKM" value={portofolioStats.pkm_count} color="bg-orange-400" href={route('portofolio.pkm')} isTheme3={isTheme3} />
                            <StatCard label="Penunjang" value={portofolioStats.penunjang_count} color="bg-teal-400" href={route('portofolio.penunjang')} isTheme3={isTheme3} />
                            <StatCard label="BKD" value={portofolioStats.bkd_count} color="bg-rose-400" href={route('bkd')} isTheme3={isTheme3} />
                            <StatCard label="Bimbingan" value={portofolioStats.bimbingan_count} color="bg-cyan-400" href={route('bimbingan')} isTheme3={isTheme3} />
                            <StatCard label="Dokumen" value={portofolioStats.dokumen_count} color="bg-amber-400" href={route('dokumen')} isTheme3={isTheme3} />
                        </div>
                    </div>
                    </>
                    )}

                    {/* Peringatan Tab */}
                    {activeTab === 'peringatan' && (
                    <>
                    <div className="mb-12">
                        <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Peringatan & Notifikasi</h3>
                        <div className="rounded-xl bg-white p-8 shadow-sm border border-gray-100 text-center">
                            {peringatanStats ? (
                                <div className="flex items-center justify-center gap-8">
                                    <div className="text-center">
                                        <div className="text-5xl font-black text-red-600">{peringatanStats.critical}</div>
                                        <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Critical</div>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-5xl font-black text-amber-600">{peringatanStats.warning}</div>
                                        <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Warning</div>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-5xl font-black text-blue-600">{peringatanStats.info}</div>
                                        <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Info</div>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-5xl font-black text-gray-600">{peringatanStats.unread}</div>
                                        <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Unread</div>
                                    </div>
                                </div>
                            ) : (
                                <p className="text-gray-400 italic font-medium">Belum ada data peringatan.</p>
                            )}
                            <div className="mt-6">
                                <Link href={route('peringatan')} className="inline-flex px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                                    Lihat Detail Peringatan →
                                </Link>
                            </div>
                        </div>
                    </div>
                    </>
                    )}

                </div>
            </div>

            <KriteriaDetailModal 
                kriteria={selectedKriteria} 
                isOpen={!!selectedKriteria} 
                onClose={() => setSelectedKriteria(null)} 
            />
        </AuthenticatedLayout>
    );
}
