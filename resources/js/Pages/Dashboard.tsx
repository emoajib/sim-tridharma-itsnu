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

interface VerifikasiStats {
    valid: number;
    need_review: number;
    invalid: number;
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
    recentPublikasi: RecentItem[];
    recentPkm: RecentItem[];
    periode_list: Periode[];
    selectedPeriode: Periode | null;
    peringatanStats?: PeringatanStats;
    verifikasiStats?: VerifikasiStats;
    latestPrediction?: LatestPrediction | null;
    prodiAccreditation: ProdiAccreditation[];
    institutionAccreditation: InstitutionAccreditation;
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
        <div className={`rounded-lg ${color} p-5 shadow-sm`}>
            <p className="text-3xl font-bold text-white">{value}</p>
            <p className="mt-1 text-sm font-medium text-white/80">{label}</p>
        </div>
    );
    return href ? <Link href={href} className="block transition hover:scale-105">{card}</Link> : card;
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

export default function Dashboard({ stats, portofolioStats, bkdStats, recentPendidikan, recentPenelitian, recentPublikasi, recentPkm, periode_list, selectedPeriode, peringatanStats, verifikasiStats, latestPrediction, prodiAccreditation, institutionAccreditation }: Props) {
    const { props } = usePage();
    const appSettings = props.appSettings as any;
    const isTheme3 = appSettings?.theme_mode === 'theme3';
    
    // State for Instrument Switcher
    const [instrument, setInstrument] = useState('banpt');

    function changePeriode(e: React.ChangeEvent<HTMLSelectElement>) {
        router.get(route('dashboard'), { periode_id: e.target.value || undefined }, { preserveState: true, replace: true });
    }

    const getStatusColor = (status: string) => {
        const s = status.toLowerCase();
        if (s.includes('unggul')) return 'text-emerald-600 bg-emerald-50 border-emerald-200';
        if (s.includes('sekali')) return 'text-blue-600 bg-blue-50 border-blue-200';
        if (s.includes('baik')) return 'text-amber-600 bg-amber-50 border-amber-200';
        return 'text-gray-600 bg-gray-50 border-gray-200';
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className={isTheme3 ? "main-content py-6" : "py-12"}>
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    
                    {/* Header Institusi (AIPT) */}
                    <div className="mb-8">
                        <div className={isTheme3 ? "priority-card overflow-hidden" : "rounded-lg bg-white p-6 shadow-sm border-l-4 border-indigo-600"}>
                            {isTheme3 && <div className="priority-card-header">🏛️ Akreditasi Perguruan Tinggi (AIPT)</div>}
                            <div className={`${isTheme3 ? "priority-card-body" : ""} flex flex-col md:flex-row md:items-center justify-between gap-6`}>
                                <div>
                                    <h3 className="text-2xl font-bold text-gray-900">{institutionAccreditation.nama}</h3>
                                    <p className="text-sm text-gray-500">Status Saat Ini: <span className="font-semibold text-indigo-600">{institutionAccreditation.status_saat_ini}</span> • Target: <span className="font-semibold text-emerald-600">{institutionAccreditation.target}</span></p>
                                    <p className="mt-1 text-xs text-gray-400">Sinkronisasi terakhir: {institutionAccreditation.last_sync}</p>
                                </div>
                                <div className="flex items-center gap-8">
                                    <div className="text-center">
                                        <p className="text-xs uppercase tracking-wider text-gray-500 font-semibold">Skor Simulasi</p>
                                        <p className="text-4xl font-black text-indigo-600">{institutionAccreditation.skor_simulasi.toFixed(2)}</p>
                                    </div>
                                    <div className="h-12 w-px bg-gray-200 hidden md:block"></div>
                                    <div className="text-center">
                                        <p className="text-xs uppercase tracking-wider text-gray-500 font-semibold">Prediksi Predikat</p>
                                        <span className="inline-flex mt-1 px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700">UNGGUL</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Filter Periode & Instrumen */}
                    <div className="mb-6 flex flex-wrap items-center gap-6">
                        <div className="flex items-center gap-3">
                            <label className="text-sm font-medium text-gray-700">Instrumen:</label>
                            <div className="flex rounded-lg shadow-sm">
                                <button
                                    onClick={() => setInstrument('banpt')}
                                    className={`px-4 py-2 text-sm font-medium rounded-l-lg border ${
                                        instrument === 'banpt' 
                                            ? 'bg-indigo-600 text-white border-indigo-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    }`}
                                >
                                    Standard BAN-PT
                                </button>
                                <button
                                    onClick={() => setInstrument('infokom')}
                                    className={`px-4 py-2 text-sm font-medium border-t border-b ${
                                        instrument === 'infokom' 
                                            ? 'bg-indigo-600 text-white border-indigo-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    }`}
                                >
                                    LAM INFOKOM
                                </button>
                                <button
                                    onClick={() => setInstrument('lamemba')}
                                    className={`px-4 py-2 text-sm font-medium rounded-r-lg border ${
                                        instrument === 'lamemba' 
                                            ? 'bg-indigo-600 text-white border-indigo-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    }`}
                                >
                                    LAMEMBA
                                </button>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <label className="text-sm font-medium text-gray-700">Periode:</label>
                            <select
                                value={selectedPeriode?.id || ''}
                                onChange={changePeriode}
                                className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 min-w-[200px]"
                            >
                                <option value="">Semua Periode</option>
                                {periode_list.map((p) => (
                                    <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Stats Cards - Master Data */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Master Data</h3>
                        <div className={isTheme3 ? "kpi-grid" : "grid grid-cols-1 gap-4 sm:grid-cols-3"}>
                            <StatCard label="Fakultas" value={stats.fakultas_count} color="bg-indigo-600" href={route('master-data.fakultas')} isTheme3={isTheme3} />
                            <StatCard label="Program Studi" value={stats.prodi_count} color="bg-indigo-500" href={route('master-data.prodi')} isTheme3={isTheme3} />
                            <StatCard label="Dosen" value={stats.dosen_count} color="bg-indigo-400" href={route('master-data.dosen')} isTheme3={isTheme3} />
                        </div>
                    </div>

                    {/* Ringkasan Akreditasi Semua Prodi */}
                    <div className="mb-8">
                        <div className="mb-3 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-800">Status Akreditasi Program Studi</h3>
                            <Link href={route('master-data.prodi')} className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Kelola Prodi</Link>
                        </div>
                        <div className={isTheme3 ? "table-wrapper overflow-x-auto" : "rounded-lg bg-white shadow-sm overflow-hidden border border-gray-200"}>
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                                        <th className="px-6 py-3">Program Studi</th>
                                        <th className="px-6 py-3">Fakultas</th>
                                        <th className="px-6 py-3 text-center">Status Saat Ini</th>
                                        <th className="px-6 py-3 text-center">Skor Simulasi AI</th>
                                        <th className="px-6 py-3 text-right">Trend</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {prodiAccreditation.map((prodi) => (
                                        <tr key={prodi.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="font-bold text-gray-900">{prodi.nama}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{prodi.fakultas}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                                <span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold border ${getStatusColor(prodi.status_saat_ini)}`}>
                                                    {prodi.status_saat_ini}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                                <span className={`font-mono font-bold ${prodi.skor_simulasi > 3 ? 'text-emerald-600' : 'text-indigo-600'}`}>
                                                    {prodi.skor_simulasi > 0 ? prodi.skor_simulasi.toFixed(2) : '-'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">
                                                <span className={`text-xs font-bold ${prodi.trend >= 0 ? 'text-emerald-500' : 'text-rose-500'}`}>
                                                    {prodi.trend >= 0 ? '▲' : '▼'} {(prodi.trend * 100).toFixed(1)}%
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* AI Agent Dashboard */}
                    {(peringatanStats || latestPrediction) && (
                        <div className="mb-8">
                            <div className="mb-3 flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-gray-800">AI Agent Analysis (Real-time)</h3>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={route('peringatan')}
                                        className="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                                    >
                                        Lihat Detail Alert
                                    </Link>
                                </div>
                            </div>
                            
                            {peringatanStats && (
                                <div className="mb-4">
                                    <PeringatanBadge
                                        critical={peringatanStats.critical}
                                        warning={peringatanStats.warning}
                                        info={peringatanStats.info}
                                        unread={peringatanStats.unread}
                                    />
                                </div>
                            )}

                            <div className={isTheme3 ? "priority-grid" : "grid grid-cols-1 gap-6 lg:grid-cols-2"}>
                                {/* Prediksi Widget */}
                                <div className={isTheme3 ? "priority-card" : ""}>
                                    {isTheme3 && <div className="priority-card-header">📊 Prediksi Akreditasi Terpusat</div>}
                                    <div className={isTheme3 ? "priority-card-body" : ""}>
                                        {latestPrediction ? (
                                            <PrediksiWidget
                                                skor_prediksi={latestPrediction.skor_prediksi}
                                                prob_unggul={latestPrediction.prob_unggul}
                                                prob_baik_sekali={latestPrediction.prob_baik_sekali}
                                                prob_baik={latestPrediction.prob_baik}
                                                last_updated={latestPrediction.created_at}
                                                showRunButton={true}
                                            />
                                        ) : (
                                            <PrediksiWidget
                                                skor_prediksi={0}
                                                prob_unggul={0}
                                                prob_baik_sekali={0}
                                                prob_baik={0}
                                                showRunButton={true}
                                            />
                                        )}
                                    </div>
                                </div>

                                {/* Radar Chart */}
                                <div className={isTheme3 ? "chart-container" : ""}>
                                    <RadarChart
                                        data={[]}
                                        title="Rata-rata Capaian 9 Kriteria Institusi"
                                        showTarget={true}
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Stats Cards - Portofolio */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Agregat Portofolio Tridharma</h3>
                        <div className={isTheme3 ? "kpi-grid" : "grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8"}>
                            <StatCard label="Pendidikan" value={portofolioStats.pendidikan_count} color="bg-blue-600" href={route('portofolio.pendidikan')} isTheme3={isTheme3} />
                            <StatCard label="Penelitian" value={portofolioStats.penelitian_count} color="bg-green-600" href={route('portofolio.penelitian')} isTheme3={isTheme3} />
                            <StatCard label="Publikasi" value={portofolioStats.publikasi_count} color="bg-purple-600" href={route('portofolio.publikasi')} isTheme3={isTheme3} />
                            <StatCard label="PKM" value={portofolioStats.pkm_count} color="bg-orange-600" href={route('portofolio.pkm')} isTheme3={isTheme3} />
                            <StatCard label="Penunjang" value={portofolioStats.penunjang_count} color="bg-teal-600" href={route('portofolio.penunjang')} isTheme3={isTheme3} />
                            <StatCard label="BKD" value={portofolioStats.bkd_count} color="bg-rose-600" href={route('bkd')} isTheme3={isTheme3} />
                            <StatCard label="Bimbingan" value={portofolioStats.bimbingan_count} color="bg-cyan-600" href={route('bimbingan')} isTheme3={isTheme3} />
                            <StatCard label="Dokumen" value={portofolioStats.dokumen_count} color="bg-amber-600" href={route('dokumen')} isTheme3={isTheme3} />
                        </div>
                    </div>

                    {/* Recent Activities */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div className={isTheme3 ? "table-wrapper" : "rounded-lg bg-white p-6 shadow-sm"}>
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">Kegiatan Pendidikan Terbaru</h3>
                            {recentPendidikan.length === 0 ? (
                                <p className="text-sm text-gray-500">Belum ada data</p>
                            ) : (
                                <ul className="divide-y divide-gray-200">
                                    {recentPendidikan.map((item) => (
                                        <li key={item.id} className="py-2">
                                            <p className="text-sm font-medium text-gray-900">{item.nama_kegiatan}</p>
                                            <p className="text-xs text-gray-500">{item.dosen?.nama_depan} • {formatDate(item.created_at)}</p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>

                        <div className={isTheme3 ? "table-wrapper" : "rounded-lg bg-white p-6 shadow-sm"}>
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">Penelitian Terbaru</h3>
                            {recentPenelitian.length === 0 ? (
                                <p className="text-sm text-gray-500">Belum ada data</p>
                            ) : (
                                <ul className="divide-y divide-gray-200">
                                    {recentPenelitian.map((item) => (
                                        <li key={item.id} className="py-2">
                                            <p className="text-sm font-medium text-gray-900 truncate">{item.judul_penelitian}</p>
                                            <p className="text-xs text-gray-500">{item.dosen?.nama_depan} • {formatDate(item.created_at)}</p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div className="mt-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Akses Cepat</h3>
                        <div className="flex flex-wrap gap-3">
                            <Link href={route('portofolio')} className="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200">Dashboard Portofolio</Link>
                            <Link href={route('bkd')} className="rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-200">Input BKD</Link>
                            <Link href={route('dokumen')} className="rounded-lg bg-purple-100 px-4 py-2 text-sm font-medium text-purple-700 hover:bg-purple-200">Upload Dokumen</Link>
                            <Link href={route('bimbingan')} className="rounded-lg bg-teal-100 px-4 py-2 text-sm font-medium text-teal-700 hover:bg-teal-200">Bimbingan Mahasiswa</Link>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
