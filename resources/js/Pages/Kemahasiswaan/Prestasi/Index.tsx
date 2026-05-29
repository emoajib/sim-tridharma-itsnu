import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { Award, Eye } from 'lucide-react';

interface PrestasiMember {
    id: number;
    mahasiswa_id: number;
    mahasiswa?: { nama: string; nim: string };
    peran: string;
}

interface PrestasiItem {
    id: number;
    kategori_id: number;
    kategori?: { nama_kategori: string };
    nama_kompetisi: string;
    penyelenggara: string;
    tanggal_pelaksanaan: string;
    tingkat: string;
    peringkat: string;
    bukti_url: string;
    file_sertifikat: string;
    status_verifikasi: string;
    catatan_reviewer: string;
    verified_at: string;
    members?: PrestasiMember[];
}

interface Kategori {
    id: number;
    nama_kategori: string;
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
    items: PaginatedData<PrestasiItem>;
    kategori_list: Kategori[];
    success?: string;
}

export default function Index({ items, kategori_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [filterTingkat, setFilterTingkat] = useState(() => {
        return new URLSearchParams(window.location.search).get('tingkat') || '';
    });
    const [filterStatus, setFilterStatus] = useState(() => {
        return new URLSearchParams(window.location.search).get('status_verifikasi') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<PrestasiItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<PrestasiItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        kategori_id: '',
        nama_kompetisi: '',
        penyelenggara: '',
        tanggal_pelaksanaan: '',
        tingkat: '',
        peringkat: '',
        bukti_url: '',
        file_sertifikat: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.prestasi'), { search, tingkat: filterTingkat, status_verifikasi: filterStatus }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search, filterTingkat, filterStatus]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: PrestasiItem) {
        setEditing(item);
        setData({
            kategori_id: String(item.kategori_id),
            nama_kompetisi: item.nama_kompetisi,
            penyelenggara: item.penyelenggara,
            tanggal_pelaksanaan: item.tanggal_pelaksanaan,
            tingkat: item.tingkat,
            peringkat: item.peringkat,
            bukti_url: item.bukti_url,
            file_sertifikat: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('kategori_id', data.kategori_id);
        formData.append('nama_kompetisi', data.nama_kompetisi);
        formData.append('penyelenggara', data.penyelenggara);
        formData.append('tanggal_pelaksanaan', data.tanggal_pelaksanaan);
        formData.append('tingkat', data.tingkat);
        formData.append('peringkat', data.peringkat);
        formData.append('bukti_url', data.bukti_url);
        if (data.file_sertifikat) formData.append('file_sertifikat', data.file_sertifikat);
        if (editing) formData.append('_method', 'PUT');

        const url = editing
            ? route('kemahasiswaan.prestasi.update', editing.id)
            : route('kemahasiswaan.prestasi.store');
        post(url, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
        });
    };

    function confirmDelete(item: PrestasiItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.prestasi.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function handleVerify(id: number) {
        router.post(route('kemahasiswaan.prestasi.verify', id), {}, {
            preserveScroll: true,
        });
    }

    const statusBadge: Record<string, string> = {
        DRAFT: 'bg-gray-100 text-gray-800',
        SUBMITTED: 'bg-blue-100 text-blue-800',
        REVISION_REQUESTED: 'bg-yellow-100 text-yellow-800',
        VERIFIED: 'bg-green-100 text-green-800',
    };

    const tingkatOptions = ['Lokal/Wilayah', 'Nasional', 'Internasional'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Prestasi</h2>}
        >
            <Head title="Prestasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Prestasi</span>
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
                                        placeholder="Cari nama kompetisi..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select
                                        value={filterTingkat}
                                        onChange={(e) => setFilterTingkat(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Tingkat</option>
                                        {tingkatOptions.map((t) => (
                                            <option key={t} value={t}>{t}</option>
                                        ))}
                                    </select>
                                    <select
                                        value={filterStatus}
                                        onChange={(e) => setFilterStatus(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="DRAFT">Draft</option>
                                        <option value="SUBMITTED">Submitted</option>
                                        <option value="REVISION_REQUESTED">Revisi</option>
                                        <option value="VERIFIED">Terverifikasi</option>
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Prestasi
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kompetisi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kategori</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tingkat</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Peringkat</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
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
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.nama_kompetisi}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.kategori?.nama_kategori || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tingkat}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.peringkat}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tanggal_pelaksanaan}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[item.status_verifikasi] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.status_verifikasi}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <Link href={route('kemahasiswaan.prestasi.show', item.id)} className="mr-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Prestasi' : 'Tambah Prestasi'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                                <select value={data.kategori_id} onChange={(e) => setData('kategori_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kategori</option>
                                    {kategori_list.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama_kategori}</option>
                                    ))}
                                </select>
                                {errors.kategori_id && <p className="mt-1 text-xs text-red-600">{errors.kategori_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Kompetisi</label>
                                <input
                                    type="text"
                                    value={data.nama_kompetisi}
                                    onChange={(e) => setData('nama_kompetisi', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nama_kompetisi && <p className="mt-1 text-xs text-red-600">{errors.nama_kompetisi}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Penyelenggara</label>
                                <input
                                    type="text"
                                    value={data.penyelenggara}
                                    onChange={(e) => setData('penyelenggara', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.penyelenggara && <p className="mt-1 text-xs text-red-600">{errors.penyelenggara}</p>}
                            </div>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tingkat</label>
                                <select value={data.tingkat} onChange={(e) => setData('tingkat', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Tingkat</option>
                                    {tingkatOptions.map((t) => (
                                        <option key={t} value={t}>{t}</option>
                                    ))}
                                </select>
                                {errors.tingkat && <p className="mt-1 text-xs text-red-600">{errors.tingkat}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Peringkat</label>
                                <input
                                    type="text"
                                    value={data.peringkat}
                                    onChange={(e) => setData('peringkat', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.peringkat && <p className="mt-1 text-xs text-red-600">{errors.peringkat}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">URL Bukti</label>
                                <input
                                    type="url"
                                    value={data.bukti_url}
                                    onChange={(e) => setData('bukti_url', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.bukti_url && <p className="mt-1 text-xs text-red-600">{errors.bukti_url}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus prestasi <strong>{deleteTarget.nama_kompetisi}</strong>?</p>
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
