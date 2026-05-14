import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler, ChangeEvent } from 'react';

interface Dosen {
    id: number;
    nama_depan: string;
    nama_belakang: string;
}

interface DokumenItem {
    id: number;
    dosen_id: number;
    nama_dokumen: string;
    file_path: string | null;
    file_size: number | null;
    tipe: string | null;
    keterangan: string | null;
    is_verified: boolean;
    dosen?: { nama_depan: string; nama_belakang: string };
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
    dokumen: PaginatedData<DokumenItem>;
    dosen_list: Dosen[];
    success?: string;
    errors?: Record<string, string>;
}

function formatFileSize(bytes: number | null): string {
    if (!bytes) return '-';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function fileTypeBadge(tipe: string | null) {
    const t = (tipe || '').toUpperCase();
    let cls = 'bg-gray-100 text-gray-800';
    if (t === 'PDF') cls = 'bg-red-100 text-red-800';
    else if (t === 'DOCX' || t === 'DOC') cls = 'bg-blue-100 text-blue-800';
    else if (t === 'JPG' || t === 'JPEG' || t === 'PNG') cls = 'bg-green-100 text-green-800';
    return (
        <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${cls}`}>
            {t || '-'}
        </span>
    );
}

export default function Index({ dokumen, dosen_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<DokumenItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<DokumenItem | null>(null);
    const [file, setFile] = useState<File | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        dosen_id: '',
        nama_dokumen: '',
        keterangan: '',
        file: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('dokumen'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setFile(null);
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: DokumenItem) {
        setEditing(item);
        setFile(null);
        setData({
            dosen_id: String(item.dosen_id),
            nama_dokumen: item.nama_dokumen,
            keterangan: item.keterangan || '',
        });
        setShowModal(true);
    }

    function handleFileChange(e: ChangeEvent<HTMLInputElement>) {
        if (e.target.files && e.target.files[0]) {
            setFile(e.target.files[0]);
        }
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('dosen_id', data.dosen_id);
        formData.append('nama_dokumen', data.nama_dokumen);
        formData.append('keterangan', data.keterangan);
        if (file) {
            formData.append('file', file);
        }

        if (editing) {
            formData.append('_method', 'PUT');
            router.post(route('dokumen.update', editing.id), formData, {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); setFile(null); },
            });
        } else {
            router.post(route('dokumen.store'), formData, {
                onSuccess: () => { setShowModal(false); reset(); setFile(null); },
            });
        }
    };

    function confirmDelete(item: DokumenItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('dokumen.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dokumen</h2>}
        >
            <Head title="Dokumen" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Dokumen</span>
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
                                    placeholder="Cari dokumen..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Dokumen
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Dokumen</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipe</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ukuran</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dosen</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {dokumen.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        dokumen.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                    {item.file_path ? (
                                                        <a href={'/storage/' + item.file_path} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-900 underline">
                                                            {item.nama_dokumen}
                                                        </a>
                                                    ) : (
                                                        item.nama_dokumen
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">{fileTypeBadge(item.tipe)}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{formatFileSize(item.file_size)}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.dosen?.nama_depan || '-'}</td>
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

                        {dokumen.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {dokumen.from} - {dokumen.to} dari {dokumen.total}
                                </div>
                                <div className="flex gap-1">
                                    {dokumen.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Dokumen' : 'Tambah Dokumen'}</h3>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nama Dokumen</label>
                                <input type="text" value={data.nama_dokumen} onChange={(e) => setData('nama_dokumen', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.nama_dokumen && <p className="mt-1 text-xs text-red-600">{errors.nama_dokumen}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File</label>
                                <input type="file" onChange={handleFileChange} className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                                {editing && <p className="mt-1 text-xs text-gray-400">Kosongkan jika tidak ingin mengubah file</p>}
                                {errors.file && <p className="mt-1 text-xs text-red-600">{errors.file}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
                                <textarea value={data.keterangan} onChange={(e) => setData('keterangan', e.target.value)} rows={3} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                {errors.keterangan && <p className="mt-1 text-xs text-red-600">{errors.keterangan}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus <strong>{deleteTarget.nama_dokumen}</strong>? File akan dihapus dari penyimpanan.</p>
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
