import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage, useForm, useError, useRemember } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Spinner, Input, Button, Select, Table, Checkbox, Tag, Badge, Modal } from '@/Components';

interface RekomendasiItem {
    id: number;
    prodi_id: number;
    indikator_id: number;
    judul_rekomendasi: string;
    deskripsi: string | null;
    prioritas: 1 | 2 | 3;
    status: 'pending' | 'in_progress' | 'completed' | 'rejected';
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

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const prioritasColors = {
    1: 'bg-red-100 text-red-800',
    2: 'bg-orange-100 text-orange-800',
    3: 'bg-yellow-100 text-yellow-800',
};

const statusLabels = {
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
    const { data: remember } = useRemember('filters-rekomendasi', {
        prodi_id: '',
        status: '',
    });
    const flashSuccess = (usePage().props as any).flash?.success;
    const flashError = (usePage().props as any).flash?.error;

    useEffect(() => {
        if (remember.prodi_id !== undefined) setSelectedProdi(remember.prodi_id);
        if (remember.status !== undefined) setSelectedStatus(remember.status);
        setData('prodi_id', remember.prodi_id || '');
    }, [remember]);

    useEffect(() => {
        router.get(router.route('rekomendasi'), {
            prodi_id: selectedProdi === '' ? null : Number(selectedProdi),
            status: selectedStatus === '' ? null : selectedStatus,
        }, {
            preserveState: true,
            onSuccess: () => {
                setData('prodi_id', remember.prodi_id || '');
            }
        });
    }, [selectedProdi, selectedStatus]);

    const handleRun = () => {
        post(route('rekomendasi.run'), {
            onSuccess: () => {
                setShowRunModal(false);
                reset();
            },
            onError: () => {
                // Error handled by form
            }
        });
        setShowRunModal(true);
    };

    return (
        <AuthenticatedLayout title="Agent Rekomendasi">
            <Head>
                <title>Agent Rekomendasi - SIM Tridharma ITSNU</title>
            </Head>

            {flashSuccess && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded">
                    {flashSuccess}
                </div>
            )}
            {flashError && (
                <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                    {flashError}
                </div>
            )}

            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Agent Rekomendasi</h1>
                <div className="flex space-x-3">
                    <Button 
                        variant="primary" 
                        onClick={handleRun}
                        disabled={processing}
                    >
                        {processing ? 'Sedang Menjalankan...' : 'Jalankan Agent Rekomendasi'}
                    </Button>
                </div>
            </div>

            <div className="space-y-6">
                <div className="bg-white p-6 rounded-lg shadow">
                    <div className="flex flex-wrap items-center gap-4 mb-4">
                        <div className="flex-1 min-w-[200px]">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
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
                                placeholder="Pilih program studi..."
                            />
                        </div>
                        <div className="flex-1 min-w-[200px]">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <Select
                                value={selectedStatus}
                                onValueChange={setSelectedStatus}
                                options={[
                                    { label: 'Semua Status', value: '' },
                                    { label: 'Menunggu', value: 'pending' },
                                    { label: 'Sedang Diproses', value: 'in_progress' },
                                    { label: 'Selesai', value: 'completed' },
                                    { label: 'Ditolak', value: 'rejected' },
                                ]}
                                placeholder="Pilih status..."
                            />
                        </div>
                    </div>
                </div>

                <Table>
                    <thead>
                        <tr className="border-b">
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">No</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Program Studi</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Indikator</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Judul Rekomendasi</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Prioritas</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Status</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Target Capai</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Deadline</th>
                            <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rekomendasis.data.length === 0 ? (
                            <tr>
                                <td className="px-6 py-4 text-center text-gray-500" colSpan="9">
                                    Belum ada rekomendasi yang dihasilkan.
                                </td>
                            </tr>
                        ) : (
                            rekomendasis.data.map((item, index) => (
                                <tr key={item.id} className="border-t">
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {rekomendasis.from + index}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {item.prodi?.kode_prodi} - {item.prodi?.nama_prodi}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {item.indikator?.kode_indikator} - {item.indikator?.nama_indikator}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        <div className="max-w-[300px]">
                                            {item.judul_rekomendasi}
                                            {item.deskripsi && (
                                                <div className="mt-1 text-xs text-gray-500">
                                                    {item.deskripsi}
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${prioritasColors[item.prioritas]}`}>
                                            Prioritas {item.prioritas}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[item.status]}`}>
                                            {statusLabels[item.status]}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {item.target_capai || '-'}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {item.deadline || '-'}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-600">
                                        {new Date(item.created_at).toLocaleDateString('id-ID')}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </Table>

                {rekomendasis.links.length > 0 && (
                    <div className="flex items-center justify-between pt-4">
                        <p className="text-sm text-gray-500">
                            Menampilkan {rekomendasis.from} - {rekomendasis.to} dari {rekomendasis.total} data
                        </p>
                        <div className="flex space-x-1">
                            {rekomendasis.links.map((link, i) => (
                                <Button 
                                    key={i}
                                    variant={link.active ? 'secondary' : 'outline'}
                                    size="sm"
                                    onClick={() => {
                                        if (link.url) {
                                            router.visit(link.url);
                                        }
                                    }}
                                    disabled={!link.url}
                                >
                                    {link.label}
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>

        <Modal show={showRunModal} onClose={() => setShowRunModal(false)}>
            <div className="space-y-4">
                <div className="text-center">
                    <div className="flex items-center justify-center h-12 w-12 bg-blue-100 rounded-full mb-3">
                        <svg className="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} 
                                d="M12 8v4m0 0h4m-4-4h4m-2-3a4.5 4.5 0 01-6.364 3.636l-1.414 1.414A5.5 5.5 0 0016.5 9.5h1.5a4.5 4.5 0 015.364 2.364l1.414-1.414A4.5 4.5 0 0014 6.5h-1.5a4.5 4.5 0 01-6.364-3.636z" />
                        </svg>
                    </div>
                    <h3 className="font-semibold text-gray-800 mb-2">Agent Rekomendasi Sedang Berjalan</h3>
                    <p className="text-sm text-gray-500">
                        Proses pencarian indikator yang perlu perbaikan dan pembuatan rekomendasi sedang berjalan.
                        Silakan tunggu beberapa saat hingga proses selesai.
                    </p>
                    <div className="mt-4">
                        <Button 
                            variant="outline" 
                            onClick={() => setShowRunModal(false)}
                        >
                            Tutup
                        </Button>
                    </div>
                </div>
            </div>
        </Modal>
    );
}