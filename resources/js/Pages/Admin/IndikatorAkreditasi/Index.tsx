import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, Link } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';

interface Instrumen {
    id: number;
    nama_instrumen: string;
    lembaga?: { singkatan: string };
}

interface Indikator {
    id: number;
    instrumen_id: number;
    kode_indikator: string;
    nama_indikator: string;
    kriteria: string;
    bobot: number;
    target: string | null;
    jenis_akreditasi: string;
    instrumen?: Instrumen;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    indikator: PaginatedData<Indikator>;
    instrumen_list: Instrumen[];
    success?: string;
}

export default function Index({ indikator, instrumen_list, success }: Props) {
    const [search, setSearch] = useState('');
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Indikator | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Indikator | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        instrumen_id: '',
        kode_indikator: '',
        nama_indikator: '',
        kriteria: '',
        bobot: '',
        target: '',
        jenis_akreditasi: 'IAPS 4.0',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('admin.indikator.index'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('admin.indikator.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('admin.indikator.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Manajemen Indikator Penilaian</h2>}
        >
            <Head title="Indikator Akreditasi" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 font-black border border-emerald-100 shadow-sm animate-in fade-in">
                           ✅ {success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm border border-gray-100 rounded-2xl">
                        <div className="border-b border-gray-100 bg-gray-50/30 p-6">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <input
                                    type="text"
                                    placeholder="Cari indikator..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full md:w-80 rounded-xl border-gray-200 text-sm font-medium shadow-sm focus:ring-indigo-500"
                                />
                                <button
                                    onClick={() => { reset(); setEditing(null); setShowModal(true); }}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-lg uppercase tracking-widest"
                                >
                                    + TAMBAH INDIKATOR
                                </button>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr className="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                        <th className="px-6 py-4 text-left">Kode</th>
                                        <th className="px-6 py-4 text-left">Indikator / Butir Penilaian</th>
                                        <th className="px-6 py-4 text-left">Instrumen</th>
                                        <th className="px-6 py-4 text-center">Bobot</th>
                                        <th className="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50 bg-white">
                                    {indikator.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-16 text-center text-gray-400 italic font-medium">Belum ada indikator ditemukan.</td>
                                        </tr>
                                    ) : (
                                        indikator.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-indigo-50/30 transition-all group">
                                                <td className="whitespace-nowrap px-6 py-5 text-sm font-black text-indigo-600">{item.kode_indikator}</td>
                                                <td className="px-6 py-5">
                                                    <div className="font-bold text-gray-800 leading-relaxed">{item.nama_indikator}</div>
                                                    <div className="text-[10px] text-gray-400 font-bold uppercase tracking-tight mt-1">Kriteria: {item.kriteria}</div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5">
                                                    <span className="text-[10px] font-black text-gray-600 bg-gray-100 px-2 py-0.5 rounded uppercase">
                                                        {item.instrumen?.lembaga?.singkatan} • {item.instrumen?.nama_instrumen}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-5 text-center font-black text-indigo-600">{item.bobot}</td>
                                                <td className="whitespace-nowrap px-6 py-5 text-right">
                                                    <div className="flex justify-end gap-3">
                                                        <button onClick={() => {
                                                            setEditing(item);
                                                            setData({
                                                                instrumen_id: String(item.instrumen_id),
                                                                kode_indikator: item.kode_indikator,
                                                                nama_indikator: item.nama_indikator,
                                                                kriteria: item.kriteria,
                                                                bobot: String(item.bobot),
                                                                target: item.target || '',
                                                                jenis_akreditasi: item.jenis_akreditasi,
                                                            });
                                                            setShowModal(true);
                                                        }} className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline underline-offset-4">Edit</button>
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
                    <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">{editing ? 'Edit Indikator' : 'Tambah Indikator Baru'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Pilih Instrumen</label>
                                <select value={data.instrumen_id} onChange={(e) => setData('instrumen_id', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500">
                                    <option value="">-- Pilih Instrumen --</option>
                                    {instrumen_list.map((i) => (
                                        <option key={i.id} value={i.id}>{i.lembaga?.singkatan} - {i.nama_instrumen}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Kode Indikator</label>
                                    <input type="text" value={data.kode_indikator} onChange={(e) => setData('kode_indikator', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500 uppercase" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Kriteria (misal: C1)</label>
                                    <input type="text" value={data.kriteria} onChange={(e) => setData('kriteria', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500 uppercase" />
                                </div>
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Nama / Deskripsi Indikator</label>
                                <textarea rows={3} value={data.nama_indikator} onChange={(e) => setData('nama_indikator', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Bobot Skor</label>
                                    <input type="number" step="0.01" value={data.bobot} onChange={(e) => setData('bobot', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-black focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Jenis Akreditasi</label>
                                    <input type="text" value={data.jenis_akreditasi} onChange={(e) => setData('jenis_akreditasi', e.target.value)} className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-xl bg-indigo-600 px-8 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 uppercase tracking-widest">
                                    {processing ? 'Menyimpan...' : 'SIMPAN INDIKATOR'}
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
                        <p className="mb-8 text-sm text-gray-600 font-medium leading-relaxed">Hapus indikator <strong>{deleteTarget.kode_indikator}</strong>? Lanjutkan?</p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest">Batal</button>
                            <button onClick={() => {
                                destroy(route('admin.indikator.destroy', deleteTarget.id), {
                                    onSuccess: () => setDeleteTarget(null),
                                });
                            }} disabled={processing} className="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-black text-white hover:bg-rose-700 uppercase tracking-widest">
                                {processing ? 'Menghapus...' : 'YA, HAPUS'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
