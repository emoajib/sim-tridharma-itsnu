import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Dosen {
    id: number;
    nama_depan: string;
    nama_belakang: string;
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Periode {
    id: number;
    nama_periode: string;
}

interface MataKuliah {
    id: number;
    kode_mk: string;
    nama_mk: string;
}

interface KegiatanPendidikan {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    nama_kegiatan: string;
    jenis_kegiatan: string;
    mata_kuliah_id: number | null;
    sks: number | null;
    jumlah_mahasiswa: number | null;
    jumlah_pertemuan: number | null;
    is_verified: boolean;
    dosen?: { nama_depan: string; nama_belakang: string };
    mata_kuliah?: { kode_mk: string; nama_mk: string };
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
    kegiatan_pendidikan: PaginatedData<KegiatanPendidikan>;
    dosen_list: Dosen[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    mata_kuliah_list: MataKuliah[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ kegiatan_pendidikan, dosen_list, prodi_list, periode_list, mata_kuliah_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<KegiatanPendidikan | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<KegiatanPendidikan | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        dosen_id: '',
        prodi_id: '',
        periode_id: '',
        nama_kegiatan: '',
        jenis_kegiatan: '',
        mata_kuliah_id: '',
        sks: '',
        jumlah_mahasiswa: '',
        jumlah_pertemuan: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('portofolio.pendidikan'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: KegiatanPendidikan) {
        setEditing(item);
        setData({
            dosen_id: String(item.dosen_id),
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            nama_kegiatan: item.nama_kegiatan,
            jenis_kegiatan: item.jenis_kegiatan,
            mata_kuliah_id: String(item.mata_kuliah_id || ''),
            sks: String(item.sks || ''),
            jumlah_mahasiswa: String(item.jumlah_mahasiswa || ''),
            jumlah_pertemuan: String(item.jumlah_pertemuan || ''),
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('portofolio.pendidikan.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('portofolio.pendidikan.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: KegiatanPendidikan) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('portofolio.pendidikan.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Kegiatan Pendidikan</h2>}
        >
            <Head title="Kegiatan Pendidikan" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link href={route('portofolio')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Portofolio</Link>
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
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Kegiatan
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dosen</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Kegiatan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">MK</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">SKS</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jml Mhs</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {kegiatan_pendidikan.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        kegiatan_pendidikan.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.dosen?.nama_depan || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.nama_kegiatan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_kegiatan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.mata_kuliah?.nama_mk || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.sks ?? '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jumlah_mahasiswa ?? '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${item.is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                                        {item.is_verified ? 'Terverifikasi' : 'Belum'}
                                                    </span>
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

                        {kegiatan_pendidikan.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {kegiatan_pendidikan.from} - {kegiatan_pendidikan.to} dari {kegiatan_pendidikan.total}
                                </div>
                                <div className="flex gap-1">
                                    {kegiatan_pendidikan.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Kegiatan Pendidikan' : 'Tambah Kegiatan Pendidikan'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Dosen</label>
                                <select value={data.dosen_id} onChange={(e) => setData('dosen_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Dosen</option>
                                    {dosen_list.map((d) => (
                                        <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
                                    ))}
                                </select>
                                {errors.dosen_id && <p className="mt-1 text-xs text-red-600">{errors.dosen_id}</p>}
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Kegiatan</label>
                                <input type="text" value={data.nama_kegiatan} onChange={(e) => setData('nama_kegiatan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.nama_kegiatan && <p className="mt-1 text-xs text-red-600">{errors.nama_kegiatan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Kegiatan</label>
                                <select value={data.jenis_kegiatan} onChange={(e) => setData('jenis_kegiatan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    <option value="mengajar">Mengajar</option>
                                    <option value="bimbingan">Bimbingan</option>
                                    <option value="ujian">Ujian</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                {errors.jenis_kegiatan && <p className="mt-1 text-xs text-red-600">{errors.jenis_kegiatan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Mata Kuliah</label>
                                <select value={data.mata_kuliah_id} onChange={(e) => setData('mata_kuliah_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih MK</option>
                                    {mata_kuliah_list.map((m) => (
                                        <option key={m.id} value={m.id}>{m.kode_mk} - {m.nama_mk}</option>
                                    ))}
                                </select>
                                {errors.mata_kuliah_id && <p className="mt-1 text-xs text-red-600">{errors.mata_kuliah_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">SKS</label>
                                <input type="number" value={data.sks} onChange={(e) => setData('sks', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.sks && <p className="mt-1 text-xs text-red-600">{errors.sks}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jumlah Mahasiswa</label>
                                <input type="number" value={data.jumlah_mahasiswa} onChange={(e) => setData('jumlah_mahasiswa', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.jumlah_mahasiswa && <p className="mt-1 text-xs text-red-600">{errors.jumlah_mahasiswa}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jumlah Pertemuan</label>
                                <input type="number" value={data.jumlah_pertemuan} onChange={(e) => setData('jumlah_pertemuan', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.jumlah_pertemuan && <p className="mt-1 text-xs text-red-600">{errors.jumlah_pertemuan}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus <strong>{deleteTarget.nama_kegiatan}</strong>?</p>
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
