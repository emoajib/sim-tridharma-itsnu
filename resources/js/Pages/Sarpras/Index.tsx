import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface SaranaItem {
    id: number;
    prodi_id: number;
    nama_sarana: string;
    jenis_sarana: string;
    jumlah: number;
    kondisi: string | null;
    tanggal_kalibrasi: string | null;
    tanggal_kalibrasi_berikut: string | null;
    prodi?: { nama_prodi: string };
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
    sarana: PaginatedData<SaranaItem>;
    prodi_list: Prodi[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ sarana, prodi_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<SaranaItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<SaranaItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        prodi_id: '',
        nama_sarana: '',
        jenis_sarana: '',
        jumlah: '',
        kondisi: '',
        tanggal_kalibrasi: '',
        tanggal_kalibrasi_berikut: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('sarpras'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: SaranaItem) {
        setEditing(item);
        setData({
            prodi_id: String(item.prodi_id),
            nama_sarana: item.nama_sarana,
            jenis_sarana: item.jenis_sarana,
            jumlah: String(item.jumlah),
            kondisi: item.kondisi || '',
            tanggal_kalibrasi: item.tanggal_kalibrasi || '',
            tanggal_kalibrasi_berikut: item.tanggal_kalibrasi_berikut || '',
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('sarpras.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('sarpras.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: SaranaItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('sarpras.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function isNearExpiry(dateStr: string | null): boolean {
        if (!dateStr) return false;
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = date.getTime() - now.getTime();
        const diffDays = diffMs / (1000 * 60 * 60 * 24);
        return diffDays >= 0 && diffDays <= 30;
    }

    function isExpired(dateStr: string | null): boolean {
        if (!dateStr) return false;
        const date = new Date(dateStr);
        const now = new Date();
        return date.getTime() < now.getTime();
    }

    const kondisiBadge: Record<string, string> = {
        baik: 'bg-green-100 text-green-800',
        sedang: 'bg-yellow-100 text-yellow-800',
        rusak: 'bg-red-100 text-red-800',
    };

    const jenisOptions = ['laboratorium', 'ruang_kelas', 'komputer', 'peralatan', 'fasilitas_lain'];
    const kondisiOptions = ['baik', 'sedang', 'rusak'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sarpras</h2>}
        >
            <Head title="Sarpras" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Sarpras</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Dashboard</Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex items-center justify-between">
                                <input
                                    type="text"
                                    placeholder="Cari nama sarana..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Sarana
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Sarana</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kondisi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kalibrasi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {sarana.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        sarana.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.nama_sarana}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_sarana}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jumlah}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${kondisiBadge[item.kondisi || ''] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.kondisi ? item.kondisi.charAt(0).toUpperCase() + item.kondisi.slice(1) : '-'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.prodi?.nama_prodi || '-'}</td>
                                                <td className={`whitespace-nowrap px-6 py-4 text-sm ${(isNearExpiry(item.tanggal_kalibrasi_berikut) || isExpired(item.tanggal_kalibrasi_berikut)) ? 'text-red-600 font-semibold' : 'text-gray-700'}`}>
                                                    {item.tanggal_kalibrasi_berikut ? item.tanggal_kalibrasi_berikut : '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {sarana.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {sarana.from} - {sarana.to} dari {sarana.total}
                                </div>
                                <div className="flex gap-1">
                                    {sarana.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Sarana' : 'Tambah Sarana'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Sarana</label>
                                <input
                                    type="text"
                                    value={data.nama_sarana}
                                    onChange={(e) => setData('nama_sarana', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama_sarana && <p className="mt-1 text-xs text-red-600">{errors.nama_sarana}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Sarana</label>
                                <select value={data.jenis_sarana} onChange={(e) => setData('jenis_sarana', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    {jenisOptions.map((j) => (
                                        <option key={j} value={j}>{j.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase())}</option>
                                    ))}
                                </select>
                                {errors.jenis_sarana && <p className="mt-1 text-xs text-red-600">{errors.jenis_sarana}</p>}
                            </div>
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
                            {editing && (
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Kondisi</label>
                                    <select value={data.kondisi} onChange={(e) => setData('kondisi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Pilih Kondisi</option>
                                        {kondisiOptions.map((k) => (
                                            <option key={k} value={k}>{k.charAt(0).toUpperCase() + k.slice(1)}</option>
                                        ))}
                                    </select>
                                    {errors.kondisi && <p className="mt-1 text-xs text-red-600">{errors.kondisi}</p>}
                                </div>
                            )}
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Kalibrasi</label>
                                <input
                                    type="date"
                                    value={data.tanggal_kalibrasi}
                                    onChange={(e) => setData('tanggal_kalibrasi', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.tanggal_kalibrasi && <p className="mt-1 text-xs text-red-600">{errors.tanggal_kalibrasi}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Kalibrasi Berikut</label>
                                <input
                                    type="date"
                                    value={data.tanggal_kalibrasi_berikut}
                                    onChange={(e) => setData('tanggal_kalibrasi_berikut', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.tanggal_kalibrasi_berikut && <p className="mt-1 text-xs text-red-600">{errors.tanggal_kalibrasi_berikut}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus sarana <strong>{deleteTarget.nama_sarana}</strong>?</p>
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
