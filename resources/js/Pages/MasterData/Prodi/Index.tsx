import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface Prodi {
    id: number;
    kode_prodi: string;
    nama_prodi: string;
    fakultas_id: number;
    lembaga_akreditasi_id: number | null;
    jenjang: string;
    akreditasi: string | null;
    sk_akreditasi: string | null;
    is_active: boolean;
    fakultas?: { id: number; nama_fakultas: string };
    lembaga?: { id: number; nama_lembaga: string; singkatan: string };
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
    prodi: PaginatedData<Prodi>;
    fakultas_list: { id: number; nama_fakultas: string }[];
    lembaga_list: Lembaga[];
    success?: string;
    errors?: Record<string, string>;
}

export default function Index({ prodi, fakultas_list, lembaga_list, success }: Props) {
    const { props } = usePage();
    const flashSuccess = success || (props as any).flash?.success;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Prodi | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Prodi | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        kode_prodi: '',
        nama_prodi: '',
        fakultas_id: '',
        lembaga_akreditasi_id: '',
        jenjang: '',
        akreditasi: '',
        sk_akreditasi: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('master-data.prodi'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Prodi) {
        setEditing(item);
        setData({
            kode_prodi: item.kode_prodi,
            nama_prodi: item.nama_prodi,
            fakultas_id: String(item.fakultas_id),
            lembaga_akreditasi_id: item.lembaga_akreditasi_id ? String(item.lembaga_akreditasi_id) : '',
            jenjang: item.jenjang,
            akreditasi: item.akreditasi || '',
            sk_akreditasi: item.sk_akreditasi || '',
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('master-data.prodi.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('master-data.prodi.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: Prodi) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('master-data.prodi.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Manajemen & Ploting Program Studi</h2>}
        >
            <Head title="Manajemen Prodi" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flashSuccess && (
                        <div className="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 font-black border border-emerald-100 shadow-sm animate-in fade-in slide-in-from-top-1">
                           ✅ {flashSuccess}
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
                                        placeholder="Cari prodi (Kode/Nama)..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-full md:w-80 rounded-xl border-gray-200 pl-10 text-sm font-medium shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all"
                                    />
                                </div>
                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95 uppercase tracking-widest"
                                >
                                    <span>+</span>
                                    <span>TAMBAH & PLOTING PRODI</span>
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr className="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                        <th className="px-6 py-4 text-left">Kode</th>
                                        <th className="px-6 py-4 text-left">Program Studi</th>
                                        <th className="px-6 py-4 text-left">Lembaga Akreditasi (Ploting)</th>
                                        <th className="px-6 py-4 text-center">Status Akreditasi</th>
                                        <th className="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50 bg-white">
                                    {prodi.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-16 text-center text-gray-400 italic font-medium">Tidak ada data program studi ditemukan.</td>
                                        </tr>
                                    ) : (
                                        prodi.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-indigo-50/30 transition-all group">
                                                <td className="whitespace-nowrap px-6 py-5 text-sm font-black text-indigo-600 bg-indigo-50/10">{item.kode_prodi}</td>
                                                <td className="px-6 py-5">
                                                    <div className="font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{item.nama_prodi}</div>
                                                    <div className="text-[10px] text-gray-400 font-bold uppercase tracking-tight mt-0.5">{item.jenjang} • {item.fakultas?.nama_fakultas}</div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5">
                                                    <span className={`inline-flex px-3 py-1 rounded-full text-[10px] font-black border transition-all ${
                                                        item.lembaga ? 'bg-indigo-50 text-indigo-700 border-indigo-100 shadow-sm' : 'bg-rose-50 text-rose-700 border-rose-100'
                                                    }`}>
                                                        {item.lembaga?.singkatan || 'BELUM DI-PLOT'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center">
                                                    <span className="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">{item.akreditasi || 'N/A'}</span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-right">
                                                    <div className="flex justify-end gap-3">
                                                        <button onClick={() => openEdit(item)} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4">Edit / Plotting</button>
                                                        <button onClick={() => confirmDelete(item)} className="text-[10px] font-black text-rose-600 hover:text-rose-800 uppercase tracking-widest">Hapus</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {prodi.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-100 bg-gray-50/30 px-6 py-4">
                                <div className="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    Menampilkan {prodi.from} - {prodi.to} dari {prodi.total} Data
                                </div>
                                <div className="flex gap-1">
                                    {prodi.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded-lg px-3 py-1.5 text-xs font-black transition-all ${link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200'} ${!link.url ? 'cursor-not-allowed opacity-30' : ''}`}
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
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl border border-white/20">
                        <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">{editing ? 'Edit & Plotting Prodi' : 'Tambah Prodi Baru'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-5">
                            <div className="grid grid-cols-3 gap-4">
                                <div className="col-span-1">
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Kode Prodi</label>
                                    <input type="text" value={data.kode_prodi} onChange={(e) => setData('kode_prodi', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500 uppercase tracking-tighter" />
                                </div>
                                <div className="col-span-2">
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Nama Program Studi</label>
                                    <input type="text" value={data.nama_prodi} onChange={(e) => setData('nama_prodi', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div className="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                                <label className="mb-2 block text-[10px] font-black text-indigo-700 uppercase tracking-widest underline decoration-indigo-200 underline-offset-4">Lembaga Akreditasi (PLOTING)</label>
                                <select value={data.lembaga_akreditasi_id} onChange={(e) => setData('lembaga_akreditasi_id', e.target.value)} className="w-full rounded-xl border-indigo-200 bg-white text-sm font-black text-indigo-900 focus:ring-indigo-500">
                                    <option value="">-- PILIH LEMBAGA PENILAI --</option>
                                    {lembaga_list.map((l) => (
                                        <option key={l.id} value={l.id}>{l.singkatan} - {l.nama_lembaga}</option>
                                    ))}
                                </select>
                                <p className="mt-2 text-[10px] text-indigo-500 italic font-medium">* Prodi ini akan muncul di dashboard filter lembaga yang dipilih.</p>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Fakultas</label>
                                    <select value={data.fakultas_id} onChange={(e) => setData('fakultas_id', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Fakultas</option>
                                        {fakultas_list.map((f) => (
                                            <option key={f.id} value={f.id}>{f.nama_fakultas}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Jenjang</label>
                                    <select value={data.jenjang} onChange={(e) => setData('jenjang', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">Pilih Jenjang</option>
                                        <option value="S1">S1</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="S2">S2</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Akreditasi Saat Ini</label>
                                    <input type="text" value={data.akreditasi} onChange={(e) => setData('akreditasi', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" placeholder="Misal: Baik Sekali" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Nomor SK</label>
                                    <input type="text" value={data.sk_akreditasi} onChange={(e) => setData('sk_akreditasi', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest transition-all">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-xl bg-indigo-600 px-8 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-widest">
                                    {processing ? 'Menyimpan...' : 'SIMPAN DATA & PLOTING'}
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
                        <p className="mb-8 text-sm text-gray-600 font-medium leading-relaxed">Menghapus prodi <strong>{deleteTarget.nama_prodi}</strong> akan berdampak pada seluruh data portofolio terkait. Lanjutkan?</p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest transition-all">Batal</button>
                            <button onClick={executeDelete} disabled={processing} className="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-black text-white hover:bg-rose-700 shadow-xl shadow-rose-100 uppercase tracking-widest transition-all active:scale-95">
                                {processing ? 'Menghapus...' : 'YA, HAPUS PERMANEN'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
