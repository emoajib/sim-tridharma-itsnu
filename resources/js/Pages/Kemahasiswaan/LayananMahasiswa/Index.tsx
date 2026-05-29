import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';

interface LayananItem {
    id: number;
    periode_id: number;
    periode?: { nama_periode: string };
    jenis_layanan: string;
    nama_program: string;
    tanggal_pelaksanaan: string;
    jumlah_peserta: number;
    file_surat_tugas: string;
    file_laporan: string;
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
    items: PaginatedData<LayananItem>;
    periode_list: Periode[];
    success?: string;
}

export default function Index({ items, periode_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [filterJenis, setFilterJenis] = useState(() => {
        return new URLSearchParams(window.location.search).get('jenis_layanan') || '';
    });
    const [filterPeriode, setFilterPeriode] = useState(() => {
        return new URLSearchParams(window.location.search).get('periode_id') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<LayananItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<LayananItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        periode_id: '',
        jenis_layanan: '',
        nama_program: '',
        tanggal_pelaksanaan: '',
        jumlah_peserta: '',
        file_surat_tugas: null as File | null,
        file_laporan: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.layanan-mahasiswa'), {
                search, jenis_layanan: filterJenis, periode_id: filterPeriode
            }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, filterJenis, filterPeriode]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: LayananItem) {
        setEditing(item);
        setData({
            periode_id: String(item.periode_id),
            jenis_layanan: item.jenis_layanan,
            nama_program: item.nama_program,
            tanggal_pelaksanaan: item.tanggal_pelaksanaan,
            jumlah_peserta: String(item.jumlah_peserta),
            file_surat_tugas: null,
            file_laporan: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('periode_id', data.periode_id);
        formData.append('jenis_layanan', data.jenis_layanan);
        formData.append('nama_program', data.nama_program);
        formData.append('tanggal_pelaksanaan', data.tanggal_pelaksanaan);
        formData.append('jumlah_peserta', data.jumlah_peserta);
        if (data.file_surat_tugas) formData.append('file_surat_tugas', data.file_surat_tugas);
        if (data.file_laporan) formData.append('file_laporan', data.file_laporan);
        if (editing) formData.append('_method', 'PUT');

        const url = editing
            ? route('kemahasiswaan.layanan-mahasiswa.update', editing.id)
            : route('kemahasiswaan.layanan-mahasiswa.store');
        post(url, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
        });
    };

    function confirmDelete(item: LayananItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.layanan-mahasiswa.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const jenisLayananOptions = ['Bimbingan Karir', 'Kewirausahaan', 'Kesehatan', 'Beasiswa'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Layanan Mahasiswa</h2>}
        >
            <Head title="Layanan Mahasiswa" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Layanan Mahasiswa</span>
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
                                        placeholder="Cari program..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-48 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select
                                        value={filterJenis}
                                        onChange={(e) => setFilterJenis(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Jenis</option>
                                        {jenisLayananOptions.map((j) => (
                                            <option key={j} value={j}>{j}</option>
                                        ))}
                                    </select>
                                    <select
                                        value={filterPeriode}
                                        onChange={(e) => setFilterPeriode(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Periode</option>
                                        {periode_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                        ))}
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Layanan
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Program</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis Layanan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Periode</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Peserta</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        items.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.nama_program}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_layanan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.periode?.nama_periode || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tanggal_pelaksanaan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jumlah_peserta}</td>
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Layanan Mahasiswa' : 'Tambah Layanan Mahasiswa'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Layanan</label>
                                <select value={data.jenis_layanan} onChange={(e) => setData('jenis_layanan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    {jenisLayananOptions.map((j) => (
                                        <option key={j} value={j}>{j}</option>
                                    ))}
                                </select>
                                {errors.jenis_layanan && <p className="mt-1 text-xs text-red-600">{errors.jenis_layanan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Program</label>
                                <input
                                    type="text"
                                    value={data.nama_program}
                                    onChange={(e) => setData('nama_program', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama_program && <p className="mt-1 text-xs text-red-600">{errors.nama_program}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Pelaksanaan</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_pelaksanaan}
                                        onChange={(e) => setData('tanggal_pelaksanaan', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_pelaksanaan && <p className="mt-1 text-xs text-red-600">{errors.tanggal_pelaksanaan}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Jumlah Peserta</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={data.jumlah_peserta}
                                        onChange={(e) => setData('jumlah_peserta', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.jumlah_peserta && <p className="mt-1 text-xs text-red-600">{errors.jumlah_peserta}</p>}
                                </div>
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Surat Tugas</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_surat_tugas', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_surat_tugas && <p className="mt-1 text-xs text-red-600">{errors.file_surat_tugas}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Laporan</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_laporan', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_laporan && <p className="mt-1 text-xs text-red-600">{errors.file_laporan}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus layanan <strong>{deleteTarget.nama_program}</strong>?</p>
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
