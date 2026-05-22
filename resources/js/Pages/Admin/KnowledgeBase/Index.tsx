import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

interface Category {
    id: number;
    nama: string;
    singkatan: string;
}

interface Document {
    id: number;
    judul: string;
    sumber: string | null;
    file_path: string;
    file_size: number;
    page_count: number | null;
    status: string;
    category: Category | null;
    category_id?: number | null;
    created_at: string;
    chunks_count?: number;
}

interface Chunk {
    id: number;
    chunk_index: number;
    content: string;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    documents: PaginatedData<Document>;
    categories: Category[];
}

export default function Index({ documents, categories }: Props) {
    const { props } = usePage();
    const flash = (props as any).flash || {};
    
    // Modal states
    const [showUpload, setShowUpload] = useState(false);
    const [showEdit, setShowEdit] = useState(false);
    const [showManageChunks, setShowManageChunks] = useState(false);
    const [selectedDoc, setSelectedDoc] = useState<Document | null>(null);
    
    // Chunks states
    const [chunks, setChunks] = useState<Chunk[]>([]);
    const [loadingChunks, setLoadingChunks] = useState(false);
    const [editingChunkId, setEditingChunkId] = useState<number | null>(null);
    const [chunkContent, setChunkContent] = useState('');
    const [savingChunk, setSavingChunk] = useState(false);
    
    const [uploading, setUploading] = useState(false);
    const [judul, setJudul] = useState('');
    const [sumber, setSumber] = useState('');
    const [categoryId, setCategoryId] = useState('');
    const [file, setFile] = useState<File | null>(null);

    function handleUpload(e: React.FormEvent) {
        e.preventDefault();
        if (!file || !judul) return;

        setUploading(true);
        const form = new FormData();
        form.append('file', file);
        form.append('judul', judul);
        form.append('sumber', sumber);
        if (categoryId) form.append('category_id', categoryId);

        router.post(route('admin.knowledge-base.upload'), form, {
            onFinish: () => {
                setUploading(false);
                setShowUpload(false);
                resetForm();
            },
        });
    }

    function openEdit(doc: Document) {
        setSelectedDoc(doc);
        setJudul(doc.judul);
        setSumber(doc.sumber || '');
        setCategoryId(doc.category_id?.toString() || doc.category?.id?.toString() || '');
        setShowEdit(true);
    }

    function handleUpdate(e: React.FormEvent) {
        e.preventDefault();
        if (!selectedDoc || !judul) return;

        setUploading(true);
        router.put(route('admin.knowledge-base.update', selectedDoc.id), {
            judul,
            sumber,
            category_id: categoryId,
        }, {
            onFinish: () => {
                setUploading(false);
                setShowEdit(false);
                resetForm();
            },
        });
    }

    async function openManageChunks(doc: Document) {
        setSelectedDoc(doc);
        setShowManageChunks(true);
        setLoadingChunks(true);
        try {
            const response = await axios.get(route('admin.knowledge-base.chunks', doc.id));
            setChunks(response.data.chunks);
        } catch (error) {
            console.error('Gagal mengambil chunks:', error);
            alert('Gagal mengambil data teks.');
        } finally {
            setLoadingChunks(false);
        }
    }

    async function handleUpdateChunk(chunkId: number) {
        setSavingChunk(true);
        try {
            await axios.put(route('admin.knowledge-base.chunks.update', chunkId), {
                content: chunkContent
            });
            setChunks(prev => prev.map(c => c.id === chunkId ? { ...c, content: chunkContent } : c));
            setEditingChunkId(null);
        } catch (error) {
            console.error('Gagal update chunk:', error);
            alert('Gagal menyimpan perubahan teks.');
        } finally {
            setSavingChunk(false);
        }
    }

    function resetForm() {
        setJudul('');
        setSumber('');
        setCategoryId('');
        setFile(null);
        setSelectedDoc(null);
    }

    function handleReindex(doc: Document) {
        router.post(route('admin.knowledge-base.reindex', doc.id));
    }

    function handleDelete(doc: Document) {
        if (confirm(`Hapus dokumen "${doc.judul}"?`)) {
            router.delete(route('admin.knowledge-base.destroy', doc.id));
        }
    }

    function formatSize(bytes: number): string {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    const statusBadge = (status: string) => {
        const styles: Record<string, string> = {
            active: 'bg-emerald-100 text-emerald-700',
            draft: 'bg-yellow-100 text-yellow-700',
            error: 'bg-rose-100 text-rose-700',
        };
        return `px-2 py-0.5 rounded-full text-[10px] font-bold ${styles[status] || 'bg-gray-100 text-gray-600'}`;
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black leading-tight text-gray-800 uppercase tracking-tighter">Knowledge Base</h2>}
        >
            <Head title="Knowledge Base" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 font-black border border-emerald-100">{flash.success}</div>
                    )}
                    {flash.warning && (
                        <div className="mb-6 rounded-xl bg-yellow-50 p-4 text-sm text-yellow-700 font-black border border-yellow-100">{flash.warning}</div>
                    )}
                    {flash.error && (
                        <div className="mb-6 rounded-xl bg-rose-50 p-4 text-sm text-rose-700 font-black border border-rose-100">{flash.error}</div>
                    )}

                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <p className="text-xs text-gray-500 font-medium">Total: {documents.total} dokumen</p>
                        </div>
                        <button
                            onClick={() => {
                                resetForm();
                                setShowUpload(true);
                            }}
                            className="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-lg uppercase tracking-widest"
                        >
                            + Upload PDF
                        </button>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm border border-gray-100 rounded-2xl">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr className="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                        <th className="px-6 py-4 text-left">Judul Dokumen</th>
                                        <th className="px-6 py-4 text-left">Kategori</th>
                                        <th className="px-6 py-4 text-center">Ukuran</th>
                                        <th className="px-6 py-4 text-center">Halaman</th>
                                        <th className="px-6 py-4 text-center">Status</th>
                                        <th className="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50">
                                    {documents.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-16 text-center text-gray-400 italic font-medium">
                                                Belum ada dokumen. Upload PDF kebijakan akreditasi untuk memulai.
                                            </td>
                                        </tr>
                                    ) : (
                                        documents.data.map((doc) => (
                                            <tr key={doc.id} className="hover:bg-indigo-50/30 transition-all">
                                                <td className="px-6 py-5">
                                                    <div className="font-bold text-gray-800">{doc.judul}</div>
                                                    {doc.sumber && (
                                                        <div className="text-[10px] text-gray-400 font-medium mt-0.5">{doc.sumber}</div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-5">
                                                    {doc.category && (
                                                        <span className="text-[10px] font-black text-gray-600 bg-gray-100 px-2 py-0.5 rounded uppercase">
                                                            {doc.category.singkatan}
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-5 text-center text-sm font-bold text-gray-600 tabular-nums">
                                                    {formatSize(doc.file_size)}
                                                </td>
                                                <td className="px-6 py-5 text-center text-sm font-bold text-gray-600">
                                                    {doc.page_count ?? '-'}
                                                </td>
                                                <td className="px-6 py-5 text-center">
                                                    <span className={statusBadge(doc.status)}>
                                                        {doc.status.toUpperCase()}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-5 text-right">
                                                    <div className="flex justify-end items-center gap-3">
                                                        <button
                                                            onClick={() => openEdit(doc)}
                                                            className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline underline-offset-4"
                                                        >
                                                            Edit
                                                        </button>
                                                        <button
                                                            onClick={() => openManageChunks(doc)}
                                                            className="text-[10px] font-black text-amber-600 hover:text-amber-800 uppercase tracking-widest underline underline-offset-4"
                                                        >
                                                            Kelola Teks
                                                        </button>
                                                        <button
                                                            onClick={() => handleReindex(doc)}
                                                            className="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest underline underline-offset-4"
                                                        >
                                                            Re-index
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(doc)}
                                                            className="text-[10px] font-black text-rose-600 hover:text-rose-800 uppercase tracking-widest"
                                                        >
                                                            Hapus
                                                        </button>
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

            {/* Modal Upload */}
            {showUpload && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">Upload Dokumen PDF</h3>
                            <button onClick={() => setShowUpload(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={handleUpload} className="space-y-4">
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">File PDF</label>
                                <input
                                    type="file"
                                    accept=".pdf"
                                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-xs file:font-black file:text-indigo-600 hover:file:bg-indigo-100"
                                    required
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Judul Dokumen</label>
                                <input
                                    type="text"
                                    value={judul}
                                    onChange={(e) => setJudul(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                    placeholder="Contoh: Instrumen IAPS 4.0"
                                    required
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Sumber / Penerbit</label>
                                <input
                                    type="text"
                                    value={sumber}
                                    onChange={(e) => setSumber(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                    placeholder="Contoh: BAN-PT"
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Kategori</label>
                                <select
                                    value={categoryId}
                                    onChange={(e) => setCategoryId(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.id}>{cat.singkatan} - {cat.nama}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                                <button type="button" onClick={() => setShowUpload(false)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest">Batal</button>
                                <button type="submit" disabled={uploading || !file} className="rounded-xl bg-indigo-600 px-8 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 uppercase tracking-widest disabled:opacity-50">
                                    {uploading ? 'Memproses...' : 'Upload & Proses'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Edit Metadata */}
            {showEdit && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">Edit Info Dokumen</h3>
                            <button onClick={() => setShowEdit(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={handleUpdate} className="space-y-4">
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Judul Dokumen</label>
                                <input
                                    type="text"
                                    value={judul}
                                    onChange={(e) => setJudul(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                    required
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Sumber / Penerbit</label>
                                <input
                                    type="text"
                                    value={sumber}
                                    onChange={(e) => setSumber(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[10px] font-black text-gray-500 uppercase tracking-widest">Kategori</label>
                                <select
                                    value={categoryId}
                                    onChange={(e) => setCategoryId(e.target.value)}
                                    className="w-full rounded-xl border-gray-200 text-sm font-bold focus:ring-indigo-500"
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.id}>{cat.singkatan} - {cat.nama}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                                <button type="button" onClick={() => setShowEdit(false)} className="rounded-xl border border-gray-200 px-6 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest">Batal</button>
                                <button type="submit" disabled={uploading} className="rounded-xl bg-indigo-600 px-8 py-2.5 text-xs font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 uppercase tracking-widest disabled:opacity-50">
                                    {uploading ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Manage Chunks */}
            {showManageChunks && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                    <div className="w-full max-w-4xl h-[80vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                        <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                            <div>
                                <h3 className="text-xl font-black text-gray-900 uppercase tracking-tighter">Kelola Isi Teks (Chunks)</h3>
                                <p className="text-xs text-gray-500 font-bold mt-1">Dokumen: {selectedDoc?.judul}</p>
                            </div>
                            <button onClick={() => setShowManageChunks(false)} className="text-3xl text-gray-400 hover:text-gray-600">&times;</button>
                        </div>

                        <div className="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
                            {loadingChunks ? (
                                <div className="flex items-center justify-center h-64">
                                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                                </div>
                            ) : chunks.length === 0 ? (
                                <div className="text-center py-20 text-gray-400 italic font-bold">
                                    Tidak ada data teks ditemukan untuk dokumen ini.
                                </div>
                            ) : (
                                chunks.map((chunk, idx) => (
                                    <div key={chunk.id} className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
                                        <div className="px-4 py-2 border-b border-gray-50 bg-indigo-50/30 flex items-center justify-between">
                                            <span className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Bagian #{chunk.chunk_index + 1}</span>
                                            {editingChunkId !== chunk.id && (
                                                <button 
                                                    onClick={() => {
                                                        setEditingChunkId(chunk.id);
                                                        setChunkContent(chunk.content);
                                                    }}
                                                    className="text-[10px] font-black text-amber-600 hover:text-amber-800 uppercase tracking-widest bg-white px-3 py-1 rounded-lg border border-amber-100"
                                                >
                                                    Edit Teks
                                                </button>
                                            )}
                                        </div>
                                        <div className="p-4">
                                            {editingChunkId === chunk.id ? (
                                                <div className="space-y-3">
                                                    <textarea
                                                        value={chunkContent}
                                                        onChange={(e) => setChunkContent(e.target.value)}
                                                        className="w-full h-48 rounded-xl border-gray-200 text-sm font-medium focus:ring-amber-500"
                                                    />
                                                    <div className="flex justify-end gap-2">
                                                        <button 
                                                            onClick={() => setEditingChunkId(null)}
                                                            className="px-4 py-2 text-xs font-black text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-lg"
                                                            disabled={savingChunk}
                                                        >
                                                            Batal
                                                        </button>
                                                        <button 
                                                            onClick={() => handleUpdateChunk(chunk.id)}
                                                            className="px-4 py-2 text-xs font-black text-white bg-amber-600 hover:bg-amber-700 uppercase tracking-widest rounded-lg shadow-lg shadow-amber-100"
                                                            disabled={savingChunk}
                                                        >
                                                            {savingChunk ? 'Menyimpan...' : 'Simpan Bagian Ini'}
                                                        </button>
                                                    </div>
                                                </div>
                                            ) : (
                                                <p className="text-sm text-gray-700 leading-relaxed font-medium whitespace-pre-wrap">{chunk.content}</p>
                                            )}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        <div className="p-6 border-t border-gray-100 bg-white shrink-0 flex justify-between items-center">
                            <p className="text-[10px] text-gray-400 font-bold italic">Setiap perubahan teks akan memicu perhitungan ulang vektor (embedding) otomatis agar hasil pencarian AI tetap akurat.</p>
                            <button 
                                onClick={() => setShowManageChunks(false)} 
                                className="rounded-xl border border-gray-200 px-8 py-2.5 text-xs font-black text-gray-500 hover:bg-gray-50 uppercase tracking-widest"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
