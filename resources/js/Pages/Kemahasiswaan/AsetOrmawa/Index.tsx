import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { Package } from 'lucide-react';

interface AsetItem {
    id: number;
    ormawa_id: number;
    ormawa?: { nama: string };
    nama_aset: string;
    jumlah: number;
    luas_ruang_m2: number;
    kondisi: string;
    tahun_perolehan: number;
}

interface Ormawa {
    id: number;
    nama: string;
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
    items: PaginatedData<AsetItem>;
    ormawa_list: Ormawa[];
    success?: string;
}

export default function Index({ items, ormawa_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [filterOrmawa, setFilterOrmawa] = useState(() => {
        return new URLSearchParams(window.location.search).get('ormawa_id') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<AsetItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<AsetItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        ormawa_id: '',
        nama_aset: '',
        jumlah: '',
        luas_ruang_m2: '',
        kondisi: '',
        tahun_perolehan: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.aset-ormawa'), { ormawa_id: filterOrmawa }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [filterOrmawa]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: AsetItem) {
        setEditing(item);
        setData({
            ormawa_id: String(item.ormawa_id),
            nama_aset: item.nama_aset,
            jumlah: String(item.jumlah),
            luas_ruang_m2: String(item.luas_ruang_m2),
            kondisi: item.kondisi,
            tahun_perolehan: String(item.tahun_perolehan),
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('kemahasiswaan.aset-ormawa.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('kemahasiswaan.aset-ormawa.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: AsetItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.aset-ormawa.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const kondisiBadge: Record<string, string> = {
        'Sangat Baik': 'bg-green-100 text-green-800',
        Baik: 'bg-blue-100 text-blue-800',
        'Rusak Ringan': 'bg-yellow-100 text-yellow-800',
        'Rusak Berat': 'bg-red-100 text-red-800',
    };

    const kondisiOptions = ['Sangat Baik', 'Baik', 'Rusak Ringan', 'Rusak Berat'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Aset Ormawa</h2>}
        >
            <Head title="Aset Ormawa" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Aset Ormawa</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Dashboard</Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex gap-3">
                                    <select
                                        value={filterOrmawa}
                                        onChange={(e) => setFilterOrmawa(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Ormawa</option>
                                        {ormawa_list.map((o) => (
                                            <option key={o.id} value={o.id}>{o.nama}</option>
                                        ))}
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Aset
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Aset</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ormawa</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Luas (m²)</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kondisi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tahun Perolehan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        items.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.nama_aset}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.ormawa?.nama || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jumlah}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.luas_ruang_m2 || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${kondisiBadge[item.kondisi] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.kondisi}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tahun_perolehan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {can('kemahasiswaan.edit') && (
                                                        <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    )}
                                                    {can('kemahasiswaan.delete') && (
                                                        <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {items.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {items.from} - {items.to} dari {items.total}
                                </div>
                                <div className="flex gap-1">
                                    {items.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
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
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Aset Ormawa' : 'Tambah Aset Ormawa'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Ormawa</label>
                                <select value={data.ormawa_id} onChange={(e) => setData('ormawa_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Ormawa</option>
                                    {ormawa_list.map((o) => (
                                        <option key={o.id} value={o.id}>{o.nama}</option>
                                    ))}
                                </select>
                                {errors.ormawa_id && <p className="mt-1 text-xs text-red-600">{errors.ormawa_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Aset</label>
                                <input
                                    type="text"
                                    value={data.nama_aset}
                                    onChange={(e) => setData('nama_aset', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama_aset && <p className="mt-1 text-xs text-red-600">{errors.nama_aset}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Jumlah</label>
                                    <input
                                        type="number"
                                        min="1"
                                        value={data.jumlah}
                                        onChange={(e) => setData('jumlah', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.jumlah && <p className="mt-1 text-xs text-red-600">{errors.jumlah}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Luas Ruang (m²)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.luas_ruang_m2}
                                        onChange={(e) => setData('luas_ruang_m2', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.luas_ruang_m2 && <p className="mt-1 text-xs text-red-600">{errors.luas_ruang_m2}</p>}
                                </div>
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Kondisi</label>
                                <select value={data.kondisi} onChange={(e) => setData('kondisi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kondisi</option>
                                    {kondisiOptions.map((k) => (
                                        <option key={k} value={k}>{k}</option>
                                    ))}
                                </select>
                                {errors.kondisi && <p className="mt-1 text-xs text-red-600">{errors.kondisi}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tahun Perolehan</label>
                                <input
                                    type="number"
                                    min="1900"
                                    max="2099"
                                    value={data.tahun_perolehan}
                                    onChange={(e) => setData('tahun_perolehan', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.tahun_perolehan && <p className="mt-1 text-xs text-red-600">{errors.tahun_perolehan}</p>}
                            </div>
                            <div className="flex justify-end gap-2">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700 disabled:opacity-50">
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus aset <strong>{deleteTarget.nama_aset}</strong>?</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:opacity-50">
                                {processing ? 'Menghapus...' : 'Hapus'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
