import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

interface Metric {
    id: number;
    aspek: string;
    indikator: string;
    deskripsi: string | null;
    skor_saat_ini: number;
    status: string;
}

interface SpmiCycle {
    id: number;
    tahap: string;
    nama_siklus: string;
    persentase_selesai: number;
    status: string;
}

interface Props {
    aspects: Record<string, Metric[]>;
    spmi_cycles: SpmiCycle[];
    periode_list: { id: number; nama_periode: string }[];
    selected_periode_id: number;
}

export default function Index({ aspects, spmi_cycles, periode_list, selected_periode_id }: Props) {
    function changePeriode(e: React.ChangeEvent<HTMLSelectElement>) {
        router.get(route('aipt.index'), { periode_id: e.target.value });
    }

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'hijau': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
            case 'kuning': return 'bg-amber-100 text-amber-700 border-amber-200';
            case 'merah': return 'bg-rose-100 text-rose-700 border-rose-200';
            default: return 'bg-gray-100 text-gray-700 border-gray-200';
        }
    };

    const getProgressColor = (percent: number) => {
        if (percent >= 100) return 'bg-emerald-500';
        if (percent >= 50) return 'bg-indigo-500';
        return 'bg-amber-500';
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Akreditasi Perguruan Tinggi (AIPT) 4.0</h2>}
        >
            <Head title="AIPT Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Header Info */}
                    <div className="mb-8 rounded-lg bg-indigo-600 p-8 text-white shadow-lg">
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div>
                                <h1 className="text-3xl font-bold">ITSNU Pekalongan</h1>
                                <p className="mt-2 text-indigo-100">Status Akreditasi Saat Ini: <span className="font-bold">BAIK</span></p>
                                <p className="text-sm text-indigo-200 italic">Target 2026: UNGGUL (BAN-PT 4.0 Standard)</p>
                            </div>
                            <div className="flex gap-4">
                                <select 
                                    value={selected_periode_id} 
                                    onChange={changePeriode}
                                    className="rounded-lg border-none bg-white/20 text-white placeholder-white/60 focus:ring-white"
                                >
                                    {periode_list.map(p => (
                                        <option key={p.id} value={p.id} className="text-gray-900">{p.nama_periode}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        {/* Left Column: Aspects */}
                        <div className="lg:col-span-2 space-y-6">
                            <h3 className="text-lg font-bold text-gray-800 flex items-center gap-2">
                                📊 Capaian 4 Aspek Utama (BAN-PT 4.0)
                            </h3>
                            
                            {Object.entries(aspects).map(([name, metrics]) => (
                                <div key={name} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                    <div className="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                                        <h4 className="font-bold text-indigo-700 uppercase tracking-wider text-sm">{name}</h4>
                                        <span className="text-xs font-medium text-gray-500">{metrics.length} Indikator</span>
                                    </div>
                                    <div className="space-y-4">
                                        {metrics.length === 0 ? (
                                            <p className="text-sm text-gray-400 italic">Belum ada data indikator</p>
                                        ) : (
                                            metrics.map(m => (
                                                <div key={m.id} className="flex items-center justify-between group p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                                    <div>
                                                        <p className="text-sm font-semibold text-gray-800">{m.indikator}</p>
                                                        <p className="text-xs text-gray-500">{m.deskripsi || 'Sesuai standar operasional standar.'}</p>
                                                    </div>
                                                    <div className="flex items-center gap-3">
                                                        <div className="text-right">
                                                            <p className="text-sm font-black text-gray-900">{m.skor_saat_ini.toFixed(2)}</p>
                                                            <p className="text-[10px] text-gray-400">Skor AI</p>
                                                        </div>
                                                        <div className={`h-3 w-3 rounded-full border ${getStatusColor(m.status)}`}></div>
                                                    </div>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Right Column: SPMI Cycles */}
                        <div className="space-y-6">
                            <h3 className="text-lg font-bold text-gray-800 flex items-center gap-2">
                                🔄 Siklus SPMI (PPEPP)
                            </h3>
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div className="space-y-6">
                                    {spmi_cycles.map((cycle, idx) => (
                                        <div key={cycle.id} className="relative pl-8">
                                            {/* Timeline Line */}
                                            {idx !== spmi_cycles.length - 1 && (
                                                <div className="absolute left-[11px] top-8 h-full w-0.5 bg-gray-100"></div>
                                            )}
                                            {/* Timeline Node */}
                                            <div className={`absolute left-0 top-1 h-6 w-6 rounded-full border-4 border-white shadow-sm flex items-center justify-center text-[10px] text-white font-bold ${cycle.status === 'completed' ? 'bg-emerald-500' : 'bg-indigo-500'}`}>
                                                {idx + 1}
                                            </div>
                                            
                                            <div>
                                                <div className="flex items-center justify-between mb-1">
                                                    <span className="text-[10px] font-bold uppercase text-gray-400 tracking-widest">{cycle.tahap}</span>
                                                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded uppercase ${cycle.status === 'completed' ? 'text-emerald-600 bg-emerald-50' : 'text-indigo-600 bg-indigo-50'}`}>
                                                        {cycle.status}
                                                    </span>
                                                </div>
                                                <p className="text-sm font-bold text-gray-800">{cycle.nama_siklus}</p>
                                                
                                                <div className="mt-3">
                                                    <div className="flex items-center justify-between mb-1">
                                                        <span className="text-[10px] text-gray-500">Progress</span>
                                                        <span className="text-[10px] font-bold text-gray-700">{cycle.persentase_selesai}%</span>
                                                    </div>
                                                    <div className="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                                        <div 
                                                            className={`h-full rounded-full transition-all duration-500 ${getProgressColor(cycle.persentase_selesai)}`}
                                                            style={{ width: `${cycle.persentase_selesai}%` }}
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <button className="mt-8 w-full rounded-lg border-2 border-dashed border-gray-200 p-3 text-sm font-medium text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-all">
                                    + Tambah Siklus Baru
                                </button>
                            </div>

                            {/* Recommendation Card */}
                            <div className="rounded-xl bg-amber-50 border border-amber-200 p-6 shadow-sm">
                                <div className="flex items-center gap-3 mb-3">
                                    <span className="text-2xl">💡</span>
                                    <h4 className="font-bold text-amber-800">Rekomendasi AI</h4>
                                </div>
                                <p className="text-sm text-amber-700 leading-relaxed">
                                    Skor pada aspek **Relevansi** masih di angka 2.8. AI mendeteksi rendahnya tingkat Tracer Study pada prodi klaster Ekonomi. Segera instruksikan Kaprodi untuk validasi data alumni.
                                </p>
                                <button className="mt-4 text-xs font-bold text-amber-900 underline hover:no-underline">Tindak Lanjuti &rarr;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
