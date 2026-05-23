import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, FormEventHandler } from 'react';

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
    deskripsi: string | null;
    prodi_count?: number;
}

interface Props {
    lembaga: Lembaga[];
    success?: string;
    error?: string;
}

export default function Index({ lembaga, success, error }: Props) {
    const { props } = usePage();
    const user = (props as any).auth?.user;
    const permissions = new Set(user?.permissions ?? []);
    const can = (perm: string) => permissions.has(perm);

    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Lembaga | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Lembaga | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        nama_lembaga: '',
        singkatan: '',
        deskripsi: '',
    });

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Lembaga) {
        setEditing(item);
        setData({
            nama_lembaga: item.nama_lembaga,
            singkatan: item.singkatan,
            deskripsi: item.deskripsi || '',
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('admin.lembaga.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('admin.lembaga.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Lembaga) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('admin.lembaga.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Manajemen Lembaga Akreditasi</h2>}
        >
            <Head title="Lembaga Akreditasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 font-bold border border-green-200">
                           ✅ {success}
                        </div>
                    )}
                    {error && (
                        <div className="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700 font-bold border border-red-200">
                           ❌ {error}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6 flex justify-between items-center">
                            <h3 className="text-lg font-bold text-gray-700">Daftar Badan/Lembaga</h3>
                            {can('admin.create') && (
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 shadow-md"
                                >
                                    + Tambah Lembaga Baru
                                </button>
                            )}
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Singkatan</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Nama Lengkap Lembaga</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Jumlah Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {lembaga.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="px-6 py-12 text-center text-gray-500 italic">Belum ada lembaga terdaftar</td>
                                        </tr>
                                    ) : (
                                        lembaga.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-black text-gray-900">{item.singkatan}</td>
                                                <td className="px-6 py-4 text-sm text-gray-700">{item.nama_lembaga}</td>
                                                <td className="px-6 py-4 text-sm font-bold text-indigo-600">{item.prodi_count} Prodi</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {can('admin.edit') && (
                                                        <button onClick={() => openEdit(item)} className="mr-3 font-bold text-indigo-600 hover:text-indigo-900 underline">Edit</button>
                                                    )}
                                                    {can('admin.delete') && (
                                                        <button onClick={() => confirmDelete(item)} className="font-bold text-red-600 hover:text-red-900">Hapus</button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-lg rounded-xl bg-white p-8 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between border-b pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">{editing ? 'Edit Lembaga' : 'Tambah Lembaga'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Singkatan (Slug)</label>
                                <input type="text" value={data.singkatan} onChange={(e) => setData('singkatan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500 uppercase" placeholder="Misal: LAMEMBA" />
                                {errors.singkatan && <p className="mt-1 text-xs text-red-600 font-bold">{errors.singkatan}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Nama Lengkap Lembaga</label>
                                <input type="text" value={data.nama_lembaga} onChange={(e) => setData('nama_lembaga', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500" />
                                {errors.nama_lembaga && <p className="mt-1 text-xs text-red-600 font-bold">{errors.nama_lembaga}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Deskripsi / Aturan Kementrian</label>
                                <textarea rows={3} value={data.deskripsi} onChange={(e) => setData('deskripsi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500" placeholder="Catatan aturan terbaru..."></textarea>
                            </div>
                            <div className="mt-8 flex justify-end gap-3 border-t pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-6 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-8 py-2 text-sm font-black text-white hover:bg-indigo-700 shadow-lg">
                                    {processing ? 'Menyimpan...' : 'SIMPAN LEMBAGA'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl border-t-4 border-rose-600">
                        <h3 className="mb-2 text-lg font-black text-rose-700">KONFIRMASI HAPUS</h3>
                        <p className="mb-6 text-sm text-gray-700">Hapus lembaga <strong>{deleteTarget.singkatan}</strong>? Tindakan ini permanen.</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">
                                {processing ? 'Menghapus...' : 'YA, HAPUS'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
