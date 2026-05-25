import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import { useState, useEffect } from 'react';

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Proposal {
    id: number;
    judul_kegiatan: string;
    status: string;
    estimasi_biaya: number;
    prodi?: { nama_prodi: string };
    periode?: { kode_periode: string; nama_periode: string };
    pengusul?: { name: string };
    created_at: string;
}

interface Props {
    proposals: PaginatedData<Proposal>;
    filters: { status?: string; periode_id?: string; prodi_id?: string };
    periode_list: { id: number; kode_periode: string; nama_periode: string }[];
    prodi_list: { id: number; nama_prodi: string }[];
}

export default function Index({ proposals, filters, periode_list, prodi_list }: Props) {
    const [params, setParams] = useState(filters);

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('rkat.index'), params, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [params]);

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    };

    const getStatusBadge = (status: string) => {
        const colors: Record<string, string> = {
            'draft': 'bg-gray-100 text-gray-800',
            'submitted': 'bg-blue-100 text-blue-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800',
            'revised': 'bg-orange-100 text-orange-800',
        };
        return colors[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Daftar Usulan RKAT</h2>}
        >
            <Head title="RKAT" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex flex-wrap gap-4 mb-6 justify-between items-end">
                            <div className="flex flex-wrap gap-4 flex-1">
                                <div className="w-48">
                                    <label className="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        value={params.status || ''}
                                        onChange={(e) => setParams({ ...params, status: e.target.value })}
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div className="w-48">
                                    <label className="block text-sm font-medium text-gray-700">Periode</label>
                                    <select
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        value={params.periode_id || ''}
                                        onChange={(e) => setParams({ ...params, periode_id: e.target.value })}
                                    >
                                        <option value="">Semua Periode</option>
                                        {periode_list.map(p => (
                                            <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-48">
                                    <label className="block text-sm font-medium text-gray-700">Prodi</label>
                                    <select
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        value={params.prodi_id || ''}
                                        onChange={(e) => setParams({ ...params, prodi_id: e.target.value })}
                                    >
                                        <option value="">Semua Prodi</option>
                                        {prodi_list.map(p => (
                                            <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Link href={route('rkat.pagu')}>
                                    <SecondaryButton>Kelola Pagu</SecondaryButton>
                                </Link>
                                <Link href={route('rkat.create')}>
                                    <PrimaryButton>Buat Usulan</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Usulan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit/Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {proposals.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-medium text-gray-900">{item.judul_kegiatan}</div>
                                                <div className="text-xs text-gray-500">{item.periode?.nama_periode}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {item.prodi?.nama_prodi || 'Rektorat'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                {formatCurrency(item.estimasi_biaya)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadge(item.status)}`}>
                                                    {item.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(item.created_at).toLocaleDateString('id-ID')}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link href={route('rkat.show', item.id)} className="text-indigo-600 hover:text-indigo-900">
                                                    Detail
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                    {proposals.data.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-10 text-center text-gray-500">Belum ada usulan RKAT.</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Total {proposals.total} usulan
                            </div>
                            <div className="flex space-x-1">
                                {proposals.links.map((link, i) => (
                                    <button
                                        key={i}
                                        disabled={!link.url}
                                        onClick={() => link.url && router.get(link.url, params, { preserveState: true })}
                                        className={`px-3 py-1 border rounded text-sm ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function SecondaryButton({ children, className = '', disabled, ...props }: any) {
    return (
        <button
            {...props}
            disabled={disabled}
            className={
                `inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 ${
                    disabled && 'opacity-25'
                } ` + className
            }
        >
            {children}
        </button>
    );
}
