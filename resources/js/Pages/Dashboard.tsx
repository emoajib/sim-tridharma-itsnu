import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

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
}

function StatCard({ label, value, color, href }: { label: string; value: number; color: string; href?: string }) {
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

export default function Dashboard({ stats, portofolioStats, bkdStats, recentPendidikan, recentPenelitian, recentPublikasi, recentPkm, periode_list, selectedPeriode }: Props) {
    function changePeriode(e: React.ChangeEvent<HTMLSelectElement>) {
        router.get(route('dashboard'), { periode_id: e.target.value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Filter Periode */}
                    <div className="mb-6 flex items-center gap-4">
                        <label className="text-sm font-medium text-gray-700">Periode:</label>
                        <select
                            value={selectedPeriode?.id || ''}
                            onChange={changePeriode}
                            className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Semua Periode</option>
                            {periode_list.map((p) => (
                                <option key={p.id} value={p.id}>{p.nama_periode}</option>
                            ))}
                        </select>
                    </div>

                    {/* Stats Cards - Master Data */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Master Data</h3>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <StatCard label="Fakultas" value={stats.fakultas_count} color="bg-indigo-600" href={route('master-data.fakultas')} />
                            <StatCard label="Program Studi" value={stats.prodi_count} color="bg-indigo-500" href={route('master-data.prodi')} />
                            <StatCard label="Dosen" value={stats.dosen_count} color="bg-indigo-400" href={route('master-data.dosen')} />
                        </div>
                    </div>

                    {/* Stats Cards - Portofolio */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Portofolio Tridharma</h3>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                            <StatCard label="Pendidikan" value={portofolioStats.pendidikan_count} color="bg-blue-600" href={route('portofolio.pendidikan')} />
                            <StatCard label="Penelitian" value={portofolioStats.penelitian_count} color="bg-green-600" href={route('portofolio.penelitian')} />
                            <StatCard label="Publikasi" value={portofolioStats.publikasi_count} color="bg-purple-600" href={route('portofolio.publikasi')} />
                            <StatCard label="PKM" value={portofolioStats.pkm_count} color="bg-orange-600" href={route('portofolio.pkm')} />
                            <StatCard label="Penunjang" value={portofolioStats.penunjang_count} color="bg-teal-600" href={route('portofolio.penunjang')} />
                            <StatCard label="BKD" value={portofolioStats.bkd_count} color="bg-rose-600" href={route('bkd')} />
                            <StatCard label="Bimbingan" value={portofolioStats.bimbingan_count} color="bg-cyan-600" href={route('bimbingan')} />
                            <StatCard label="Dokumen" value={portofolioStats.dokumen_count} color="bg-amber-600" href={route('dokumen')} />
                        </div>
                    </div>

                    {/* BKD Overview */}
                    <div className="mb-8">
                        <h3 className="mb-3 text-lg font-semibold text-gray-800">Rekap BKD</h3>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-5">
                            <StatCard label="Total BKD" value={bkdStats.total} color="bg-gray-600" />
                            <StatCard label="Draft" value={bkdStats.draft} color="bg-gray-400" />
                            <StatCard label="Diajukan" value={bkdStats.diajukan} color="bg-yellow-500" />
                            <StatCard label="Disetujui" value={bkdStats.disetujui} color="bg-green-500" />
                            <StatCard label="Rata SKS" value={Math.round(bkdStats.rata_sks * 10) / 10} color="bg-blue-500" />
                        </div>
                    </div>

                    {/* Recent Activities */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div className="rounded-lg bg-white p-6 shadow-sm">
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

                        <div className="rounded-lg bg-white p-6 shadow-sm">
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

                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">Publikasi Terbaru</h3>
                            {recentPublikasi.length === 0 ? (
                                <p className="text-sm text-gray-500">Belum ada data</p>
                            ) : (
                                <ul className="divide-y divide-gray-200">
                                    {recentPublikasi.map((item) => (
                                        <li key={item.id} className="py-2">
                                            <p className="text-sm font-medium text-gray-900 truncate">{item.judul_publikasi}</p>
                                            <p className="text-xs text-gray-500">{item.dosen?.nama_depan} • {formatDate(item.created_at)}</p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>

                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">PKM Terbaru</h3>
                            {recentPkm.length === 0 ? (
                                <p className="text-sm text-gray-500">Belum ada data</p>
                            ) : (
                                <ul className="divide-y divide-gray-200">
                                    {recentPkm.map((item) => (
                                        <li key={item.id} className="py-2">
                                            <p className="text-sm font-medium text-gray-900 truncate">{item.judul_pkm}</p>
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
