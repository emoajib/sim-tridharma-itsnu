import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { Download } from 'lucide-react';

interface SertifikatItem {
    id: number;
    mahasiswa_id: number;
    mahasiswa?: { nama: string; nim: string };
    periode_id: number;
    periode?: { nama_periode: string };
    jenis_sertifikat: string;
    nomor_sertifikat: string;
    tanggal_terbit: string;
    file_sertifikat: string;
    is_downloadable: boolean;
}

interface Mahasiswa {
    id: number;
    nama: string;
    nim: string;
}

interface Periode {
    id: number;
    nama_periode: string;
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
    items: PaginatedData<SertifikatItem>;
    mahasiswa_list: Mahasiswa[];
    periode_list: Periode[];
    success?: string;
}

export default function Index({ items, mahasiswa_list, periode_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [filterJenis, setFilterJenis] = useState(() => {
        return new URLSearchParams(window.location.search).get('jenis_sertifikat') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<SertifikatItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<SertifikatItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        mahasiswa_id: '',
        periode_id: '',
        jenis_sertifikat: '',
        nomor_sertifikat: '',
        tanggal_terbit: '',
        file_sertifikat: null as File | null,
        is_downloadable: false,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.sertifikat-ostamaru'), {
                search, jenis_sertifikat: filterJenis
            }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, filterJenis]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: SertifikatItem) {
        setEditing(item);
        setData({
            mahasiswa_id: String(item.mahasiswa_id),
            periode_id: String(item.periode_id),
            jenis_sertifikat: item.jenis_sertifikat,
            nomor_sertifikat: item.nomor_sertifikat,
            tanggal_terbit: item.tanggal_terbit,
            file_sertifikat: null,
            is_downloadable: item.is_downloadable,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('mahasiswa_id', data.mahasiswa_id);
        formData.append('periode_id', data.periode_id);
        formData.append('jenis_sertifikat', data.jenis_sertifikat);
        formData.append('nomor_sertifikat', data.nomor_sertifikat);
        formData.append('tanggal_terbit', data.tanggal_terbit);
        formData.append('is_downloadable', data.is_downloadable ? '1' : '0');
        if (data.file_sertifikat) formData.append('file_sertifikat', data.file_sertifikat);
        if (editing) formData.append('_method', 'PUT');

        const url = editing
            ? route('kemahasiswaan.sertifikat-ostamaru.update', editing.id)
            : route('kemahasiswaan.sertifikat-ostamaru.store');
        post(url, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
        });
    };

    function confirmDelete(item: SertifikatItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.sertifikat-ostamaru.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const jenisOptions = ['OSTAMARU', 'PK2', 'Diksar', 'Lainnya'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sertifikat Ostamaru</h2>}
        >
            <Head title="Sertifikat Ostamaru" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Sertifikat Ostamaru</span>
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
                                <div className="flex flex-wrap gap-3">
                                    <input
                                        type="text"
                                        placeholder="Cari nomor sertifikat..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select
                                        value={filterJenis}
                                        onChange={(e) => setFilterJenis(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Jenis</option>
                                        {jenisOptions.map((j) => (
                                            <option key={j} value={j}>{j}</option>
                                        ))}
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Sertifikat
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mahasiswa</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nomor Sertifikat</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal Terbit</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Downloadable</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">File</th>
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
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.mahasiswa?.nama || '-'} ({item.mahasiswa?.nim || '-'})</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_sertifikat}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.nomor_sertifikat}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tanggal_terbit}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${item.is_downloadable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                        {item.is_downloadable ? 'Yes' : 'No'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {item.file_sertifikat ? (
                                                        <a href={`/storage/${item.file_sertifikat}`} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    ) : '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {item.is_downloadable && (
                                                        <a
                                                            href={route('kemahasiswaan.sertifikat-ostamaru.download', item.id)}
                                                            className="mr-2 inline-flex items-center text-green-600 hover:text-green-900"
                                                        >
                                                            <Download className="h-4 w-4" />
                                                        </a>
                                                    )}
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Sertifikat' : 'Tambah Sertifikat'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Mahasiswa</label>
                                <select value={data.mahasiswa_id} onChange={(e) => setData('mahasiswa_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Mahasiswa</option>
                                    {mahasiswa_list.map((m) => (
                                        <option key={m.id} value={m.id}>{m.nama} ({m.nim})</option>
                                    ))}
                                </select>
                                {errors.mahasiswa_id && <p className="mt-1 text-xs text-red-600">{errors.mahasiswa_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                <select value={data.periode_id} onChange={(e) => setData('periode_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                                {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Sertifikat</label>
                                <select value={data.jenis_sertifikat} onChange={(e) => setData('jenis_sertifikat', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    {jenisOptions.map((j) => (
                                        <option key={j} value={j}>{j}</option>
                                    ))}
                                </select>
                                {errors.jenis_sertifikat && <p className="mt-1 text-xs text-red-600">{errors.jenis_sertifikat}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nomor Sertifikat</label>
                                <input
                                    type="text"
                                    value={data.nomor_sertifikat}
                                    onChange={(e) => setData('nomor_sertifikat', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nomor_sertifikat && <p className="mt-1 text-xs text-red-600">{errors.nomor_sertifikat}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Terbit</label>
                                <input
                                    type="date"
                                    value={data.tanggal_terbit}
                                    onChange={(e) => setData('tanggal_terbit', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.tanggal_terbit && <p className="mt-1 text-xs text-red-600">{errors.tanggal_terbit}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Sertifikat</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_sertifikat', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_sertifikat && <p className="mt-1 text-xs text-red-600">{errors.file_sertifikat}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input
                                        type="checkbox"
                                        checked={data.is_downloadable}
                                        onChange={(e) => setData('is_downloadable', e.target.checked)}
                                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    Dapat Didownload
                                </label>
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus sertifikat <strong>{deleteTarget.nomor_sertifikat}</strong>?</p>
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
