import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface KuisionerItem {
    id: number;
    prodi_id: number;
    judul_kuisioner: string;
    tahun: string;
    pertanyaan: string[];
    is_active: boolean;
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
    kuisioners: PaginatedData<KuisionerItem>;
    prodi_list: Prodi[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ kuisioners, prodi_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<KuisionerItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<KuisionerItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        prodi_id: '',
        judul_kuisioner: '',
        tahun: '',
        pertanyaan: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('tracer.kuisioner'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: KuisionerItem) {
        setEditing(item);
        setData({
            prodi_id: String(item.prodi_id),
            judul_kuisioner: item.judul_kuisioner,
            tahun: item.tahun,
            pertanyaan: JSON.stringify(item.pertanyaan, null, 2),
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('tracer.kuisioner.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('tracer.kuisioner.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: KuisionerItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('tracer.kuisioner.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function getJumlahPertanyaan(pertanyaan: string[]): number {
        return Array.isArray(pertanyaan) ? pertanyaan.length : 0;
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Kuisioner Tracer</h2>}
        >
            <Head title="Kuisioner Tracer" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <Link href={route('tracer.kuisioner')} className="text-indigo-600 hover:text-indigo-900">Tracer</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Kuisioner</span>
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
                                    placeholder="Cari judul kuisioner..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Kuisioner
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Judul Kuisioner</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tahun</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah Pertanyaan</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {kuisioners.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        kuisioners.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.judul_kuisioner}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.prodi?.nama_prodi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tahun}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{getJumlahPertanyaan(item.pertanyaan)}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                        {item.is_active ? 'Aktif' : 'Tidak Aktif'}
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

                        {kuisioners.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {kuisioners.from} - {kuisioners.to} dari {kuisioners.total}
                                </div>
                                <div className="flex gap-1">
                                    {kuisioners.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Kuisioner' : 'Tambah Kuisioner'}</h3>
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
                                <label className="mb-1 block text-sm font-medium text-gray-700">Judul Kuisioner</label>
                                <input
                                    type="text"
                                    value={data.judul_kuisioner}
                                    onChange={(e) => setData('judul_kuisioner', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.judul_kuisioner && <p className="mt-1 text-xs text-red-600">{errors.judul_kuisioner}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                                <input
                                    type="text"
                                    maxLength={4}
                                    value={data.tahun}
                                    onChange={(e) => setData('tahun', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.tahun && <p className="mt-1 text-xs text-red-600">{errors.tahun}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Pertanyaan (JSON)</label>
                                <textarea
                                    rows={6}
                                    value={data.pertanyaan}
                                    onChange={(e) => setData('pertanyaan', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p className="mt-1 text-xs text-gray-500">Format JSON array of questions, e.g. ["Pertanyaan 1?", "Pertanyaan 2?"]</p>
                                {errors.pertanyaan && <p className="mt-1 text-xs text-red-600">{errors.pertanyaan}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus kuisioner <strong>{deleteTarget.judul_kuisioner}</strong>?</p>
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
