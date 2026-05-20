import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage, useForm, useRemember } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Spinner, Input, Button, Select, Table, Checkbox, Tag, Badge, Modal } from '@/Components';

interface RekomendasiItem {
    id: number;
    prodi_id: number;
    indikator_id: number;
    judul_rekomendasi: string;
    deskripsi: string | null;
    prioritas: string;
    status: 'baru' | 'pending' | 'in_progress' | 'completed' | 'rejected';
    target_capai: string | null;
    deadline: string | null;
    created_at: string;
    updated_at: string;
    prodi?: { id: number; nama_prodi: string; kode_prodi: string };
    indikator?: { id: number; kode_indikator: string; nama_indikator: string };
}

interface Prodi {
    id: number;
    nama_prodi: string;
    kode_prodi: string;
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
    rekomendasis: PaginatedData<RekomendasiItem>;
    prodi_list: Prodi[];
    filters: {
        prodi_id: number | null;
        status: string | null;
    };
}

const statusColors: Record<string, string> = {
    baru: 'bg-indigo-100 text-indigo-800',
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const prioritasColors: Record<string, string> = {
    'Tinggi': 'bg-red-100 text-red-800',
    'Sedang': 'bg-orange-100 text-orange-800',
    'Rendah': 'bg-yellow-100 text-yellow-800',
    '1': 'bg-red-100 text-red-800',
    '2': 'bg-orange-100 text-orange-800',
    '3': 'bg-yellow-100 text-yellow-800',
};

const statusLabels: Record<string, string> = {
    baru: 'Baru',
    pending: 'Menunggu',
    in_progress: 'Sedang Diproses',
    completed: 'Selesai',
    rejected: 'Ditolak',
};

export default function Index({ rekomendasis, prodi_list, filters }: Props) {
    const [selectedProdi, setSelectedProdi] = useState<number | ''>(filters.prodi_id || '');
    const [selectedStatus, setSelectedStatus] = useState<string | ''>(filters.status || '');
    const [showRunModal, setShowRunModal] = useState(false);
    const { data: form, setData, post, processing, errors, reset } = useForm({
        prodi_id: '',
    });

    const flash: any = usePage().props.flash || {};

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('rekomendasi'), {
                prodi_id: selectedProdi === '' ? null : Number(selectedProdi),
                status: selectedStatus === '' ? null : selectedStatus,
            }, {
                preserveState: true,
                replace: true
            });
        }, 500);
        return () => clearTimeout(timer);
    }, [selectedProdi, selectedStatus]);

    const handleRun = () => {
        post(route('rekomendasi.run'), {
            onSuccess: () => {
                setShowRunModal(true);
                reset();
            },
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Agent Rekomendasi AI</h2>}>
            <Head title="Agent Rekomendasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded text-green-700 text-sm font-medium">
                            {flash.success}
                        </div>
                    )}

                    {flash.error && (
                        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm font-medium">
                            {flash.error}
                        </div>
                    )}

                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">Rekomendasi Strategis AI</h1>
                        <div className="flex space-x-3">
                            <Button 
                                variant="primary" 
                                onClick={handleRun}
                                disabled={processing}
                            >
                                {processing ? <><Spinner className="mr-2 h-4 w-4" /> Memproses...</> : 'Jalankan Agent Rekomendasi'}
                            </Button>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                            <div className="flex flex-wrap items-center gap-4">
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Filter Program Studi</label>
                                    <Select
                                        value={selectedProdi}
                                        onValueChange={setSelectedProdi}
                                        options={[
                                            { label: 'Semua Program Studi', value: '' },
                                            ...prodi_list.map(p => ({ 
                                                label: `${p.kode_prodi} - ${p.nama_prodi}`, 
                                                value: p.id 
                                            }))
                                        ]}
                                    />
                                </div>
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Filter Status</label>
                                    <Select
                                        value={selectedStatus}
                                        onValueChange={setSelectedStatus}
                                        options={[
                                            { label: 'Semua Status', value: '' },
                                            { label: 'Baru', value: 'baru' },
                                            { label: 'Menunggu', value: 'pending' },
                                            { label: 'Sedang Diproses', value: 'in_progress' },
                                            { label: 'Selesai', value: 'completed' },
                                            { label: 'Ditolak', value: 'rejected' },
                                        ]}
                                    />
                                </div>
                            </div>
                        </div>

                        <Table>
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">No</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Program Studi</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Indikator</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Rekomendasi</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Prioritas</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Status</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {rekomendasis.data.length === 0 ? (
                                    <tr>
                                        <td className="px-6 py-12 text-center text-gray-500 text-sm" colSpan={7}>
                                            Belum ada rekomendasi yang dihasilkan. Klik "Jalankan Agent" untuk memulai analisis.
                                        </td>
                                    </tr>
                                ) : (
                                    rekomendasis.data.map((item, index) => (
                                        <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 text-sm text-gray-600 font-medium">
                                                {rekomendasis.from + index}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-bold">
                                                {item.prodi?.nama_prodi}
                                                <div className="text-[10px] text-gray-400 font-black">{item.prodi?.kode_prodi}</div>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                <Badge className="bg-gray-100 text-gray-600">{item.indikator?.kode_indikator}</Badge>
                                                <div className="mt-1 text-xs truncate max-w-[150px]">{item.indikator?.nama_indikator}</div>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700">
                                                <div className="max-w-[400px]">
                                                    <div className="font-bold mb-1">{item.judul_rekomendasi}</div>
                                                    {item.deskripsi && (
                                                        <div className="text-xs text-gray-500 leading-relaxed italic">
                                                            "{item.deskripsi}"
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <Badge className={prioritasColors[item.prioritas] || 'bg-gray-100 text-gray-800'}>
                                                    {item.prioritas}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <Badge className={statusColors[item.status] || 'bg-gray-100 text-gray-800'}>
                                                    {statusLabels[item.status] || item.status}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500 tabular-nums font-medium">
                                                {new Date(item.created_at).toLocaleDateString('id-ID')}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </Table>

                        {rekomendasis.last_page > 1 && (
                            <div className="flex items-center justify-between pt-4">
                                <p className="text-sm text-gray-500 font-medium">
                                    Menampilkan <span className="text-gray-900">{rekomendasis.from} - {rekomendasis.to}</span> dari <span className="text-gray-900 font-bold">{rekomendasis.total}</span> rekomendasi
                                </p>
                                <div className="flex space-x-1">
                                    {rekomendasis.links.map((link, i) => (
                                        <Button 
                                            key={i}
                                            variant={link.active ? 'primary' : 'secondary'}
                                            size="sm"
                                            onClick={() => link.url && router.visit(link.url)}
                                            disabled={!link.url}
                                            className="min-w-[40px]"
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Modal show={showRunModal} onClose={() => setShowRunModal(false)}>
                <div className="p-6">
                    <div className="text-center">
                        <div className="flex items-center justify-center h-16 w-16 bg-indigo-100 rounded-full mb-4 mx-auto">
                            <svg className="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 className="text-xl font-bold text-gray-900 mb-2">Agent AI Sedang Bekerja</h3>
                        <p className="text-sm text-gray-500 leading-relaxed mb-6">
                            Agent Rekomendasi sedang menganalisis data indikator akreditasi untuk Program Studi pilihan Anda. 
                            Rekomendasi strategis akan muncul di daftar setelah proses selesai.
                        </p>
                        <div className="flex justify-center">
                            <Button 
                                variant="primary" 
                                onClick={() => setShowRunModal(false)}
                                className="w-full"
                            >
                                Mengerti, Saya Akan Menunggu
                            </Button>
                        </div>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}