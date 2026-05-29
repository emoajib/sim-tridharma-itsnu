import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { Scroll } from 'lucide-react';

interface SkpiItem {
    id: number;
    mahasiswa_id: number;
    mahasiswa?: { nama: string; nim: string };
    periode_id: number;
    periode?: { nama_periode: string };
    jenis_kegiatan: string;
    nama_kegiatan: string;
    tingkat: string | null;
    peran: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    jam_kompen: number;
    poin_skpi: number;
    file_sertifikat: string;
    status_verifikasi: string;
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
    items: PaginatedData<SkpiItem>;
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
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<SkpiItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<SkpiItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        mahasiswa_id: '',
        periode_id: '',
        jenis_kegiatan: '',
        nama_kegiatan: '',
        tingkat: '',
        peran: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
        jam_kompen: '',
        poin_skpi: '',
        file_sertifikat: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.skpi'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: SkpiItem) {
        setEditing(item);
        setData({
            mahasiswa_id: String(item.mahasiswa_id),
            periode_id: String(item.periode_id),
            jenis_kegiatan: item.jenis_kegiatan,
            nama_kegiatan: item.nama_kegiatan,
            tingkat: item.tingkat || '',
            peran: item.peran,
            tanggal_mulai: item.tanggal_mulai,
            tanggal_selesai: item.tanggal_selesai,
            jam_kompen: String(item.jam_kompen),
            poin_skpi: String(item.poin_skpi),
            file_sertifikat: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('mahasiswa_id', data.mahasiswa_id);
        formData.append('periode_id', data.periode_id);
        formData.append('jenis_kegiatan', data.jenis_kegiatan);
        formData.append('nama_kegiatan', data.nama_kegiatan);
        formData.append('tingkat', data.tingkat);
        formData.append('peran', data.peran);
        formData.append('tanggal_mulai', data.tanggal_mulai);
        formData.append('tanggal_selesai', data.tanggal_selesai);
        formData.append('jam_kompen', data.jam_kompen);
        formData.append('poin_skpi', data.poin_skpi);
        if (data.file_sertifikat) formData.append('file_sertifikat', data.file_sertifikat);
        if (editing) formData.append('_method', 'PUT');

        const url = editing
            ? route('kemahasiswaan.skpi.update', editing.id)
            : route('kemahasiswaan.skpi.store');
        post(url, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
        });
    };

    function confirmDelete(item: SkpiItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.skpi.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function handleVerify(id: number) {
        router.post(route('kemahasiswaan.skpi.verify', id), {}, {
            preserveScroll: true,
        });
    }

    const statusBadge: Record<string, string> = {
        DRAFT: 'bg-gray-100 text-gray-800',
        SUBMITTED: 'bg-blue-100 text-blue-800',
        REVISION_REQUESTED: 'bg-yellow-100 text-yellow-800',
        VERIFIED: 'bg-green-100 text-green-800',
    };

    const jenisKegiatanOptions = ['Organisasi', 'Kepanitiaan', 'Prestasi', 'Sertifikasi', 'Lainnya'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">SKPI</h2>}
        >
            <Head title="SKPI" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">SKPI</span>
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
                                    placeholder="Cari kegiatan..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah SKPI
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mahasiswa</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kegiatan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Poin</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
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
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.mahasiswa?.nama || '-'} ({item.mahasiswa?.nim || '-'})</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.nama_kegiatan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_kegiatan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.poin_skpi}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[item.status_verifikasi] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.status_verifikasi}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {can('kemahasiswaan.edit') && (
                                                        <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    )}
                                                    {can('kemahasiswaan.verify') && item.status_verifikasi === 'SUBMITTED' && (
                                                        <button onClick={() => handleVerify(item.id)} className="mr-2 text-green-600 hover:text-green-900">Verify</button>
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit SKPI' : 'Tambah SKPI'}</h3>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Kegiatan</label>
                                <select value={data.jenis_kegiatan} onChange={(e) => setData('jenis_kegiatan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    {jenisKegiatanOptions.map((j) => (
                                        <option key={j} value={j}>{j}</option>
                                    ))}
                                </select>
                                {errors.jenis_kegiatan && <p className="mt-1 text-xs text-red-600">{errors.jenis_kegiatan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                                <input
                                    type="text"
                                    value={data.nama_kegiatan}
                                    onChange={(e) => setData('nama_kegiatan', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama_kegiatan && <p className="mt-1 text-xs text-red-600">{errors.nama_kegiatan}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tingkat</label>
                                    <select value={data.tingkat} onChange={(e) => setData('tingkat', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Pilih Tingkat</option>
                                        <option value="Lokal">Lokal</option>
                                        <option value="Wilayah">Wilayah</option>
                                        <option value="Nasional">Nasional</option>
                                        <option value="Internasional">Internasional</option>
                                    </select>
                                    {errors.tingkat && <p className="mt-1 text-xs text-red-600">{errors.tingkat}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Peran</label>
                                    <input
                                        type="text"
                                        value={data.peran}
                                        onChange={(e) => setData('peran', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.peran && <p className="mt-1 text-xs text-red-600">{errors.peran}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input type="date" value={data.tanggal_mulai} onChange={(e) => setData('tanggal_mulai', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    {errors.tanggal_mulai && <p className="mt-1 text-xs text-red-600">{errors.tanggal_mulai}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                    <input type="date" value={data.tanggal_selesai} onChange={(e) => setData('tanggal_selesai', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    {errors.tanggal_selesai && <p className="mt-1 text-xs text-red-600">{errors.tanggal_selesai}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Jam Kompen</label>
                                    <input type="number" min="0" value={data.jam_kompen} onChange={(e) => setData('jam_kompen', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    {errors.jam_kompen && <p className="mt-1 text-xs text-red-600">{errors.jam_kompen}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Poin SKPI</label>
                                    <input type="number" min="0" value={data.poin_skpi} onChange={(e) => setData('poin_skpi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    {errors.poin_skpi && <p className="mt-1 text-xs text-red-600">{errors.poin_skpi}</p>}
                                </div>
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus data ini?</p>
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
