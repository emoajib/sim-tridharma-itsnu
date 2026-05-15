import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, Link } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Rps {
    id: number;
    kode_rps: string;
    mata_kuliah_id: number;
    prodi_id: number;
    status: string;
    file: string | null;
    mata_kuliah?: { id: number; kode_mk: string; nama_mk: string };
    prodi?: { id: number; nama_prodi: string };
}

interface MkItem {
    id: number;
    kode_mk: string;
    nama_mk: string;
}

interface Prodi {
    id: number;
    nama_prodi: string;
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
    rps: PaginatedData<Rps>;
    mk_list: MkItem[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    success?: string;
    errors?: Record<string, string>;
}

export default function RpsIndex({ rps, mk_list, prodi_list, periode_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Rps | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Rps | null>(null);

    const { data, setData, post, delete: destroy, errors, processing, reset } = useForm({
        kode_rps: '',
        mata_kuliah_id: '',
        prodi_id: '',
        periode_id: '',
        status: 'draft',
        file: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kurikulum.rps'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setData('status', 'draft');
        setShowModal(true);
    }

    function openEdit(item: Rps) {
        setEditing(item);
        setData({
            kode_rps: item.kode_rps,
            mata_kuliah_id: String(item.mata_kuliah_id),
            prodi_id: String(item.prodi_id),
            periode_id: '',
            status: item.status,
            file: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('kode_rps', data.kode_rps);
        formData.append('mata_kuliah_id', data.mata_kuliah_id);
        formData.append('prodi_id', data.prodi_id);
        formData.append('periode_id', data.periode_id);
        formData.append('status', data.status);
        if (data.file) {
            formData.append('file', data.file);
        }

        if (editing) {
            formData.append('_method', 'PUT');
            router.post(route('kurikulum.rps.update', editing.id), formData, {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            router.post(route('kurikulum.rps.store'), formData, {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Rps) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kurikulum.rps.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">RPS</h2>}
        >
            <Head title="RPS" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="hover:text-indigo-600">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Kurikulum</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">RPS</span>
                    </nav>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex items-center justify-between">
                                <input
                                    type="text"
                                    placeholder="Cari kode RPS atau mata kuliah..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah RPS
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kode RPS</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mata Kuliah</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">File</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {rps.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        rps.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.kode_rps}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.mata_kuliah ? `${item.mata_kuliah.kode_mk} - ${item.mata_kuliah.nama_mk}` : '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.prodi?.nama_prodi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${
                                                        item.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                                    }`}>
                                                        {item.status === 'selesai' ? 'Selesai' : 'Draft'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {item.file ? (
                                                        <a
                                                            href={`/storage/${item.file}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Download
                                                        </a>
                                                    ) : (
                                                        <span className="text-gray-400">-</span>
                                                    )}
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

                        {rps.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {rps.from} - {rps.to} dari {rps.total}
                                </div>
                                <div className="flex gap-1">
                                    {rps.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit RPS' : 'Tambah RPS'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Kode RPS</label>
                                <input
                                    type="text"
                                    value={data.kode_rps}
                                    onChange={(e) => setData('kode_rps', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.kode_rps && <p className="mt-1 text-xs text-red-600">{errors.kode_rps}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Mata Kuliah</label>
                                <select
                                    value={data.mata_kuliah_id}
                                    onChange={(e) => setData('mata_kuliah_id', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Mata Kuliah</option>
                                    {mk_list.map((mk) => (
                                        <option key={mk.id} value={mk.id}>{mk.kode_mk} - {mk.nama_mk}</option>
                                    ))}
                                </select>
                                {errors.mata_kuliah_id && <p className="mt-1 text-xs text-red-600">{errors.mata_kuliah_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Program Studi</label>
                                <select
                                    value={data.prodi_id}
                                    onChange={(e) => setData('prodi_id', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                                {errors.prodi_id && <p className="mt-1 text-xs text-red-600">{errors.prodi_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                <select
                                    value={data.periode_id}
                                    onChange={(e) => setData('periode_id', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                                {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File RPS</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file && <p className="mt-1 text-xs text-red-600">{errors.file}</p>}
                            </div>
                            {editing && (
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="selesai">Selesai</option>
                                    </select>
                                    {errors.status && <p className="mt-1 text-xs text-red-600">{errors.status}</p>}
                                </div>
                            )}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus <strong>{deleteTarget.kode_rps}</strong>?</p>
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
