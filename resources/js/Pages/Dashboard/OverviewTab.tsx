import { useState } from 'react';
import { Link } from '@inertiajs/react';

interface StatCardProps {
    label: string;
    value: number | string;
    color: string;
    href?: string;
    isTheme3?: boolean;
}

function StatCard({ label, value, color, href, isTheme3 }: StatCardProps) {
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

interface ProdiAccreditation {
    id: number;
    nama: string;
    fakultas: string;
    status_saat_ini: string;
    skor_simulasi: number;
    trend: number;
}

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface Props {
    stats: { dosen_count: number; prodi_count: number; fakultas_count: number };
    prodiAccreditation: ProdiAccreditation[];
    lembaga_list: Lembaga[];
    selectedInstrumenId: number;
    isTheme3: boolean;
}

export default function OverviewTab({ stats, prodiAccreditation, lembaga_list, selectedInstrumenId, isTheme3 }: Props) {
    const [searchProdi, setSearchProdi] = useState('');
    const [showAllProdi, setShowAllProdi] = useState(false);

    const getStatusColor = (status: string) => {
        const s = status.toLowerCase();
        if (s.includes('unggul')) return 'text-emerald-600 bg-emerald-50 border-emerald-200';
        if (s.includes('sekali')) return 'text-blue-600 bg-blue-50 border-blue-200';
        if (s.includes('baik')) return 'text-amber-600 bg-amber-50 border-amber-200';
        return 'text-gray-600 bg-gray-50 border-gray-200';
    };

    const filteredProdi = prodiAccreditation.filter(
        (p) =>
            p.nama.toLowerCase().includes(searchProdi.toLowerCase()) ||
            p.fakultas.toLowerCase().includes(searchProdi.toLowerCase())
    );
    const displayedProdi = showAllProdi ? filteredProdi : filteredProdi.slice(0, 5);

    return (
        <>
            {/* KPI Row */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-8">
                <StatCard label="Fakultas" value={stats.fakultas_count} color="bg-indigo-500" href={route('master-data.fakultas')} isTheme3={isTheme3} />
                <StatCard label="Program Studi" value={stats.prodi_count} color="bg-blue-500" href={route('master-data.prodi')} isTheme3={isTheme3} />
                <StatCard label="Dosen Aktif" value={stats.dosen_count} color="bg-teal-500" href={route('master-data.dosen')} isTheme3={isTheme3} />
                <StatCard
                    label="Rata-rata Skor"
                    value={prodiAccreditation.length > 0 ? (prodiAccreditation.reduce((sum, p) => sum + p.skor_simulasi, 0) / prodiAccreditation.length).toFixed(2) : '0.00'}
                    color="bg-purple-500"
                    isTheme3={isTheme3}
                />
            </div>

            {/* SPMI Quick Access */}
            <div className="mb-10">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                        📋 SPMI — Sistem Penjaminan Mutu Internal
                    </h3>
                    <Link href={route('spmi.dashboard')} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4 whitespace-nowrap">
                        Dashboard SPMI →
                    </Link>
                </div>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    {[
                        { label: 'Dashboard SPMI', route: 'spmi.dashboard', color: 'bg-indigo-500' },
                        { label: 'Audit Mutu', route: 'spmi.audit', color: 'bg-red-500' },
                        { label: 'Standar Mutu', route: 'spmi.standar-mutu', color: 'bg-blue-500' },
                        { label: 'CAPA', route: 'spmi.capa', color: 'bg-orange-500' },
                        { label: 'Siklus PPEPP', route: 'spmi.cycle', color: 'bg-emerald-500' },
                        { label: 'EDPS', route: 'spmi.edps', color: 'bg-teal-500' },
                        { label: 'RTM', route: 'spmi.rtm', color: 'bg-purple-500' },
                        { label: 'Dokumen Mutu', route: 'spmi.dokumen-mutu', color: 'bg-cyan-500' },
                        { label: 'Survey SPMI', route: 'spmi.survey', color: 'bg-pink-500' },
                        { label: 'Risk Register', route: 'spmi.risk', color: 'bg-amber-500' },
                    ].map((modul) => (
                        <Link
                            key={modul.route}
                            href={route(modul.route)}
                            className="rounded-xl bg-white p-4 shadow-sm border border-gray-100 hover:border-indigo-200 hover:-translate-y-1 transition-all group flex items-center gap-3"
                        >
                            <div className={`w-8 h-8 rounded-lg ${modul.color} flex items-center justify-center text-white text-sm font-bold shrink-0`}>
                                {modul.label.charAt(0)}
                            </div>
                            <p className="text-xs font-bold text-gray-700 group-hover:text-indigo-600 transition-colors leading-tight">
                                {modul.label}
                            </p>
                        </Link>
                    ))}
                </div>
            </div>

            {/* Prodi Accreditation Table */}
            <div className="mb-12">
                <div className="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 className="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                        Monitoring Prodi : {lembaga_list.find((l) => l.id === selectedInstrumenId)?.nama_lembaga}
                    </h3>
                    <div className="flex items-center gap-3">
                        <input
                            type="text"
                            placeholder="Cari prodi..."
                            value={searchProdi}
                            onChange={(e) => {
                                setSearchProdi(e.target.value);
                                setShowAllProdi(false);
                            }}
                            className="rounded-lg border-gray-200 bg-gray-50 text-xs font-medium shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-1.5 w-48"
                        />
                        <Link href={route('master-data.prodi')} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4 whitespace-nowrap">
                            Kelola Prodi
                        </Link>
                    </div>
                </div>
                <div className={isTheme3 ? 'table-wrapper' : 'rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden'}>
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
                                <tr>
                                    <td colSpan={5} className="px-6 py-16 text-center text-gray-400 italic font-medium">
                                        Tidak ada prodi yang cocok dengan pencarian.
                                    </td>
                                </tr>
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
    );
}
