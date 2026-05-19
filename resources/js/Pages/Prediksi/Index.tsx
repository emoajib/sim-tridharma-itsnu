import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface PrediksiItem {
    id: number;
    prodi_id: number;
    periode_id: number | null;
    skor_prediksi: number;
    confidence_interval: number | null;
    probabilitas_unggul: number | null;
    probabilitas_baik_sekali: number | null;
    probabilitas_baik: number | null;
    detail_data: any;
    created_at: string;
    prodi?: { id: number; nama_prodi: string };
    periode?: { id: number; nama_periode: string };
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface Stats {
    total: number;
    unggul: number;
    baik_sekali: number;
    prodi_terbaik: PrediksiItem | null;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    prediksi: PaginatedData<PrediksiItem>;
    prodi_list: Prodi[];
    periode_list: Periode[];
    stats: Stats;
    filters: {
        prodi_id: number | null;
        periode_id: number | null;
    };
}

function getPredicateColor(prob: number | null): string {
    if (!prob) return 'bg-gray-100 text-gray-600';
    if (prob >= 50) return 'bg-green-100 text-green-700';
    if (prob >= 30) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
}

function getPredicateLabel(probUnggul: number | null, probBaikSekali: number | null): string {
    if (probUnggul !== null && probUnggul >= 50) return 'UNGGUL';
    if (probBaikSekali !== null && probBaikSekali >= 50) return 'BAIK SEKALI';
    if (probUnggul !== null || probBaikSekali !== null) return 'BAIK';
    return 'BELUM';
}

export default function Index({ prediksi, prodi_list, periode_list, stats, filters }: Props) {
    const [selectedProdi, setSelectedProdi] = useState<number | ''>(filters.prodi_id || '');
    const [selectedPeriode, setSelectedPeriode] = useState<number | ''>(filters.periode_id || '');
    const [showRunModal, setShowRunModal] = useState(false);
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('prediksi'), {
                prodi_id: selectedProdi || undefined,
                periode_id: selectedPeriode || undefined,
            }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [selectedProdi, selectedPeriode]);

    function runAgent() {
        router.post(route('prediksi.run'), {
            prodi_id: selectedProdi || undefined,
            periode_id: selectedPeriode || undefined,
        }, {
            onSuccess: () => {
                setShowRunModal(false);
            },
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="Agent Prediksi Akreditasi" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900">Agent Prediksi Akreditasi</h1>
                            <p className="text-sm text-gray-500 mt-1">
                                Prediksi skor dan probabilitas akreditasi menggunakan AI
                            </p>
                        </div>
                        <button
                            onClick={() => setShowRunModal(true)}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Jalankan AI
                        </button>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Total Prediksi</div>
                            <div className="text-2xl font-bold text-gray-900">{stats.total}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Prodi Prediksi Unggul</div>
                            <div className="text-2xl font-bold text-green-600">{stats.unggul}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Prodi Prediksi Baik Sekali</div>
                            <div className="text-2xl font-bold text-blue-600">{stats.baik_sekali}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-4">
                            <div className="text-sm text-gray-500">Prodi Tertinggi</div>
                            <div className="text-lg font-bold text-gray-900">
                                {stats.prodi_terbaik?.prodi?.nama_prodi || '-'}
                            </div>
                            <div className="text-sm text-indigo-600">
                                {stats.prodi_terbaik ? `${stats.prodi_terbaik.skor_prediksi}/4.00` : '-'}
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow mb-6">
                        <div className="p-4 border-b border-gray-200">
                            <div className="flex flex-wrap gap-4 items-center">
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Prodi</label>
                                    <select
                                        value={selectedProdi}
                                        onChange={(e) => setSelectedProdi(e.target.value ? Number(e.target.value) : '')}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Prodi</option>
                                        {prodi_list.map(p => (
                                            <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                                    <select
                                        value={selectedPeriode}
                                        onChange={(e) => setSelectedPeriode(e.target.value ? Number(e.target.value) : '')}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Periode</option>
                                        {periode_list.map(p => (
                                            <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skor Prediksi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Predikat</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prob. Unggul</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prob. Baik Sekali</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {prediksi.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-12 text-center text-gray-500">
                                            Belum ada data prediksi. Jalankan agent AI untuk memulai.
                                        </td>
                                    </tr>
                                ) : (
                                    prediksi.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {item.prodi?.nama_prodi || '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-500">
                                                    {item.periode?.nama_periode || '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-lg font-bold text-indigo-600">
                                                    {item.skor_prediksi?.toFixed(2) || '-'}
                                                </div>
                                                <div className="text-xs text-gray-400">/ 4.00</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 py-1 text-xs font-medium rounded-full ${getPredicateColor(item.probabilitas_unggul)}`}>
                                                    {getPredicateLabel(item.probabilitas_unggul, item.probabilitas_baik_sekali)}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-2">
                                                    <div className="w-24 bg-gray-200 rounded-full h-2">
                                                        <div
                                                            className="bg-green-500 h-2 rounded-full"
                                                            style={{ width: `${item.probabilitas_unggul || 0}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-sm text-gray-600">{item.probabilitas_unggul?.toFixed(1) || 0}%</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-2">
                                                    <div className="w-24 bg-gray-200 rounded-full h-2">
                                                        <div
                                                            className="bg-blue-500 h-2 rounded-full"
                                                            style={{ width: `${item.probabilitas_baik_sekali || 0}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-sm text-gray-600">{item.probabilitas_baik_sekali?.toFixed(1) || 0}%</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(item.created_at).toLocaleDateString('id-ID')}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>

                        {prediksi.last_page > 1 && (
                            <div className="px-6 py-4 border-t border-gray-200">
                                <div className="flex justify-between items-center">
                                    <div className="text-sm text-gray-500">
                                        Menampilkan {prediksi.from} - {prediksi.to} dari {prediksi.total} data
                                    </div>
                                    <div className="flex gap-1">
                                        {prediksi.links.map((link, i) => (
                                            <button
                                                key={i}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`px-3 py-1 text-sm rounded ${
                                                    link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : link.url
                                                        ? 'bg-white border border-gray-300 hover:bg-gray-50'
                                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {showRunModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-lg font-semibold mb-4">Jalankan Agent Prediksi</h3>
                        <p className="text-sm text-gray-600 mb-4">
                            Agent akan menghitung prediksi skor akreditasi berdasarkan data terbaru.
                        </p>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Filter Prodi (opsional)</label>
                            <select
                                value={selectedProdi}
                                onChange={(e) => setSelectedProdi(e.target.value ? Number(e.target.value) : '')}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Semua Prodi</option>
                                {prodi_list.map(p => (
                                    <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => setShowRunModal(false)}
                                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                            >
                                Batal
                            </button>
                            <button
                                onClick={runAgent}
                                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                            >
                                Jalankan
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}