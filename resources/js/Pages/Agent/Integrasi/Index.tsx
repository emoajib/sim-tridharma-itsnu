import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Spinner, Input, Button, Select, Table, Tag, Badge, Modal } from '@/Components';

interface IntegrasiLog {
    id: number;
    sumber: string;
    jenis_data: string;
    status: 'success' | 'failed' | 'running';
    jumlah_ditarik: number;
    jumlah_konflik: number;
    waktu_mulai: string | null;
    waktu_selesai: string | null;
    created_at: string;
    updated_at: string;
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

interface ProdiItem {
    id: number;
    nama_prodi: string;
    kode_prodi: string;
}

interface Props {
    logs: PaginatedData<IntegrasiLog>;
    prodi_list: ProdiItem[];
    filters: {
        sumber: string | null;
        status: string | null;
    };
}

const statusColors: Record<string, string> = {
    success: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    running: 'bg-blue-100 text-blue-800',
};

const statusLabels: Record<string, string> = {
    success: 'Berhasil',
    failed: 'Gagal',
    running: 'Sedang Berjalan',
};

const sumberLabels: Record<string, string> = {
    PDDIKTI: 'PD-DIKTI',
    SINTA: 'SINTA',
    SISTER: 'SISTER',
};

export default function Index({ logs, prodi_list, filters }: Props) {
    const [selectedSumber, setSelectedSumber] = useState<string | ''>(filters.sumber || '');
    const [selectedStatus, setSelectedStatus] = useState<string | ''>(filters.status || '');
    const [showRunModal, setShowRunModal] = useState(false);
    const [showSyncModal, setShowSyncModal] = useState(false);
    const { data: form, setData, post, processing, errors, reset } = useForm({
        sumber: '',
    });

    const flash: any = usePage().props.flash || {};

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('integrasi'), {
                sumber: selectedSumber === '' ? null : selectedSumber,
                status: selectedStatus === '' ? null : selectedStatus,
            }, {
                preserveState: true,
                replace: true
            });
        }, 500);
        return () => clearTimeout(timer);
    }, [selectedSumber, selectedStatus]);

    const handleRun = () => {
        post(route('integrasi.run'), {
            onSuccess: () => {
                setShowRunModal(true);
                reset();
            },
        });
    };

    const handleSync = () => {
        post(route('integrasi.sync'), {
            onSuccess: () => {
                setShowSyncModal(true);
                reset();
            },
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Agent Integrasi Data</h2>}>
            <Head title="Agent Integrasi" />

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
                        <h1 className="text-2xl font-bold text-gray-900">Sinkronisasi Data Eksternal</h1>
                        <div className="flex space-x-3">
                            <Button 
                                variant="primary" 
                                onClick={handleRun}
                                disabled={processing}
                            >
                                {processing ? <Spinner className="mr-2 h-4 w-4" /> : 'Jalankan Sinkronisasi Otomatis'}
                            </Button>
                            <Button 
                                variant="secondary" 
                                onClick={handleSync}
                                disabled={processing}
                            >
                                Sinkronisasi Manual
                            </Button>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                            <div className="flex flex-wrap items-center gap-4">
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Sumber Data</label>
                                    <Select
                                        value={selectedSumber}
                                        onValueChange={setSelectedSumber}
                                        options={[
                                            { label: 'Semua Sumber', value: '' },
                                            { label: 'PD-DIKTI', value: 'PDDIKTI' },
                                            { label: 'SINTA', value: 'SINTA' },
                                            { label: 'SISTER', value: 'SISTER' },
                                        ]}
                                    />
                                </div>
                                <div className="flex-1 min-w-[200px]">
                                    <label className="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Status</label>
                                    <Select
                                        value={selectedStatus}
                                        onValueChange={setSelectedStatus}
                                        options={[
                                            { label: 'Semua Status', value: '' },
                                            { label: 'Berhasil', value: 'success' },
                                            { label: 'Gagal', value: 'failed' },
                                            { label: 'Sedang Berjalan', value: 'running' },
                                        ]}
                                    />
                                </div>
                            </div>
                        </div>

                        <Table>
                            <thead className="bg-gray-50">
                                <tr className="border-b">
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">No</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Sumber</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Jenis Data</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Ditarik</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Konflik</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Durasi</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Status</th>
                                    <th className="text-left px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {logs.data.length === 0 ? (
                                    <tr>
                                        <td className="px-6 py-12 text-center text-gray-500 text-sm" colSpan={8}>
                                            Belum ada log sinkronisasi yang tersedia.
                                        </td>
                                    </tr>
                                ) : (
                                    logs.data.map((item, index) => (
                                        <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 text-sm text-gray-600 font-medium">
                                                {logs.from + index}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-bold uppercase">
                                                {sumberLabels[item.sumber] || item.sumber}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600 font-medium italic">
                                                {item.jenis_data}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-indigo-600 font-bold tabular-nums">
                                                {item.jumlah_ditarik}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-red-600 font-bold tabular-nums">
                                                {item.jumlah_konflik}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500 font-medium tabular-nums">
                                                {item.waktu_mulai ? new Date(item.waktu_mulai).toLocaleTimeString('id-ID') : '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <Badge className={statusColors[item.status] || 'bg-gray-100 text-gray-800'}>
                                                    {statusLabels[item.status] || item.status}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500 tabular-nums">
                                                {new Date(item.created_at).toLocaleDateString('id-ID')}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </Table>

                        {logs.last_page > 1 && (
                            <div className="flex items-center justify-between pt-4">
                                <p className="text-sm text-gray-500 font-medium">
                                    Menampilkan <span className="text-gray-900">{logs.from} - {logs.to}</span> dari <span className="text-gray-900 font-bold">{logs.total}</span> log
                                </p>
                                <div className="flex space-x-1">
                                    {logs.links.map((link, i) => (
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
                <div className="p-6 text-center">
                    <div className="flex items-center justify-center h-16 w-16 bg-blue-100 rounded-full mb-4 mx-auto">
                        <svg className="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.001 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 className="text-xl font-bold text-gray-900 mb-2">Sinkronisasi Otomatis Dimulai</h3>
                    <p className="text-sm text-gray-500 leading-relaxed mb-6">
                        Agent Integrasi sedang menarik data dari sumber eksternal (PDDIKTI/SINTA). 
                        Silakan cek daftar log secara berkala untuk melihat hasil sinkronisasi.
                    </p>
                    <Button variant="primary" onClick={() => setShowRunModal(false)} className="w-full">Tutup</Button>
                </div>
            </Modal>

            <Modal show={showSyncModal} onClose={() => setShowSyncModal(false)}>
                <div className="p-6 text-center">
                    <div className="flex items-center justify-center h-16 w-16 bg-green-100 rounded-full mb-4 mx-auto">
                        <svg className="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 className="text-xl font-bold text-gray-900 mb-2">Sinkronisasi Manual Berhasil</h3>
                    <p className="text-sm text-gray-500 leading-relaxed mb-6">
                        Permintaan sinkronisasi manual telah dikirim ke sistem. 
                        Data akan segera diperbarui dalam beberapa saat.
                    </p>
                    <Button variant="primary" onClick={() => setShowSyncModal(false)} className="w-full">Tutup</Button>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}