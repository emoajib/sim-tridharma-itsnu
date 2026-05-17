import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PeringatanBadge from '@/Components/Agent/PeringatanBadge';
import PrediksiWidget from '@/Components/Agent/PrediksiWidget';
import RadarChart from '@/Components/Agent/RadarChart';

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
    prodiAccreditation: ProdiAccreditation[];
    institutionAccreditation: InstitutionAccreditation | null;
    filters: { periode_id?: string; instrumen_id?: string };
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

export default function Dashboard({ stats, portofolioStats, bkdStats, recentPendidikan, recentPenelitian, periode_list, selectedPeriode, lembaga_list, selectedInstrumenId, peringatanStats, latestPrediction, prodiAccreditation, institutionAccreditation, filters }: Props) {
    const { props } = usePage();
    const appSettings = props.appSettings as any;
    const isTheme3 = appSettings?.theme_mode === 'theme3';

    function changeFilter(key: string, value: string) {
        router.get(route('dashboard'), 
            { 
                ...filters,
                [key]: value 
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

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Command Center Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className={isTheme3 ? "main-content py-6" : "py-2"}>
                <div className="mx-auto max-w-7xl">
                    
                    {/* Header Institusi (Only if BAN-PT is active) */}
                    {institutionAccreditation && (
                        <div className="mb-8">
                            <div className={isTheme3 ? "priority-card overflow-hidden" : "rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 p-8 shadow-xl text-white relative overflow-hidden"}>
                                {!isTheme3 && <div className="absolute top-0 right-0 p-8 opacity-10 text-9xl font-black">ITSNU</div>}
                                {isTheme3 && <div className="priority-card-header">🏛️ Akreditasi Perguruan Tinggi (AIPT)</div>}
                                <div className={`${isTheme3 ? "priority-card-body" : "relative z-10"} flex flex-col md:flex-row md:items-center justify-between gap-8`}>
                                    <div>
                                        <h3 className="text-3xl font-black">{institutionAccreditation.nama}</h3>
                                        <p className={`${isTheme3 ? 'text-gray-500' : 'text-indigo-100'} text-sm mt-1 font-medium`}>
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

                    {/* Stats Cards - Master Data */}
                    <div className="mb-12">
                        <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Master Data Overview</h3>
                        <div className={isTheme3 ? "kpi-grid" : "grid grid-cols-1 gap-6 sm:grid-cols-3"}>
                            <StatCard label="Total Fakultas" value={stats.fakultas_count} color="bg-indigo-500" href={route('master-data.fakultas')} isTheme3={isTheme3} />
                            <StatCard label="Program Studi" value={stats.prodi_count} color="bg-blue-500" href={route('master-data.prodi')} isTheme3={isTheme3} />
                            <StatCard label="Dosen Aktif" value={stats.dosen_count} color="bg-teal-500" href={route('master-data.dosen')} isTheme3={isTheme3} />
                        </div>
                    </div>

                    {/* Ringkasan Akreditasi Semua Prodi */}
                    <div className="mb-12">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Monitoring Prodi : {lembaga_list.find(l => l.id === selectedInstrumenId)?.nama_lembaga}</h3>
                            <Link href={route('master-data.prodi')} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4">Kelola Ploting & Prodi</Link>
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
                                    {prodiAccreditation.length === 0 ? (
                                        <tr><td colSpan={5} className="px-6 py-16 text-center text-gray-400 italic font-medium">Belum ada prodi yang di-ploting ke lembaga ini.</td></tr>
                                    ) : (
                                        prodiAccreditation.map((prodi) => (
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
                    </div>

                    {/* AI Agent Analysis Grid */}
                    <div className="mb-12">
                        <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Analisis Agent AI : {lembaga_list.find(l => l.id === selectedInstrumenId)?.singkatan}</h3>
                        
                        {peringatanStats && (
                            <div className="mb-6">
                                <PeringatanBadge
                                    critical={peringatanStats.critical}
                                    warning={peringatanStats.warning}
                                    info={peringatanStats.info}
                                    unread={peringatanStats.unread}
                                />
                            </div>
                        )}

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
                                    <RadarChart data={[]} title="Capaian Kriteria (9 Kriteria / 4 Aspek)" showTarget={true} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Akumulasi Kinerja Section */}
                    <div className="mb-12">
                        <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Akumulasi Kinerja Tridharma</h3>
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

                    {/* Action Bar / Quick Access */}
                    <div className="mt-8 border-t border-gray-100 pt-8">
                        <div className="flex flex-wrap gap-4">
                            <Link href={route('admin.templates.index')} className="px-6 py-2.5 rounded-xl bg-gray-900 text-white text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200">Download Template XL</Link>
                            <Link href={route('portofolio.publikasi')} className="px-6 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-700 transition-all shadow-lg shadow-rose-200">Import SINTA Massal</Link>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
