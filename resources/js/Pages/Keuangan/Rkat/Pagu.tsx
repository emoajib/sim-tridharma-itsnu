import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, FormEventHandler } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';

interface Periode {
    id: number;
    kode_periode: string;
    nama_periode: string;
}

interface Pagu {
    id: number;
    periode_id: number;
    unit_type: string;
    unit_id: number;
    pagu_total: number;
    periode?: Periode;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    paginations: PaginatedData<Pagu>;
    periode_list: Periode[];
    prodi_list: { id: number; nama_prodi: string }[];
    fakultas_list: { id: number; nama_fakultas: string }[];
}

export default function PaguPage({ paginations, periode_list, prodi_list, fakultas_list }: Props) {
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    const [showModal, setShowModal] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        periode_id: '',
        unit_type: 'Prodi',
        unit_id: '',
        pagu_total: '0',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('rkat.pagu.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            },
        });
    };

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Kelola Pagu Anggaran</h2>}
        >
            <Head title="Kelola Pagu Anggaran" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm">
                            {flashSuccess}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-lg font-medium text-gray-900">Daftar Pagu Per Unit</h3>
                            <PrimaryButton onClick={() => setShowModal(true)}>
                                Tambah / Update Pagu
                            </PrimaryButton>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Unit</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Unit</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pagu</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {paginations.data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                {item.periode?.nama_periode}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {item.unit_type}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {item.unit_type === 'Prodi' 
                                                    ? prodi_list.find(p => p.id === item.unit_id)?.nama_prodi 
                                                    : fakultas_list.find(f => f.id === item.unit_id)?.nama_fakultas || 'Rektorat'
                                                }
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                                {formatCurrency(item.pagu_total)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button 
                                                    onClick={() => {
                                                        setData({
                                                            periode_id: String(item.periode_id),
                                                            unit_type: item.unit_type,
                                                            unit_id: String(item.unit_id),
                                                            pagu_total: String(item.pagu_total)
                                                        });
                                                        setShowModal(true);
                                                    }}
                                                    className="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <form onSubmit={submit} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Set Pagu Anggaran</h2>

                    <div className="space-y-4">
                        <div>
                            <InputLabel htmlFor="periode_id" value="Periode Akademik" />
                            <select
                                id="periode_id"
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value={data.periode_id}
                                onChange={(e) => setData('periode_id', e.target.value)}
                                required
                            >
                                <option value="">Pilih Periode</option>
                                {periode_list.map(p => (
                                    <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                ))}
                            </select>
                            <InputError message={errors.periode_id} className="mt-2" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="unit_type" value="Tipe Unit" />
                                <select
                                    id="unit_type"
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    value={data.unit_type}
                                    onChange={(e) => setData('unit_type', e.target.value)}
                                >
                                    <option value="Rektorat">Rektorat</option>
                                    <option value="Fakultas">Fakultas</option>
                                    <option value="Prodi">Prodi</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel htmlFor="unit_id" value="Pilih Unit" />
                                <select
                                    id="unit_id"
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    value={data.unit_id}
                                    onChange={(e) => setData('unit_id', e.target.value)}
                                    required={data.unit_type !== 'Rektorat'}
                                >
                                    {data.unit_type === 'Rektorat' && <option value="0">Pusat / Rektorat</option>}
                                    {data.unit_type === 'Fakultas' && (
                                        <>
                                            <option value="">Pilih Fakultas</option>
                                            {fakultas_list.map(f => <option key={f.id} value={f.id}>{f.nama_fakultas}</option>)}
                                        </>
                                    )}
                                    {data.unit_type === 'Prodi' && (
                                        <>
                                            <option value="">Pilih Prodi</option>
                                            {prodi_list.map(p => <option key={p.id} value={p.id}>{p.nama_prodi}</option>)}
                                        </>
                                    )}
                                </select>
                                <InputError message={errors.unit_id} className="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel htmlFor="pagu_total" value="Total Pagu Anggaran (Rp)" />
                            <TextInput
                                id="pagu_total"
                                type="number"
                                className="mt-1 block w-full"
                                value={data.pagu_total}
                                onChange={(e) => setData('pagu_total', e.target.value)}
                                required
                            />
                            <InputError message={errors.pagu_total} className="mt-2" />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setShowModal(false)}>Batal</SecondaryButton>
                        <PrimaryButton className="ml-3" disabled={processing}>Simpan Pagu</PrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
