import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface HistoryItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    jenis_dokumen: string;
    narasi: string;
    status: string;
    created_at: string;
    prodi?: { id: number; nama_prodi: string };
    periode?: { id: number; nama_periode: string };
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    prodi_list: Prodi[];
    periode_list: Periode[];
    history: PaginatedData<HistoryItem>;
    filters: {
        prodi_id: number | null;
    };
}

export default function Index({ prodi_list, periode_list, history, filters }: Props) {
    const [selectedProdi, setSelectedProdi] = useState<number | ''>(filters.prodi_id || '');
    const [selectedPeriode, setSelectedPeriode] = useState<number | ''>('');
    const [selectedJenis, setSelectedJenis] = useState<'led' | 'lkpt'>('led');
    const [generating, setGenerating] = useState(false);
    const [selectedDoc, setSelectedDoc] = useState<HistoryItem | null>(null);
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    function handleGenerate() {
        if (!selectedProdi || !selectedPeriode) {
            alert('Pilih Prodi dan Periode terlebih dahulu');
            return;
        }

        setGenerating(true);
        router.post(route('generator.generate'), {
            prodi_id: selectedProdi,
            periode_id: selectedPeriode,
            jenis_dokumen: selectedJenis,
        }, {
            onFinish: () => setGenerating(false),
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Generator Dokumen AI
                </h2>
            }
        >
            <Head title="Generator Dokumen" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                            {flashSuccess}
                        </div>
                    )}

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Generator Form */}
                        <div className="rounded-lg bg-white p-6 shadow-sm lg:col-span-1">
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">Generate Dokumen</h3>
                            
                            <div className="space-y-4">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Jenis Dokumen
                                    </label>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => setSelectedJenis('led')}
                                            className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition ${
                                                selectedJenis === 'led'
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            }`}
                                        >
                                            LED
                                        </button>
                                        <button
                                            onClick={() => setSelectedJenis('lkpt')}
                                            className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition ${
                                                selectedJenis === 'lkpt'
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            }`}
                                        >
                                            LKPT
                                        </button>
                                    </div>
                                    <p className="mt-1 text-xs text-gray-500">
                                        {selectedJenis === 'led' 
                                            ? 'Laporan Evaluasi Diri - Deskripsi lengkap kondisi prodi'
                                            : 'Laporan Kinerja Program Studi - Data kuantitatif terkini'}
                                    </p>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Program Studi
                                    </label>
                                    <select
                                        value={selectedProdi}
                                        onChange={(e) => setSelectedProdi(e.target.value ? Number(e.target.value) : '')}
                                        className="w-full rounded-lg border-gray-300"
                                    >
                                        <option value="">Pilih Prodi</option>
                                        {prodi_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Periode Akademik
                                    </label>
                                    <select
                                        value={selectedPeriode}
                                        onChange={(e) => setSelectedPeriode(e.target.value ? Number(e.target.value) : '')}
                                        className="w-full rounded-lg border-gray-300"
                                    >
                                        <option value="">Pilih Periode</option>
                                        {periode_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                        ))}
                                    </select>
                                </div>

                                <button
                                    onClick={handleGenerate}
                                    disabled={generating || !selectedProdi || !selectedPeriode}
                                    className="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {generating ? 'Generating...' : 'Generate dengan AI'}
                                </button>
                            </div>
                        </div>

                        {/* History List */}
                        <div className="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                            <h3 className="mb-4 text-lg font-semibold text-gray-800">Riwayat Generate</h3>
                            
                            {history.data.length === 0 ? (
                                <p className="text-sm text-gray-500">Belum ada dokumen yang digenerate.</p>
                            ) : (
                                <div className="space-y-3">
                                    {history.data.map((item) => (
                                        <div
                                            key={item.id}
                                            className="flex items-start justify-between rounded-lg border p-4 hover:bg-gray-50"
                                        >
                                            <div className="flex-1">
                                                <div className="flex items-center gap-2">
                                                    <span className="rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                                        {item.jenis_dokumen.toUpperCase()}
                                                    </span>
                                                    <span className="text-sm font-medium text-gray-900">
                                                        {item.prodi?.nama_prodi}
                                                    </span>
                                                </div>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    {item.periode?.nama_periode} • {new Date(item.created_at).toLocaleString('id-ID')}
                                                </p>
                                                <p className="mt-2 text-sm text-gray-600 line-clamp-2">
                                                    {item.narasi?.substring(0, 150)}...
                                                </p>
                                            </div>
                                            <button
                                                onClick={() => setSelectedDoc(item)}
                                                className="ml-4 rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50"
                                            >
                                                Lihat
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Pagination */}
                            {history.last_page > 1 && (
                                <div className="mt-4 flex justify-center">
                                    <div className="flex gap-1">
                                        {history.links.map((link, idx) => (
                                            <button
                                                key={idx}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`px-3 py-1 text-sm ${
                                                    link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : link.url
                                                        ? 'bg-white text-gray-700 hover:bg-gray-50'
                                                        : 'bg-gray-100 text-gray-400'
                                                } rounded border`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Document Preview Modal */}
            {selectedDoc && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="w-full max-w-3xl max-h-[80vh] overflow-auto rounded-lg bg-white p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold">
                                {selectedDoc.jenis_dokumen.toUpperCase()} - {selectedDoc.prodi?.nama_prodi}
                            </h3>
                            <button
                                onClick={() => setSelectedDoc(null)}
                                className="text-gray-500 hover:text-gray-700"
                            >
                                ✕
                            </button>
                        </div>
                        <div className="prose max-w-none whitespace-pre-wrap text-sm text-gray-700">
                            {selectedDoc.narasi}
                        </div>
                        <div className="mt-4 flex justify-end gap-2">
                            <button
                                onClick={() => {
                                    const blob = new Blob([selectedDoc.narasi], { type: 'text/plain' });
                                    const url = URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = url;
                                    a.download = `${selectedDoc.jenis_dokumen}_${selectedDoc.prodi?.nama_prodi}.txt`;
                                    a.click();
                                }}
                                className="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Download TXT
                            </button>
                            <button
                                onClick={() => setSelectedDoc(null)}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}