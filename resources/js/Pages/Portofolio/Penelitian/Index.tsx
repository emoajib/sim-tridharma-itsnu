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

interface Penelitian {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    judul_penelitian: string;
    jenis_penelitian: string;
    sumber_dana: string;
    jumlah_dana: number | null;
    tahun_pelaksanaan: number;
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
    penelitian: PaginatedData<Penelitian>;
    dosen_list: Dosen[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    success?: string;
}

export default function Index({ penelitian, dosen_list, prodi_list, periode_list, success }: Props) {
    const { props } = usePage();
    const flashSuccess = success || (props as any).flash?.success;
    const flashError = (props as any).flash?.error;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Penelitian | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Penelitian | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        dosen_id: '',
        prodi_id: '',
        periode_id: '',
        judul_penelitian: '',
        jenis_penelitian: '',
        sumber_dana: '',
        jumlah_dana: '',
        tahun_pelaksanaan: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('portofolio.penelitian'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('portofolio.penelitian.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('portofolio.penelitian.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    const { data: importData, setData: setImportData, post: postImport, processing: importProcessing, reset: resetImport, errors: importErrors } = useForm({
        file: null as File | null,
    });

    const handleImport: FormEventHandler = (e) => {
        e.preventDefault();
        postImport(route('import.sinta.penelitian'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { resetImport(); },
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Database Penelitian Dosen</h2>}
        >
            <Head title="Penelitian" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <Link href={route('portofolio')} className="text-xs font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest flex items-center gap-2">
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            KEMBALI KE PORTOFOLIO
                        </Link>
                        
                        <form onSubmit={handleImport} className="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 shadow-sm">
                            <div className="flex flex-col">
                                <span className="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-1 px-1">Import SINTA Penelitian</span>
                                <input 
                                    type="file" 
                                    accept=".xlsx,.xls,.csv"
                                    onChange={e => setImportData('file', e.target.files?.[0] || null)}
                                    className="text-[10px] text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-[10px] file:font-black file:text-white hover:file:bg-emerald-700 cursor-pointer transition-all"
                                />
                            </div>
                            <button 
                                type="submit" 
                                disabled={importProcessing || !importData.file}
                                className="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-xs font-black text-white hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-widest"
                            >
                                {importProcessing ? '⏳ SYNCING...' : '🚀 SINKRONKAN'}
                            </button>
                        </form>
                    </div>

                    {flashSuccess && (
                        <div className="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 font-black border border-emerald-100 shadow-sm animate-in fade-in">
                           ✅ {flashSuccess}
                        </div>
                    )}
                    {flashError && (
                        <div className="mb-6 rounded-xl bg-rose-50 p-4 text-sm text-rose-700 font-black border border-rose-100 shadow-sm animate-in fade-in">
                           ❌ {flashError}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm border border-gray-100 rounded-2xl">
                        <div className="border-b border-gray-100 bg-gray-50/30 p-6">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div className="relative">
                                    <span className="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg className="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </span>
                                    <input
                                        type="text"
                                        placeholder="Cari judul penelitian..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-full md:w-80 rounded-xl border-gray-200 pl-10 text-sm font-medium shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    />
                                </div>
                                <button
                                    onClick={() => { reset(); setEditing(null); setShowModal(true); }}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-lg transition-all active:scale-95 uppercase tracking-widest"
                                >
                                    <span>+</span>
                                    <span>TAMBAH PENELITIAN MANUAL</span>
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr className="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                        <th className="px-6 py-4 text-left">Dosen Utama</th>
                                        <th className="px-6 py-4 text-left">Judul Penelitian</th>
                                        <th className="px-6 py-4 text-center">Jenis</th>
                                        <th className="px-6 py-4 text-center">Sumber Dana</th>
                                        <th className="px-6 py-4 text-center">Tahun</th>
                                        <th className="px-6 py-4 text-center">Status</th>
                                        <th className="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50 bg-white">
                                    {penelitian.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-16 text-center text-gray-400 italic font-medium">Tidak ada data penelitian ditemukan.</td>
                                        </tr>
                                    ) : (
                                        penelitian.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-indigo-50/30 transition-all group">
                                                <td className="whitespace-nowrap px-6 py-5 text-sm font-black text-gray-900">{item.dosen?.nama_depan} {item.dosen?.nama_belakang}</td>
                                                <td className="px-6 py-5">
                                                    <div className="font-bold text-gray-800 group-hover:text-indigo-600 transition-colors leading-relaxed">{item.judul_penelitian}</div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center">
                                                    <span className="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded uppercase">{item.jenis_penelitian}</span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center text-[10px] font-black text-gray-500 uppercase tracking-widest">{item.sumber_dana || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center font-black text-gray-700">{item.tahun_pelaksanaan}</td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center">
                                                    <span className={`inline-flex px-3 py-1 rounded-full text-[10px] font-black border transition-all ${
                                                        item.is_verified ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'
                                                    }`}>
                                                        {item.is_verified ? 'VERIFIED' : 'PENDING'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-right">
                                                    <div className="flex justify-end gap-3">
                                                        <button onClick={() => {
                                                            setEditing(item);
                                                            setData({
                                                                dosen_id: String(item.dosen_id),
                                                                prodi_id: String(item.prodi_id),
                                                                periode_id: String(item.periode_id),
                                                                judul_penelitian: item.judul_penelitian,
                                                                jenis_penelitian: item.jenis_penelitian,
                                                                sumber_dana: item.sumber_dana,
                                                                jumlah_dana: String(item.jumlah_dana || ''),
                                                                tahun_pelaksanaan: String(item.tahun_pelaksanaan),
                                                            });
                                                            setShowModal(true);
                                                        }} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4">Edit</button>
                                                        <button onClick={() => setDeleteTarget(item)} className="text-[10px] font-black text-rose-600 hover:text-rose-800 uppercase tracking-widest">Hapus</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl border border-white/20">
                        <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">{editing ? 'Edit Penelitian' : 'Tambah Penelitian Manual'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Dosen Pengusul</label>
                                <select value={data.dosen_id} onChange={(e) => setData('dosen_id', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500">
                                    <option value="">Pilih Dosen</option>
                                    {dosen_list.map((d) => (
                                        <option key={d.id} value={d.id}>{d.nama_depan} {d.nama_belakang}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Prodi</label>
                                    <select value={data.prodi_id} onChange={(e) => setData('prodi_id', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Prodi</option>
                                        {prodi_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Tahun</label>
                                    <input type="number" value={data.tahun_pelaksanaan} onChange={(e) => setData('tahun_pelaksanaan', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500" />
                                </div>
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Judul Penelitian</label>
                                <textarea rows={2} value={data.judul_penelitian} onChange={(e) => setData('judul_penelitian', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Skema / Jenis</label>
                                    <select value={data.jenis_penelitian} onChange={(e) => setData('jenis_penelitian', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Jenis</option>
                                        <option value="mandiri">Mandiri</option>
                                        <option value="kelompok">Kelompok</option>
                                        <option value="hibah_pt">Hibah Institusi</option>
                                        <option value="hibah_dikti">Hibah Kemendikbud</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Sumber Dana</label>
                                    <input type="text" value={data.sumber_dana} onChange={(e) => setData('sumber_dana', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest transition-all">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-xl bg-indigo-600 px-8 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-xl transition-all active:scale-95 disabled:opacity-50 uppercase tracking-widest">
                                    {processing ? 'Menyimpan...' : 'SIMPAN DATA'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 animate-in zoom-in-95 duration-200">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl border-t-8 border-rose-600">
                        <h3 className="mb-2 text-xl font-black text-rose-700 uppercase tracking-tighter">KONFIRMASI HAPUS</h3>
                        <p className="mb-8 text-sm text-gray-600 font-medium leading-relaxed">Hapus data penelitian ini? Lanjutkan?</p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest transition-all">Batal</button>
                            <button onClick={() => {
                                destroy(route('portofolio.penelitian.destroy', deleteTarget.id), {
                                    onSuccess: () => setDeleteTarget(null),
                                });
                            }} disabled={processing} className="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-black text-white hover:bg-rose-700 shadow-xl uppercase tracking-widest transition-all active:scale-95">
                                {processing ? 'Menghapus...' : 'YA, HAPUS PERMANEN'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
