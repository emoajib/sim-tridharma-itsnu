import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';

interface OrmawaItem {
    id: number;
    nama: string;
    kategori: string;
    prodi_id: number;
    prodi?: { nama_prodi: string };
    visi_misi: string;
    file_ad_art: string;
    created_at: string;
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
    items: PaginatedData<OrmawaItem>;
    prodi_list: Prodi[];
    success?: string;
}

export default function Index({ items, prodi_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [filterKategori, setFilterKategori] = useState(() => {
        return new URLSearchParams(window.location.search).get('kategori') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<OrmawaItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<OrmawaItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        nama: '',
        kategori: '',
        prodi_id: '',
        visi_misi: '',
        file_ad_art: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.ormawa'), { search, kategori: filterKategori }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, filterKategori]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: OrmawaItem) {
        setEditing(item);
        setData({
            nama: item.nama,
            kategori: item.kategori,
            prodi_id: String(item.prodi_id),
            visi_misi: item.visi_misi,
            file_ad_art: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('nama', data.nama);
        formData.append('kategori', data.kategori);
        formData.append('prodi_id', data.prodi_id);
        formData.append('visi_misi', data.visi_misi);
        if (data.file_ad_art) formData.append('file_ad_art', data.file_ad_art);
        if (editing) formData.append('_method', 'PUT');

        if (editing) {
            put(route('kemahasiswaan.ormawa.update', editing.id), {
                headers: { 'Content-Type': 'multipart/form-data' },
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('kemahasiswaan.ormawa.store'), {
                headers: { 'Content-Type': 'multipart/form-data' },
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: OrmawaItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.ormawa.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const kategoriBadge: Record<string, string> = {
        BEM: 'bg-red-100 text-red-800',
        DPM: 'bg-blue-100 text-blue-800',
        HIMA: 'bg-green-100 text-green-800',
        UKM: 'bg-purple-100 text-purple-800',
    };

    const kategoriOptions = ['BEM', 'DPM', 'HIMA', 'UKM'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Ormawa</h2>}
        >
            <Head title="Ormawa" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Ormawa</span>
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
                                    <input
                                        type="text"
                                        placeholder="Cari nama ormawa..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select
                                        value={filterKategori}
                                        onChange={(e) => setFilterKategori(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Kategori</option>
                                        {kategoriOptions.map((k) => (
                                            <option key={k} value={k}>{k}</option>
                                        ))}
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Ormawa
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kategori</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">File AD/ART</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        items.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.nama}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${kategoriBadge[item.kategori] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.kategori}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.prodi?.nama_prodi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {item.file_ad_art ? (
                                                        <a href={`/storage/${item.file_ad_art}`} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    ) : '-'}
                                                </td>
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Ormawa' : 'Tambah Ormawa'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Ormawa</label>
                                <input
                                    type="text"
                                    value={data.nama}
                                    onChange={(e) => setData('nama', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama && <p className="mt-1 text-xs text-red-600">{errors.nama}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                                <select value={data.kategori} onChange={(e) => setData('kategori', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kategori</option>
                                    {kategoriOptions.map((k) => (
                                        <option key={k} value={k}>{k}</option>
                                    ))}
                                </select>
                                {errors.kategori && <p className="mt-1 text-xs text-red-600">{errors.kategori}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Prodi</label>
                                <select value={data.prodi_id} onChange={(e) => setData('prodi_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                                {errors.prodi_id && <p className="mt-1 text-xs text-red-600">{errors.prodi_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Visi Misi</label>
                                <textarea
                                    value={data.visi_misi}
                                    onChange={(e) => setData('visi_misi', e.target.value)}
                                    rows={4}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.visi_misi && <p className="mt-1 text-xs text-red-600">{errors.visi_misi}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File AD/ART</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_ad_art', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_ad_art && <p className="mt-1 text-xs text-red-600">{errors.file_ad_art}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus ormawa <strong>{deleteTarget.nama}</strong>?</p>
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
