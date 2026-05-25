import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, FormEventHandler } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';

interface Iku {
    id: number;
    nama_indikator: string;
    kode_iku: string;
}

interface Periode {
    id: number;
    kode_periode: string;
    nama_periode: string;
}

interface Unit {
    id: number;
    nama_prodi?: string;
    nama_fakultas?: string;
}

interface Cascading {
    id: number;
    iku_id: number;
    periode_id: number;
    unit_type: 'Fakultas' | 'Prodi';
    unit_id: number;
    target: number;
    capaian: number;
    catatan: string | null;
    iku?: Iku;
    periode?: Periode;
    unit?: Unit;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    cascading: PaginatedData<Cascading>;
    iku_list: Iku[];
    periode_list: Periode[];
    prodi_list: { id: number; nama_prodi: string }[];
    fakultas_list: { id: number; nama_fakultas: string }[];
    success?: string;
}

export default function CascadingPage({ cascading, iku_list, periode_list, prodi_list, fakultas_list, success }: Props) {
    const { props } = usePage();
    const flashSuccess = success || (props as any).flash?.success;

    const [showModal, setShowModal] = useState(false);
    const [showCapaianModal, setShowCapaianModal] = useState(false);
    const [selectedCascading, setSelectedCascading] = useState<Cascading | null>(null);

    const { data, setData, post, processing, reset, errors } = useForm({
        iku_id: '',
        periode_id: '',
        unit_type: 'Prodi',
        unit_id: '',
        target: '0',
        catatan: '',
    });

    const { data: capaianData, setData: setCapaianData, post: postCapaian, processing: capaianProcessing, errors: capaianErrors } = useForm({
        capaian: '0',
        catatan: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('iku.cascading.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            },
        });
    };

    function openCapaianModal(item: Cascading) {
        setSelectedCascading(item);
        setCapaianData({
            capaian: String(item.capaian),
            catatan: item.catatan || '',
        });
        setShowCapaianModal(true);
    }

    const submitCapaian: FormEventHandler = (e) => {
        e.preventDefault();
        if (selectedCascading) {
            post(route('iku.cascading.capaian', selectedCascading.id), {
                onSuccess: () => {
                    setShowCapaianModal(false);
                    setSelectedCascading(null);
                },
            });
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Cascading IKU</h2>}
        >
            <Head title="Cascading IKU" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {flashSuccess}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-lg font-medium text-gray-900">Target & Capaian Unit</h3>
                            <PrimaryButton onClick={() => setShowModal(true)}>
                                Tambah Target Unit
                            </PrimaryButton>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IKU</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capaian</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {cascading.data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-medium">
                                                [{item.iku?.kode_iku}] {item.iku?.nama_indikator}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500">
                                                <span className="block text-xs text-gray-400">{item.unit_type}</span>
                                                {item.unit_type === 'Prodi' ? item.unit?.nama_prodi : item.unit?.nama_fakultas}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500">
                                                {item.periode?.nama_periode}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500">{item.target}%</td>
                                            <td className="px-6 py-4 text-sm text-gray-500">
                                                <div className="flex items-center">
                                                    <span className={`font-bold ${item.capaian >= item.target ? 'text-green-600' : 'text-orange-600'}`}>
                                                        {item.capaian}%
                                                    </span>
                                                    <div className="ml-2 w-16 bg-gray-200 rounded-full h-1.5">
                                                        <div className={`h-1.5 rounded-full ${item.capaian >= item.target ? 'bg-green-500' : 'bg-orange-500'}`} style={{ width: `${Math.min(item.capaian, 100)}%` }}></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onClick={() => openCapaianModal(item)} className="text-indigo-600 hover:text-indigo-900">
                                                    Update Capaian
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

            {/* Modal Tambah Target */}
            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <form onSubmit={submit} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Tambah Target Unit</h2>
                    
                    <div className="space-y-4">
                        <div>
                            <InputLabel htmlFor="iku_id" value="Indikator IKU" />
                            <select
                                id="iku_id"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={data.iku_id}
                                onChange={(e) => setData('iku_id', e.target.value)}
                                required
                            >
                                <option value="">Pilih IKU</option>
                                {iku_list.map((iku) => (
                                    <option key={iku.id} value={iku.id}>[{iku.kode_iku}] {iku.nama_indikator}</option>
                                ))}
                            </select>
                            <InputError message={errors.iku_id} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="periode_id" value="Periode" />
                            <select
                                id="periode_id"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={data.periode_id}
                                onChange={(e) => setData('periode_id', e.target.value)}
                                required
                            >
                                <option value="">Pilih Periode</option>
                                {periode_list.map((p) => (
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
                                    className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    value={data.unit_type}
                                    onChange={(e) => setData('unit_type', e.target.value as 'Fakultas' | 'Prodi')}
                                >
                                    <option value="Fakultas">Fakultas</option>
                                    <option value="Prodi">Prodi</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel htmlFor="unit_id" value="Pilih Unit" />
                                <select
                                    id="unit_id"
                                    className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    value={data.unit_id}
                                    onChange={(e) => setData('unit_id', e.target.value)}
                                    required
                                >
                                    <option value="">Pilih Unit</option>
                                    {data.unit_type === 'Prodi' 
                                        ? prodi_list.map(u => <option key={u.id} value={u.id}>{u.nama_prodi}</option>)
                                        : fakultas_list.map(u => <option key={u.id} value={u.id}>{u.nama_fakultas}</option>)
                                    }
                                </select>
                            </div>
                        </div>

                        <div>
                            <InputLabel htmlFor="target" value="Target (%)" />
                            <TextInput
                                id="target"
                                type="number"
                                className="mt-1 block w-full"
                                value={data.target}
                                onChange={(e) => setData('target', e.target.value)}
                                required
                            />
                        </div>

                        <div>
                            <InputLabel htmlFor="catatan" value="Catatan" />
                            <textarea
                                id="catatan"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={data.catatan}
                                onChange={(e) => setData('catatan', e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setShowModal(false)}>Batal</SecondaryButton>
                        <PrimaryButton className="ml-3" disabled={processing}>Simpan</PrimaryButton>
                    </div>
                </form>
            </Modal>

            {/* Modal Update Capaian */}
            <Modal show={showCapaianModal} onClose={() => setShowCapaianModal(false)}>
                <form onSubmit={submitCapaian} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Update Capaian</h2>
                    <p className="text-sm text-gray-600 mb-4">
                        Unit: {selectedCascading?.unit_type === 'Prodi' ? selectedCascading?.unit?.nama_prodi : selectedCascading?.unit?.nama_fakultas}
                        <br />
                        IKU: {selectedCascading?.iku?.nama_indikator}
                    </p>

                    <div className="space-y-4">
                        <div>
                            <InputLabel htmlFor="capaian" value="Capaian Realisasi (%)" />
                            <TextInput
                                id="capaian"
                                type="number"
                                className="mt-1 block w-full"
                                value={capaianData.capaian}
                                onChange={(e) => setCapaianData('capaian', e.target.value)}
                                required
                            />
                            <InputError message={capaianErrors.capaian} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="catatan_capaian" value="Catatan Realisasi" />
                            <textarea
                                id="catatan_capaian"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={capaianData.catatan}
                                onChange={(e) => setCapaianData('catatan', e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setShowCapaianModal(false)}>Batal</SecondaryButton>
                        <PrimaryButton className="ml-3" disabled={capaianProcessing}>Update</PrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
