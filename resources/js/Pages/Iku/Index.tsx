import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface Iku {
    id: number;
    kode_iku: string;
    nama_indikator: string;
    deskripsi: string | null;
    lembaga_id: number | null;
    bobot: number;
    target: number;
    is_active: boolean;
    lembaga?: Lembaga;
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
    iku: PaginatedData<Iku>;
    lembaga_list: Lembaga[];
    success?: string;
}

export default function Index({ iku, lembaga_list, success }: Props) {
    const { props } = usePage();
    const flashSuccess = success || (props as any).flash?.success;
    
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Iku | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Iku | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        kode_iku: '',
        nama_indikator: '',
        deskripsi: '',
        lembaga_id: '',
        bobot: '0',
        target: '0',
        is_active: true,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('iku.index'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Iku) {
        setEditing(item);
        setData({
            kode_iku: item.kode_iku,
            nama_indikator: item.nama_indikator,
            deskripsi: item.deskripsi || '',
            lembaga_id: item.lembaga_id ? String(item.lembaga_id) : '',
            bobot: String(item.bobot),
            target: String(item.target),
            is_active: item.is_active,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('iku.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('iku.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Iku) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (deleteTarget) {
            destroy(route('iku.destroy', deleteTarget.id), {
                onSuccess: () => setDeleteTarget(null),
            });
        }
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Indikator Kinerja Utama (IKU)</h2>}
        >
            <Head title="Indikator Kinerja Utama" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {flashSuccess}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <div className="w-1/3">
                                <TextInput
                                    className="w-full"
                                    placeholder="Cari indikator..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <PrimaryButton onClick={openCreate}>
                                Tambah IKU
                            </PrimaryButton>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikator</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lembaga</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {iku.data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{item.kode_iku}</td>
                                            <td className="px-6 py-4 text-sm text-gray-500">
                                                <div className="font-medium text-gray-900">{item.nama_indikator}</div>
                                                <div className="text-xs text-gray-400 truncate max-w-xs">{item.deskripsi}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.lembaga?.singkatan || '-'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.target}%</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.bobot}%</td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                    {item.is_active ? 'Aktif' : 'Non-aktif'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onClick={() => openEdit(item)} className="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                                <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Menampilkan {iku.from} sampai {iku.to} dari {iku.total} hasil
                            </div>
                            <div className="flex space-x-1">
                                {iku.links.map((link, i) => (
                                    <button
                                        key={i}
                                        disabled={!link.url}
                                        onClick={() => link.url && router.get(link.url, { search }, { preserveState: true })}
                                        className={`px-3 py-1 border rounded text-sm ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <form onSubmit={submit} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        {editing ? 'Edit Indikator IKU' : 'Tambah Indikator IKU'}
                    </h2>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel htmlFor="kode_iku" value="Kode IKU" />
                            <TextInput
                                id="kode_iku"
                                className="mt-1 block w-full"
                                value={data.kode_iku}
                                onChange={(e) => setData('kode_iku', e.target.value)}
                                required
                            />
                            <InputError message={errors.kode_iku} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="nama_indikator" value="Nama Indikator" />
                            <TextInput
                                id="nama_indikator"
                                className="mt-1 block w-full"
                                value={data.nama_indikator}
                                onChange={(e) => setData('nama_indikator', e.target.value)}
                                required
                            />
                            <InputError message={errors.nama_indikator} className="mt-2" />
                        </div>

                        <div className="md:col-span-2">
                            <InputLabel htmlFor="deskripsi" value="Deskripsi" />
                            <textarea
                                id="deskripsi"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows={3}
                                value={data.deskripsi}
                                onChange={(e) => setData('deskripsi', e.target.value)}
                            />
                            <InputError message={errors.deskripsi} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="lembaga_id" value="Lembaga Akreditasi" />
                            <select
                                id="lembaga_id"
                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={data.lembaga_id}
                                onChange={(e) => setData('lembaga_id', e.target.value)}
                            >
                                <option value="">Pilih Lembaga</option>
                                {lembaga_list.map((l) => (
                                    <option key={l.id} value={l.id}>{l.nama_lembaga} ({l.singkatan})</option>
                                ))}
                            </select>
                            <InputError message={errors.lembaga_id} className="mt-2" />
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
                            <InputError message={errors.target} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="bobot" value="Bobot (%)" />
                            <TextInput
                                id="bobot"
                                type="number"
                                className="mt-1 block w-full"
                                value={data.bobot}
                                onChange={(e) => setData('bobot', e.target.value)}
                                required
                            />
                            <InputError message={errors.bobot} className="mt-2" />
                        </div>

                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                checked={data.is_active}
                                onChange={(e) => setData('is_active', e.target.checked)}
                            />
                            <label htmlFor="is_active" className="ml-2 text-sm text-gray-600">Aktif</label>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setShowModal(false)}>Batal</SecondaryButton>
                        <PrimaryButton className="ml-3" disabled={processing}>
                            {editing ? 'Update' : 'Simpan'}
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>

            <Modal show={!!deleteTarget} onClose={() => setDeleteTarget(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Apakah Anda yakin ingin menghapus indikator "{deleteTarget?.nama_indikator}"?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setDeleteTarget(null)}>Batal</SecondaryButton>
                        <DangerButton className="ml-3" onClick={executeDelete} disabled={processing}>
                            Hapus
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
