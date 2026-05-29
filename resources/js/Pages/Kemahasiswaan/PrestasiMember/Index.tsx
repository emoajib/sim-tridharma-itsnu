import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { usePage } from '@inertiajs/react';

interface PrestasiMemberItem {
    id: number;
    prestasi_id: number;
    prestasi?: { nama_kompetisi: string };
    mahasiswa_id: number;
    mahasiswa?: { nama: string; nim: string };
    peran: string;
    nominal_reward: number;
    status_reward: string;
}

interface Prestasi {
    id: number;
    nama_kompetisi: string;
}

interface Mahasiswa {
    id: number;
    nama: string;
    nim: string;
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
    items: PaginatedData<PrestasiMemberItem>;
    prestasi_list: Prestasi[];
    mahasiswa_list: Mahasiswa[];
    success?: string;
}

export default function Index({ items, prestasi_list, mahasiswa_list, success }: Props) {
    const { auth } = usePage().props as any;
    const can = (perm: string) => auth?.user?.permissions?.includes(perm) ?? false;

    const [filterPrestasi, setFilterPrestasi] = useState(() => {
        return new URLSearchParams(window.location.search).get('prestasi_id') || '';
    });
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<PrestasiMemberItem | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<PrestasiMemberItem | null>(null);

    const { data, setData, post, put, delete: destroy, errors, processing, reset } = useForm({
        prestasi_id: '',
        mahasiswa_id: '',
        peran: '',
        nominal_reward: '',
        status_reward: '',
    });

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('kemahasiswaan.prestasi-member'), { prestasi_id: filterPrestasi }, { preserveState: true, replace: true });
        }, 500);
        return () => clearTimeout(timer);
    }, [filterPrestasi]);

    function openCreate() {
        reset();
        setEditing(null);
        setShowModal(true);
    }

    function openEdit(item: PrestasiMemberItem) {
        setEditing(item);
        setData({
            prestasi_id: String(item.prestasi_id),
            mahasiswa_id: String(item.mahasiswa_id),
            peran: item.peran,
            nominal_reward: String(item.nominal_reward),
            status_reward: item.status_reward,
        });
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            put(route('kemahasiswaan.prestasi-member.update', editing.id), {
                onSuccess: () => { setShowModal(false); reset(); setEditing(null); },
            });
        } else {
            post(route('kemahasiswaan.prestasi-member.store'), {
                onSuccess: () => { setShowModal(false); reset(); },
            });
        }
    };

    function confirmDelete(item: PrestasiMemberItem) {
        setDeleteTarget(item);
    }

    function executeDelete() {
        if (!deleteTarget) return;
        destroy(route('kemahasiswaan.prestasi-member.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    }

    const statusRewardBadge: Record<string, string> = {
        'Belum Diajukan': 'bg-gray-100 text-gray-800',
        Diajukan: 'bg-blue-100 text-blue-800',
        Disetujui: 'bg-green-100 text-green-800',
        Cair: 'bg-emerald-100 text-emerald-800',
    };

    const peranOptions = ['Ketua', 'Anggota', 'Peserta'];
    const statusRewardOptions = ['Belum Diajukan', 'Diajukan', 'Disetujui', 'Cair'];

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Prestasi Member</h2>}
        >
            <Head title="Prestasi Member" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Prestasi Member</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Dashboard</Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex gap-3">
                                    <select
                                        value={filterPrestasi}
                                        onChange={(e) => setFilterPrestasi(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Prestasi</option>
                                        {prestasi_list.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nama_kompetisi}</option>
                                        ))}
                                    </select>
                                </div>
                                {can('kemahasiswaan.create') && (
                                    <button
                                        onClick={openCreate}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        + Tambah Member
                                    </button>
                                )}
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prestasi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mahasiswa</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Peran</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reward</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status Reward</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-12 text-center text-gray-500">Tidak ada data</td>
                                        </tr>
                                    ) : (
                                        items.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{item.prestasi?.nama_kompetisi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.mahasiswa?.nama || '-'} ({item.mahasiswa?.nim || '-'})</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.peran}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{item.nominal_reward ? `Rp ${item.nominal_reward.toLocaleString()}` : '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusRewardBadge[item.status_reward] || 'bg-gray-100 text-gray-800'}`}>
                                                        {item.status_reward}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    {can('kemahasiswaan.edit') && (
                                                        <button onClick={() => openEdit(item)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                                    )}
                                                    {can('kemahasiswaan.delete') && (
                                                        <button onClick={() => confirmDelete(item)} className="text-red-600 hover:text-red-900">Hapus</button>
                                                    )}
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
                            <h3 className="text-lg font-semibold text-gray-900">{editing ? 'Edit Prestasi Member' : 'Tambah Prestasi Member'}</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Prestasi</label>
                                <select value={data.prestasi_id} onChange={(e) => setData('prestasi_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Prestasi</option>
                                    {prestasi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_kompetisi}</option>
                                    ))}
                                </select>
                                {errors.prestasi_id && <p className="mt-1 text-xs text-red-600">{errors.prestasi_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Mahasiswa</label>
                                <select value={data.mahasiswa_id} onChange={(e) => setData('mahasiswa_id', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Mahasiswa</option>
                                    {mahasiswa_list.map((m) => (
                                        <option key={m.id} value={m.id}>{m.nama} ({m.nim})</option>
                                    ))}
                                </select>
                                {errors.mahasiswa_id && <p className="mt-1 text-xs text-red-600">{errors.mahasiswa_id}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Peran</label>
                                <select value={data.peran} onChange={(e) => setData('peran', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Peran</option>
                                    {peranOptions.map((p) => (
                                        <option key={p} value={p}>{p}</option>
                                    ))}
                                </select>
                                {errors.peran && <p className="mt-1 text-xs text-red-600">{errors.peran}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Nominal Reward</label>
                                <input
                                    type="number"
                                    min="0"
                                    value={data.nominal_reward}
                                    onChange={(e) => setData('nominal_reward', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.nominal_reward && <p className="mt-1 text-xs text-red-600">{errors.nominal_reward}</p>}
                            </div>
                            <div className="mb-4">
                                <label className="mb-1 block text-sm font-medium text-gray-700">Status Reward</label>
                                <select value={data.status_reward} onChange={(e) => setData('status_reward', e.target.value)} className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Status</option>
                                    {statusRewardOptions.map((s) => (
                                        <option key={s} value={s}>{s}</option>
                                    ))}
                                </select>
                                {errors.status_reward && <p className="mt-1 text-xs text-red-600">{errors.status_reward}</p>}
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
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus data ini?</p>
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
