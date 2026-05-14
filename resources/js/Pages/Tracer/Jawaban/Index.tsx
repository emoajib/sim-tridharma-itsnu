import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface JawabanItem {
    id: number;
    alumni_id: number;
    kuisioner_id: number;
    jawaban: Record<string, unknown>;
    diisi_pada: string | null;
    alumni?: { nama: string; nim: string };
    kuisioner?: { judul_kuisioner: string; tahun: string };
}

interface AlumniOption {
    id: number;
    nama: string;
    nim: string;
}

interface KuisionerOption {
    id: number;
    judul_kuisioner: string;
    tahun: string;
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
    jawaban: PaginatedData<JawabanItem>;
    alumni_list: AlumniOption[];
    kuisioner_list: KuisionerOption[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ jawaban, alumni_list, kuisioner_list, success }: Props) {
    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<JawabanItem | null>(null);

    const { data, setData, post, delete: destroy, errors, processing, reset } = useForm({
        alumni_id: '',
        kuisioner_id: '',
        jawaban: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('tracer.jawaban'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('tracer.jawaban.store'), {
            onSuccess: () => { setShowModal(false); reset(); },
        });
    };

    function confirmDelete(item: JawabanItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('tracer.jawaban.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function truncateJson(json: Record<string, unknown> | string, maxLen = 100): string {
        const str = typeof json === 'string' ? json : JSON.stringify(json);
        return str.length > maxLen ? str.substring(0, maxLen) + '...' : str;
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Jawaban Tracer</h2>}
        >
            <Head title="Jawaban Tracer" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <Link href={route('tracer.kuisioner')} className="text-indigo-600 hover:text-indigo-900">Tracer</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Jawaban</span>
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
                                    placeholder="Cari alumni..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    + Tambah Jawaban
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Alumni</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kuisioner</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jawaban</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal Isi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {jawaban.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        jawaban.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.alumni?.nama || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.kuisioner?.judul_kuisioner || '-'}</td>
                                                <td className="max-w-xs truncate px-6 py-4 text-sm text-gray-700">{truncateJson(item.jawaban)}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{formatDate(item.diisi_pada)}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {jawaban.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {jawaban.from} - {jawaban.to} dari {jawaban.total}
                                </div>
                                <div className="flex gap-1">
                                    {jawaban.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">Tambah Jawaban</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Alumni</label>
                                <select value={data.alumni_id} onChange={(e) => setData('alumni_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Alumni</option>
                                    {alumni_list.map((a) => (
                                        <option key={a.id} value={a.id}>{a.nama} ({a.nim})</option>
                                    ))}
                                </select>
                                {errors.alumni_id && <p className="mt-1 text-xs text-red-600">{errors.alumni_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Kuisioner</label>
                                <select value={data.kuisioner_id} onChange={(e) => setData('kuisioner_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kuisioner</option>
                                    {kuisioner_list.map((k) => (
                                        <option key={k.id} value={k.id}>{k.judul_kuisioner} ({k.tahun})</option>
                                    ))}
                                </select>
                                {errors.kuisioner_id && <p className="mt-1 text-xs text-red-600">{errors.kuisioner_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jawaban (JSON)</label>
                                <textarea
                                    rows={6}
                                    value={data.jawaban}
                                    onChange={(e) => setData('jawaban', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p className="mt-1 text-xs text-gray-500">Format JSON object of answers</p>
                                {errors.jawaban && <p className="mt-1 text-xs text-red-600">{errors.jawaban}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus jawaban dari <strong>{deleteTarget.alumni?.nama || 'alumni ini'}</strong>?</p>
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
