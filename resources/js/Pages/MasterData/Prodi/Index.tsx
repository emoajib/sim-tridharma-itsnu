import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface Prodi {
    id: number;
    kode_prodi: string;
    nama_prodi: string;
    fakultas_id: number;
    lembaga_akreditasi_id: number | null;
    jenjang: string;
    akreditasi: string | null;
    sk_akreditasi: string | null;
    is_active: boolean;
    fakultas?: { id: number; nama_fakultas: string };
    lembaga?: { id: number; nama_lembaga: string; singkatan: string };
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
    prodi: PaginatedData<Prodi>;
    fakultas_list: { id: number; nama_fakultas: string }[];
    lembaga_list: Lembaga[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ prodi, fakultas_list, lembaga_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Prodi | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Prodi | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        kode_prodi: '',
        nama_prodi: '',
        fakultas_id: '',
        lembaga_akreditasi_id: '',
        jenjang: '',
        akreditasi: '',
        sk_akreditasi: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('master-data.prodi'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Prodi) {
        setEditing(item);
        setData({
            kode_prodi: item.kode_prodi,
            nama_prodi: item.nama_prodi,
            fakultas_id: String(item.fakultas_id),
            lembaga_akreditasi_id: item.lembaga_akreditasi_id ? String(item.lembaga_akreditasi_id) : '',
            jenjang: item.jenjang,
            akreditasi: item.akreditasi || '',
            sk_akreditasi: item.sk_akreditasi || '',
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('master-data.prodi.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('master-data.prodi.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Prodi) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('master-data.prodi.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Manajemen & Ploting Program Studi</h2>}
        >
            <Head title="Manajemen Prodi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 font-bold border border-green-200">
                           ✅ {success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex items-center justify-between">
                                <input
                                    type="text"
                                    placeholder="Cari prodi..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 shadow-md"
                                >
                                    + Tambah & Plotting Prodi
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Kode</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Nama Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Lembaga Akreditasi (Ploting)</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {prodi.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-12 text-center text-gray-500 italic">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        prodi.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-black text-gray-900">{item.kode_prodi}</td>
                                                <td className="px-6 py-4 text-sm text-gray-700 font-medium">
                                                    {item.nama_prodi}
                                                    <div className="text-[10px] text-gray-400 font-normal uppercase">{item.jenjang} - {item.fakultas?.nama_fakultas}</div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex px-3 py-1 rounded-full text-xs font-black border ${
                                                        item.lembaga ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-rose-50 text-rose-700 border-rose-200'
                                                    }`}>
                                                        {item.lembaga?.singkatan || 'BELUM DI-PLOT'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-bold text-emerald-600">{item.akreditasi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <button onClick={() => openEdit(item)} className="mr-3 font-bold text-indigo-600 hover:text-indigo-900 underline">Edit / Plotting</button>
                                                    <button onClick={() => confirmDelete(item)} className="font-bold text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {prodi.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {prodi.from} - {prodi.to} dari {prodi.total}
                                </div>
                                <div className="flex gap-1">
                                    {prodi.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm font-bold ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-lg rounded-xl bg-white p-8 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between border-b pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">{editing ? 'Edit & Plotting Prodi' : 'Tambah Prodi Baru'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-5">
                            <div className="grid grid-cols-3 gap-4">
                                <div className="col-span-1">
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Kode Prodi</label>
                                    <input type="text" value={data.kode_prodi} onChange={(e) => setData('kode_prodi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500 uppercase" />
                                </div>
                                <div className="col-span-2">
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Nama Program Studi</label>
                                    <input type="text" value={data.nama_prodi} onChange={(e) => setData('nama_prodi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div>
                                <label className="mb-1 block text-xs font-black text-gray-500 uppercase underline decoration-rose-500">Lembaga Akreditasi (PLOTING)</label>
                                <select value={data.lembaga_akreditasi_id} onChange={(e) => setData('lembaga_akreditasi_id', e.target.value)} className="w-full rounded-lg border-rose-300 bg-rose-50 text-sm font-black text-rose-800 focus:ring-rose-500">
                                    <option value="">-- PILIH LEMBAGA PENILAI --</option>
                                    {lembaga_list.map((l) => (
                                        <option key={l.id} value={l.id}>{l.singkatan} - {l.nama_lembaga}</option>
                                    ))}
                                </select>
                                <p className="mt-1 text-[10px] text-rose-600 italic">* Menentukan dashboard mana prodi ini akan muncul.</p>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Fakultas</label>
                                    <select value={data.fakultas_id} onChange={(e) => setData('fakultas_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Fakultas</option>
                                        {fakultas_list.map((f) => (
                                            <option key={f.id} value={f.id}>{f.nama_fakultas}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Jenjang</label>
                                    <select value={data.jenjang} onChange={(e) => setData('jenjang', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Jenjang</option>
                                        <option value="S1">S1</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="S2">S2</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Akreditasi Saat Ini</label>
                                    <input type="text" value={data.akreditasi} onChange={(e) => setData('akreditasi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500" placeholder="Misal: Baik Sekali" />
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Nomor SK</label>
                                    <input type="text" value={data.sk_akreditasi} onChange={(e) => setData('sk_akreditasi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-6 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-8 py-2 text-sm font-black text-white hover:bg-indigo-700 shadow-lg transition-all active:scale-95 disabled:opacity-50">
                                    {processing ? 'Menyimpan...' : 'SIMPAN DATA & PLOTING'}
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
                        <p className="mb-6 text-sm text-gray-700">Menghapus prodi <strong>{deleteTarget.nama_prodi}</strong> akan berdampak pada seluruh data portofolio terkait. Lanjutkan?</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">
                                {processing ? 'Menghapus...' : 'YA, HAPUS PERMANEN'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
