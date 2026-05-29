import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';
import { Eye } from 'lucide-react';

interface ProposalItem {
    id: number;
    jenis_proposal: string;
    ormawa_id: number;
    ormawa?: { nama: string };
    prodi_id: number;
    prodi?: { nama_prodi: string };
    periode_id: number;
    periode?: { nama_periode: string };
    judul_kegiatan: string;
    latar_belakang: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    rab_diajukan: number;
    rab_disetujui: number;
    file_proposal: string;
    file_lpj: string;
    status_kegiatan: string;
    status_hima: string;
}

interface Ormawa { id: number; nama: string }
interface Prodi { id: number; nama_prodi: string }
interface Periode { id: number; nama_periode: string }

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
    items: PaginatedData<ProposalItem>;
    ormawa_list: Ormawa[];
    prodi_list: Prodi[];
    periode_list: Periode[];
    success?: string;
}

export default function Index({ items, ormawa_list, prodi_list, periode_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [search, setSearch] = useState(() => {
        return new URLSearchParams(window.location.search).get('search') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<ProposalItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<ProposalItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        jenis_proposal: '',
        ormawa_id: '',
        prodi_id: '',
        periode_id: '',
        judul_kegiatan: '',
        latar_belakang: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
        rab_diajukan: '',
        file_proposal: null as File | null,
        file_lpj: null as File | null,
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.proposal-kegiatan'), { search }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: ProposalItem) {
        setEditing(item);
        setData({
            jenis_proposal: item.jenis_proposal,
            ormawa_id: String(item.ormawa_id),
            prodi_id: String(item.prodi_id),
            periode_id: String(item.periode_id),
            judul_kegiatan: item.judul_kegiatan,
            latar_belakang: item.latar_belakang,
            tanggal_mulai: item.tanggal_mulai,
            tanggal_selesai: item.tanggal_selesai,
            rab_diajukan: String(item.rab_diajukan),
            file_proposal: null,
            file_lpj: null,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('jenis_proposal', data.jenis_proposal);
        formData.append('ormawa_id', data.ormawa_id);
        formData.append('prodi_id', data.prodi_id);
        formData.append('periode_id', data.periode_id);
        formData.append('judul_kegiatan', data.judul_kegiatan);
        formData.append('latar_belakang', data.latar_belakang);
        formData.append('tanggal_mulai', data.tanggal_mulai);
        formData.append('tanggal_selesai', data.tanggal_selesai);
        formData.append('rab_diajukan', data.rab_diajukan);
        if (data.file_proposal) formData.append('file_proposal', data.file_proposal);
        if (data.file_lpj) formData.append('file_lpj', data.file_lpj);
        if (editing) formData.append('_method', 'PUT');

        const url = editing
            ? route('kemahasiswaan.proposal-kegiatan.update', editing.id)
            : route('kemahasiswaan.proposal-kegiatan.store');
        post(url, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
        });
    };

    function confirmDelete(item: ProposalItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.proposal-kegiatan.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    function handleAction(item: ProposalItem, action: string) {
        router.post(route('kemahasiswaan.proposal-kegiatan.action', [item.id, action]), {}, {
            preserveScroll: true,
        });
    }

    const statusBadge: Record<string, string> = {
        Draft: 'bg-gray-100 text-gray-800',
        Review_Pembina: 'bg-blue-100 text-blue-800',
        Review_Kaprodi: 'bg-blue-100 text-blue-800',
        Review_Fakultas: 'bg-purple-100 text-purple-800',
        Review_WR3: 'bg-orange-100 text-orange-800',
        Approved: 'bg-green-100 text-green-800',
        Rejected: 'bg-red-100 text-red-800',
        LPJ_Submitted: 'bg-teal-100 text-teal-800',
        LPJ_Approved: 'bg-emerald-100 text-emerald-800',
        Review_Dekan: 'bg-blue-100 text-blue-800',
    };

    function renderActionButtons(item: ProposalItem) {
        const status = item.status_kegiatan;
        const btns: { label: string; action: string; color: string }[] = [];

        if (status === 'Draft') btns.push({ label: 'Submit', action: 'submit', color: 'bg-blue-600' });
        if (status === 'Review_Pembina') {
            btns.push({ label: 'Approve Pembina', action: 'approve-pembina', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_Kaprodi') {
            btns.push({ label: 'Approve Kaprodi', action: 'approve-kaprodi', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_Fakultas') {
            btns.push({ label: 'Approve Fakultas', action: 'approve-fakultas', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_WR3') {
            btns.push({ label: 'Approve WR3', action: 'approve-wr3', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Approved') btns.push({ label: 'Submit LPJ', action: 'submit-lpj', color: 'bg-teal-600' });
        if (status === 'LPJ_Submitted') btns.push({ label: 'Approve LPJ', action: 'approve-lpj', color: 'bg-emerald-600' });

        return (
            <div className="flex flex-wrap gap-1">
                {btns.map((btn) => (
                    <button
                        key={btn.action}
                        onClick={() => handleAction(item, btn.action)}
                        className={`rounded px-2 py-1 text-xs font-medium text-white ${btn.color} hover:opacity-90`}
                    >
                        {btn.label}
                    </button>
                ))}
            </div>
        );
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Proposal Kegiatan</h2>}
        >
            <Head title="Proposal Kegiatan" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Proposal Kegiatan</span>
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
                                    placeholder="Cari judul kegiatan..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Proposal
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Judul</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ormawa</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        items.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{item.judul_kegiatan}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.jenis_proposal}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.ormawa?.nama || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[item.status_kegiatan] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.status_kegiatan}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <Link href={route('kemahasiswaan.proposal-kegiatan.show', item.id)} className="mr-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                    {can('kemahasiswaan.edit') && (
                                                        <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    )}
                                                    {can('kemahasiswaan.delete') && (
                                                        <button onClick={() => confirmDelete(item)} className="mr-2 text-red-600 hover:text-red-900">Hapus</button>
                                                    )}
                                                    {renderActionButtons(item)}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {items.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {items.from} - {items.to} dari {items.total}
                                </div>
                                <div className="flex gap-1">
                                    {items.links.map((link, i) => (
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Proposal' : 'Tambah Proposal'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Jenis Proposal</label>
                                <select value={data.jenis_proposal} onChange={(e) => setData('jenis_proposal', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Jenis</option>
                                    <option value="Ormawa">Ormawa</option>
                                    <option value="HIMA">HIMA</option>
                                </select>
                                {errors.jenis_proposal && <p className="mt-1 text-xs text-red-600">{errors.jenis_proposal}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Ormawa</label>
                                <select value={data.ormawa_id} onChange={(e) => setData('ormawa_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Ormawa</option>
                                    {ormawa_list.map((o) => (
                                        <option key={o.id} value={o.id}>{o.nama}</option>
                                    ))}
                                </select>
                                {errors.ormawa_id && <p className="mt-1 text-xs text-red-600">{errors.ormawa_id}</p>}
                            </div>
                            {data.jenis_proposal === 'HIMA' && (
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
                            )}
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                <select value={data.periode_id} onChange={(e) => setData('periode_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                    ))}
                                </select>
                                {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Judul Kegiatan</label>
                                <input
                                    type="text"
                                    value={data.judul_kegiatan}
                                    onChange={(e) => setData('judul_kegiatan', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.judul_kegiatan && <p className="mt-1 text-xs text-red-600">{errors.judul_kegiatan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Latar Belakang</label>
                                <textarea
                                    value={data.latar_belakang}
                                    onChange={(e) => setData('latar_belakang', e.target.value)}
                                    rows={4}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.latar_belakang && <p className="mt-1 text-xs text-red-600">{errors.latar_belakang}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_mulai}
                                        onChange={(e) => setData('tanggal_mulai', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_mulai && <p className="mt-1 text-xs text-red-600">{errors.tanggal_mulai}</p>}
                                </div>
                                <div className="mb-4">
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                    <input
                                        type="date"
                                        value={data.tanggal_selesai}
                                        onChange={(e) => setData('tanggal_selesai', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.tanggal_selesai && <p className="mt-1 text-xs text-red-600">{errors.tanggal_selesai}</p>}
                                </div>
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">RAB Diajukan</label>
                                <input
                                    type="number"
                                    min="0"
                                    value={data.rab_diajukan}
                                    onChange={(e) => setData('rab_diajukan', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.rab_diajukan && <p className="mt-1 text-xs text-red-600">{errors.rab_diajukan}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File Proposal</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_proposal', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_proposal && <p className="mt-1 text-xs text-red-600">{errors.file_proposal}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">File LPJ</label>
                                <input
                                    type="file"
                                    onChange={(e) => setData('file_lpj', e.target.files?.[0] || null)}
                                    className="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                {errors.file_lpj && <p className="mt-1 text-xs text-red-600">{errors.file_lpj}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus proposal <strong>{deleteTarget.judul_kegiatan}</strong>?</p>
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
