import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
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

interface Publikasi {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    judul_publikasi: string;
    jenis_publikasi: string;
    tingkat: string;
    link: string | null;
    tahun: number;
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
    publikasi: PaginatedData<Publikasi>;
    dosen_list: Dosen[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ publikasi, dosen_list, prodi_list, periode_list, success }: Props) {
    const { props } = usePage();
    const flashError = (props as any).flash?.error;
    const flashSuccess = success || (props as any).flash?.success;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Publikasi | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Publikasi | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        dosen_id: '',
        prodi_id: '',
        periode_id: '',
        judul_publikasi: '',
        jenis_publikasi: '',
        tingkat: '',
        link: '',
        tahun: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('portofolio.publikasi'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Publikasi) {
        setEditing(item);
        setData({
            dosen_id: String(item.dosen_id),
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            judul_publikasi: item.judul_publikasi,
            jenis_publikasi: item.jenis_publikasi,
            tingkat: item.tingkat,
            link: item.link || '',
            tahun: String(item.tahun),
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('portofolio.publikasi.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('portofolio.publikasi.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Publikasi) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('portofolio.publikasi.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const { data: importData, setData: setImportData, post: postImport, processing: importProcessing, reset: resetImport, errors: importErrors } = useForm({
        file: null as File | null,
    });

    const handleImport: FormEventHandler = (e) => {
        e.preventDefault();
        postImport(route('import.sinta.publikasi'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { resetImport(); },
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Publikasi</h2>}
        >
            <Head title="Publikasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <Link href={route('portofolio')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Portofolio</Link>
                        
                        {/* Import Section */}
                        <form onSubmit={handleImport} className="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 p-2 shadow-sm">
                            <div className="flex flex-col">
                                <span className="text-[10px] font-black text-rose-700 uppercase px-1">Import SINTA (XLS/CSV):</span>
                                <div className="flex items-center gap-2">
                                    <input 
                                        type="file" 
                                        accept=".xlsx,.xls,.csv"
                                        onChange={e => setImportData('file', e.target.files?.[0] || null)}
                                        className="text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-rose-600 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-rose-700 cursor-pointer"
                                    />
                                    <button 
                                        type="submit" 
                                        disabled={importProcessing || !importData.file}
                                        className="rounded-md bg-rose-600 px-3 py-1 text-xs font-bold text-white hover:bg-rose-700 disabled:opacity-50 transition-all shadow-sm"
                                    >
                                        {importProcessing ? 'Syncing...' : 'Sinkronkan'}
                                    </button>
                                </div>
                                {importErrors.file && <p className="text-[10px] text-red-600 px-1 font-bold">{importErrors.file}</p>}
                            </div>
                        </form>
                    </div>

                    {flashSuccess && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 font-bold border border-green-200 shadow-sm animate-pulse">
                            ✅ {flashSuccess}
                        </div>
                    )}
                    
                    {flashError && (
                        <div className="mb-4 rounded-lg bg-rose-100 p-4 text-sm text-rose-700 font-bold border border-rose-200 shadow-sm">
                            ❌ {flashError}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex items-center justify-between">
                                <input
                                    type="text"
                                    placeholder="Cari publikasi..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    onClick={openCreate}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 shadow-sm"
                                >
                                    + Tambah Publikasi
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dosen</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Judul</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tingkat</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tahun</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {publikasi.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        publikasi.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900 font-medium">{item.dosen?.nama_depan || '-'}</td>
                                                <td className="px-6 py-4 text-sm text-gray-700 leading-relaxed">{item.judul_publikasi}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_publikasi}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700 uppercase font-bold">{item.tingkat}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.tahun}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold leading-5 ${item.is_verified ? 'bg-green-100 text-emerald-800' : 'bg-yellow-100 text-amber-800'}`}>
                                                        {item.is_verified ? 'Terverifikasi' : 'Pending AI'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-bold">
                                                    <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination Footer */}
                        {publikasi.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {publikasi.from} - {publikasi.to} dari {publikasi.total}
                                </div>
                                <div className="flex gap-1">
                                    {publikasi.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm font-bold ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal & Delete Confirmations remains same as previous but with small styling improvements */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between border-b pb-3">
                            <h3 className="text-lg font-bold text-gray-900">{editing ? 'Edit Publikasi' : 'Tambah Publikasi'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="grid grid-cols-1 gap-4">
                                <div>
                                    <label className="mb-1 block text-sm font-bold text-gray-700">Dosen</label>
                                    <select value={data.dosen_id} onChange={(e) => setData('dosen_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                                        <option value="">Pilih Dosen</option>
                                        {dosen_list.map((d) => (
                                            <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
                                        ))}
                                    </select>
                                    {errors.dosen_id && <p className="mt-1 text-xs text-red-600 font-bold">{errors.dosen_id}</p>}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-bold text-gray-700">Prodi</label>
                                    <select value={data.prodi_id} onChange={(e) => setData('prodi_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                                        <option value="">Pilih Prodi</option>
                                        {prodi_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                        ))}
                                    </select>
                                    {errors.prodi_id && <p className="mt-1 text-xs text-red-600 font-bold">{errors.prodi_id}</p>}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-bold text-gray-700">Judul Publikasi</label>
                                    <textarea rows={2} value={data.judul_publikasi} onChange={(e) => setData('judul_publikasi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500"></textarea>
                                    {errors.judul_publikasi && <p className="mt-1 text-xs text-red-600 font-bold">{errors.judul_publikasi}</p>}
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="mb-1 block text-sm font-bold text-gray-700">Jenis</label>
                                        <select value={data.jenis_publikasi} onChange={(e) => setData('jenis_publikasi', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                                            <option value="">Pilih Jenis</option>
                                            <option value="jurnal">Jurnal</option>
                                            <option value="prosiding">Prosiding</option>
                                            <option value="buku">Buku</option>
                                            <option value="HKI">HKI</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-bold text-gray-700">Tingkat</label>
                                        <select value={data.tingkat} onChange={(e) => setData('tingkat', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                                            <option value="">Pilih Tingkat</option>
                                            <option value="nasional">Nasional</option>
                                            <option value="internasional">Internasional</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div className="mt-6 flex justify-end gap-2 border-t pt-4">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50 shadow-md">
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl border-2 border-rose-500">
                        <h3 className="mb-2 text-lg font-black text-rose-600">KONFIRMASI HAPUS</h3>
                        <p className="mb-6 text-sm text-gray-700 font-medium">Yakin ingin menghapus publikasi:<br/><span className="italic font-bold">"{deleteTarget.judul_publikasi}"</span>?</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700 shadow-md">
                                {processing ? 'Menghapus...' : 'Ya, Hapus'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
