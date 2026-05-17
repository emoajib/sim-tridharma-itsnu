import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState, FormEventHandler, useRef } from 'react';
import axios from 'axios';

interface Lembaga {
    id: number;
    singkatan: string;
}

interface Instrumen {
    id: number;
    lembaga_id: number;
    nama_instrumen: string;
    matriks_kriteria: any;
    lembaga?: Lembaga;
}

interface Props {
    instrumen: Instrumen[];
    lembaga_list: Lembaga[];
    success?: string;
}

export default function Index({ instrumen, lembaga_list, success }: Props) {
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Instrumen | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Instrumen | null>(null);
    const [isImporting, setIsImporting] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        lembaga_id: '',
        nama_instrumen: '',
        matriks_kriteria: [] as any[],
    });

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: Instrumen) {
        setEditing(item);
        setData({
            lembaga_id: String(item.lembaga_id),
            nama_instrumen: item.nama_instrumen,
            matriks_kriteria: item.matriks_kriteria || [],
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('admin.instrumen.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('admin.instrumen.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('admin.instrumen.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    // Dynamic Criteria Editor Logic
    const addCriteria = () => {
        setData('matriks_kriteria', [...data.matriks_kriteria, { kode: '', nama: '', bobot: 1 }]);
    };

    const removeCriteria = (index: number) => {
        const newList = [...data.matriks_kriteria];
        newList.splice(index, 1);
        setData('matriks_kriteria', newList);
    };

    const updateCriteria = (index: number, field: string, value: any) => {
        const newList = [...data.matriks_kriteria];
        newList[index] = { ...newList[index], [field]: value };
        setData('matriks_kriteria', newList);
    };

    // Excel Import Logic
    const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setIsImporting(true);
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post(route('admin.instrumen.import-preview'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (response.data.success) {
                // Append or replace? Let's ask user or just replace for now as it's cleaner for fresh setup
                setData('matriks_kriteria', response.data.data);
                alert(`Berhasil memuat ${response.data.data.length} kriteria dari Excel. Silakan periksa kembali.`);
            }
        } catch (err: any) {
            alert('Gagal mengimpor file: ' + (err.response?.data?.message || err.message));
        } finally {
            setIsImporting(false);
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Manajemen Instrumen & Kriteria</h2>}
        >
            <Head title="Instrumen Akreditasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 font-bold border border-green-200">
                           ✅ {success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6 flex justify-between items-center">
                            <h3 className="text-lg font-bold text-gray-700">Daftar Instrumen Penilaian</h3>
                            <button
                                onClick={openCreate}
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 shadow-md"
                            >
                                + Tambah Instrumen
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Lembaga</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Nama Instrumen</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Jumlah Kriteria</th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {instrumen.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="px-6 py-12 text-center text-gray-500 italic">Belum ada instrumen terdaftar</td>
                                        </tr>
                                    ) : (
                                        instrumen.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-black text-indigo-600">{item.lembaga?.singkatan}</td>
                                                <td className="px-6 py-4 text-sm text-gray-700 font-bold">{item.nama_instrumen}</td>
                                                <td className="px-6 py-4 text-sm text-gray-600">{(item.matriks_kriteria || []).length} Kriteria</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <button onClick={() => openEdit(item)} className="mr-3 font-bold text-indigo-600 hover:text-indigo-900 underline">Kelola Kriteria</button>
                                                    <button onClick={() => setDeleteTarget(item)} className="font-bold text-red-600 hover:text-red-900">Hapus</button>
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
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-2xl rounded-xl bg-white p-8 shadow-2xl overflow-y-auto max-h-[90vh]">
                        <div className="mb-6 flex items-center justify-between border-b pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">Setup Instrumen & Matriks</h3>
                            <button onClick={() => setShowModal(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Pilih Lembaga</label>
                                    <select value={data.lembaga_id} onChange={(e) => setData('lembaga_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500">
                                        <option value="">-- Pilih --</option>
                                        {lembaga_list.map((l) => (
                                            <option key={l.id} value={l.id}>{l.singkatan}</option>
                                        ))}
                                    </select>
                                    {errors.lembaga_id && <p className="mt-1 text-xs text-red-600 font-bold">{errors.lembaga_id}</p>}
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-black text-gray-500 uppercase">Nama Instrumen</label>
                                    <input type="text" value={data.nama_instrumen} onChange={(e) => setData('nama_instrumen', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-indigo-500" placeholder="Misal: IAPS 4.0" />
                                    {errors.nama_instrumen && <p className="mt-1 text-xs text-red-600 font-bold">{errors.nama_instrumen}</p>}
                                </div>
                            </div>

                            <div className="border-t pt-4">
                                <div className="flex justify-between items-center mb-4">
                                    <h4 className="text-sm font-black text-gray-700 uppercase tracking-widest">Matriks Kriteria Penilaian</h4>
                                    <div className="flex gap-2">
                                        <input 
                                            type="file" 
                                            ref={fileInputRef} 
                                            className="hidden" 
                                            accept=".xlsx,.xls,.csv" 
                                            onChange={handleFileUpload} 
                                        />
                                        <button 
                                            type="button" 
                                            disabled={isImporting}
                                            onClick={() => fileInputRef.current?.click()}
                                            className="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded text-xs font-bold hover:bg-indigo-100 transition-all"
                                        >
                                            {isImporting ? '⏳ Memproses...' : '📂 Import Excel'}
                                        </button>
                                        <button type="button" onClick={addCriteria} className="bg-emerald-500 text-white px-3 py-1 rounded text-xs font-bold hover:bg-emerald-600 transition-all">+ Tambah Manual</button>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    {data.matriks_kriteria.map((crit: any, index: number) => (
                                        <div key={index} className="flex gap-2 items-start bg-gray-50 p-3 rounded-lg border border-gray-200 group">
                                            <div className="w-20">
                                                <input type="text" value={crit.kode} onChange={(e) => updateCriteria(index, 'kode', e.target.value)} placeholder="Kode" className="w-full rounded border-gray-300 text-xs font-bold uppercase" />
                                            </div>
                                            <div className="flex-1">
                                                <input type="text" value={crit.nama} onChange={(e) => updateCriteria(index, 'nama', e.target.value)} placeholder="Nama Kriteria / Aspek" className="w-full rounded border-gray-300 text-xs font-medium" />
                                            </div>
                                            <div className="w-16">
                                                <input type="number" step="0.1" value={crit.bobot} onChange={(e) => updateCriteria(index, 'bobot', e.target.value)} placeholder="Bobot" className="w-full rounded border-gray-300 text-xs font-black text-center" title="Bobot Penilaian" />
                                            </div>
                                            <button type="button" onClick={() => removeCriteria(index)} className="text-rose-500 hover:text-rose-700 p-1">&times;</button>
                                        </div>
                                    ))}
                                    {data.matriks_kriteria.length === 0 && (
                                        <div className="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                                            <p className="text-xs text-gray-400 italic mb-2">Belum ada kriteria. Import dari Excel atau tambah manual.</p>
                                            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Format Excel: kode | nama_kriteria | bobot</p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded-lg border border-gray-300 px-6 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                                <button type="submit" disabled={processing} className="rounded-lg bg-indigo-600 px-8 py-2 text-sm font-black text-white hover:bg-indigo-700 shadow-lg">
                                    {processing ? 'Menyimpan...' : 'SIMPAN INSTRUMEN'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl border-t-4 border-rose-600">
                        <h3 className="mb-2 text-lg font-black text-rose-700">KONFIRMASI HAPUS</h3>
                        <p className="mb-6 text-sm text-gray-700">Hapus instrumen <strong>{deleteTarget.nama_instrumen}</strong>? Data kriteria di dalamnya akan hilang.</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteTarget(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={() => executeDelete()} disabled={processing} className="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">
                                {processing ? 'Menghapus...' : 'YA, HAPUS'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
