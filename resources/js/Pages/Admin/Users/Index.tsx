import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useRef } from 'react';
import { Dialog } from '@headlessui/react';

interface Role {
    id: number;
    name: string;
    guard_name: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: Role[];
    dosen?: { id: number; nama: string } | null;
    prodi?: { id: number; nama_prodi: string } | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    users: Paginated<UserItem>;
    filters: { search?: string; role?: string };
    roles: Role[];
}

interface PreviewResult {
    row: number;
    nama: string;
    email: string;
    action: 'CREATE' | 'UPDATE';
    normalized_nidn: string;
    nuptk: string | null;

    // SISTER fields now exposed from backend (A2)
    jabatan_fungsional: string | null;
    status_serdos: string | null;
    pendidikan_terakhir: string | null;
    kepangkatan: string | null;
    rumpun_ilmu: string | null;
    status_pegawai: string | null;
    ikatan_kerja: string | null;
    penempatan: string | null;

    would_assign_dosen_role: boolean;
    would_link_dosen: string | null;
    would_update_prodi: number | null;
    note: string;
}

export default function Index({ users, filters, roles }: Props) {
    const { props } = usePage();
    const user = (props as any).auth?.user;
    const permissions = new Set(user?.permissions ?? []);
    const can = (perm: string) => permissions.has(perm);

    const [search, setSearch] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');
    const [showModal, setShowModal] = useState(false);
    const [editingUser, setEditingUser] = useState<UserItem | null>(null);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        is_active: true,
        role_ids: [] as number[],
        dosen_id: '',
        prodi_id: '',
    });
    const [saving, setSaving] = useState(false);

    // === SISTER Import Preview State (Mode Aman) ===
    const [showPreviewModal, setShowPreviewModal] = useState(false);
    const [previewResults, setPreviewResults] = useState<PreviewResult[]>([]);
    const [previewSummary, setPreviewSummary] = useState<any>(null);
    const [previewLoading, setPreviewLoading] = useState(false);
    const [previewError, setPreviewError] = useState<string | null>(null);
    const [pendingImportFile, setPendingImportFile] = useState<File | null>(null);
    const flashSuccess = (props as any).flash?.success;
    const flashError = (props as any).flash?.error;

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get(route('admin.users.index'), { search, role: roleFilter }, { preserveState: true });
    }

    // ========== SISTER Import Handlers (Mode Aman + Preview) ==========
    const fileInputRef = useRef<HTMLInputElement>(null);

    function triggerFileSelect(forPreview: boolean) {
        setPreviewError(null);
        setPendingImportFile(null);
        // We store intent in a data attribute or closure via onChange
        if (fileInputRef.current) {
            fileInputRef.current.dataset.forPreview = forPreview ? 'true' : 'false';
            fileInputRef.current.click();
        }
    }

    async function handleFileSelected(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;

        const forPreview = e.target.dataset.forPreview === 'true';
        e.target.value = ''; // reset input

        if (forPreview) {
            await runImportPreview(file);
        } else {
            // Langsung import (tanpa preview)
            await performRealImport(file);
        }
    }

    async function runImportPreview(file: File) {
        setPreviewLoading(true);
        setPreviewError(null);
        setPreviewResults([]);
        setPreviewSummary(null);
        setPendingImportFile(file);

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(route('admin.users.import-preview'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setPreviewResults(data.results || []);
                setPreviewSummary(data.summary || {});
                setShowPreviewModal(true);
            } else {
                setPreviewError(data.message || 'Gagal menjalankan simulasi.');
            }
        } catch (err: any) {
            setPreviewError('Terjadi kesalahan saat simulasi: ' + (err.message || 'Unknown error'));
        } finally {
            setPreviewLoading(false);
        }
    }

    async function performRealImport(file: File) {
        setSaving(true);
        const formData = new FormData();
        formData.append('file', file);

        try {
            // Gunakan Inertia untuk real import (biar flash message muncul)
            router.post(route('admin.users.import'), { file }, {
                forceFormData: true,
                onSuccess: () => {
                    setSaving(false);
                    // Refresh halaman untuk melihat data baru
                    router.reload();
                },
                onError: (errors) => {
                    setSaving(false);
                    alert('Import gagal: ' + JSON.stringify(errors));
                },
            });
        } catch (err) {
            setSaving(false);
            alert('Terjadi kesalahan saat import.');
        }
    }

    async function confirmAndImport() {
        if (!pendingImportFile) return;

        setShowPreviewModal(false);
        await performRealImport(pendingImportFile);
        setPendingImportFile(null);
        setPreviewResults([]);
        setPreviewSummary(null);
    }

    function handleOpenCreate() {
        setEditingUser(null);
        setFormData({
            name: '', email: '', password: '', password_confirmation: '',
            is_active: true, role_ids: [], dosen_id: '', prodi_id: '',
        });
        setShowModal(true);
    }

    function handleOpenEdit(user: UserItem) {
        setEditingUser(user);
        setFormData({
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            is_active: user.is_active,
            role_ids: user.roles.map(r => r.id),
            dosen_id: user.dosen?.id?.toString() || '',
            prodi_id: user.prodi?.id?.toString() || '',
        });
        setShowModal(true);
    }

    function handleSave(e: React.FormEvent) {
        e.preventDefault();
        setSaving(true);
        const data: any = { ...formData };
        if (!data.password) delete data.password;
        if (!editingUser) {
            router.post(route('admin.users.store'), data, {
                onFinish: () => { setSaving(false); setShowModal(false); },
            });
        } else {
            router.put(route('admin.users.update', editingUser.id), data, {
                onFinish: () => { setSaving(false); setShowModal(false); },
            });
        }
    }

    function handleDelete(user: UserItem) {
        if (user.email === 'admin@itsnu.ac.id') {
            alert('Tidak dapat menghapus Super Admin utama.');
            return;
        }
        if (!confirm(`Hapus user "${user.name}"?`)) return;
        router.delete(route('admin.users.destroy', user.id));
    }

    function toggleRole(roleId: number) {
        setFormData(prev => ({
            ...prev,
            role_ids: prev.role_ids.includes(roleId)
                ? prev.role_ids.filter(id => id !== roleId)
                : [...prev.role_ids, roleId],
        }));
    }

    return (
        <AuthenticatedLayout>
            <Head title="Manajemen Pengguna" />
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h1 className="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
                        {can('users.create') && (
                            <button onClick={handleOpenCreate} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                + Tambah Pengguna
                            </button>
                        )}
                    </div>

                    {flashSuccess && <div className="mb-4 rounded bg-green-50 p-4 text-green-700">{flashSuccess}</div>}
                    {flashError && <div className="mb-4 rounded bg-red-50 p-4 text-red-700">{flashError}</div>}

                    <form onSubmit={handleSearch} className="mb-6 flex gap-4">
                        <input
                            type="text"
                            placeholder="Cari nama atau email..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="flex-1 rounded border border-gray-300 px-4 py-2"
                        />
                        <select
                            value={roleFilter}
                            onChange={e => setRoleFilter(e.target.value)}
                            className="rounded border border-gray-300 px-4 py-2"
                        >
                            <option value="">Semua Role</option>
                            {roles.map(r => (
                                <option key={r.id} value={r.name}>{r.name}</option>
                            ))}
                        </select>
                         <button type="submit" className="rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900">Cari</button>
                     </form>

                     {/* === SISTER Import Section (Mode Aman) === */}
                     <div className="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                         <div className="flex flex-wrap items-center gap-3">
                             <div className="font-semibold text-blue-900">Import Data Dosen dari SISTER</div>
                             <div className="text-xs text-blue-700">(Mode Aman: role & gelar tidak akan diubah)</div>

                             <div className="ml-auto flex gap-2">
                                 <button
                                     onClick={() => triggerFileSelect(true)}
                                     disabled={previewLoading || saving}
                                     className="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                                 >
                                     {previewLoading ? 'Memproses Simulasi...' : 'Simulasi Import'}
                                 </button>

                                 <button
                                     onClick={() => triggerFileSelect(false)}
                                     disabled={previewLoading || saving}
                                     className="rounded border border-blue-600 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 disabled:opacity-50"
                                 >
                                     {saving ? 'Mengimpor...' : 'Import Langsung'}
                                 </button>
                             </div>
                         </div>
                         <div className="mt-1 text-xs text-blue-600">
                             Gunakan tombol <strong>Simulasi Import</strong> terlebih dahulu untuk melihat pratinjau sebelum data benar-benar diubah.
                         </div>
                     </div>

                     {/* Hidden file input for SISTER import */}
                     <input
                         ref={fileInputRef}
                         type="file"
                         accept=".xlsx,.xls,.csv"
                         className="hidden"
                         onChange={handleFileSelected}
                     />

                     <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {users.data.map(user => (
                                    <tr key={user.id}>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm font-medium text-gray-900">{user.name}</div>
                                            {user.dosen && <div className="text-xs text-gray-500">{user.dosen.nama}</div>}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{user.email}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex flex-wrap gap-1">
                                                {user.roles.map(r => (
                                                    <span key={r.id} className="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800">
                                                        {r.name}
                                                    </span>
                                                ))}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                {user.is_active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            {can('users.edit') && (
                                                <button onClick={() => handleOpenEdit(user)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                            )}
                                            {can('users.delete') && (
                                                <button onClick={() => handleDelete(user)} className="text-red-600 hover:text-red-900">Hapus</button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="mt-4 flex items-center justify-between">
                        <div className="text-sm text-gray-500">
                            Menampilkan {users.data.length} dari {users.total} pengguna
                        </div>
                        <div className="flex gap-2">
                            {Array.from({ length: users.last_page }, (_, i) => i + 1).map(page => (
                                <button
                                    key={page}
                                    onClick={() => router.get(route('admin.users.index'), { page, search, role: roleFilter }, { preserveState: true })}
                                    className={`rounded px-3 py-1 text-sm ${page === users.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
                                >
                                    {page}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal Create/Edit */}
            {/* ========== PREVIEW IMPORT SISTER MODAL (Mode Aman) ========== */}
            <Dialog open={showPreviewModal} onClose={() => setShowPreviewModal(false)} className="relative z-50">
                <div className="fixed inset-0 bg-black/30" />
                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <Dialog.Panel className="w-full max-w-5xl rounded-lg bg-white p-6 shadow-xl">
                        <Dialog.Title className="mb-2 text-xl font-bold text-blue-900">
                            Hasil Simulasi Import SISTER (Mode Aman)
                        </Dialog.Title>

                        <div className="mb-4 rounded bg-blue-50 p-3 text-sm text-blue-800">
                            {previewSummary && (
                                <>
                                    Total baris valid: <strong>{previewSummary.total}</strong> &nbsp;|&nbsp;
                                    Akan dibuat (CREATE): <strong className="text-green-700">{previewSummary.create}</strong> &nbsp;|&nbsp;
                                    Akan diperbarui (UPDATE): <strong className="text-blue-700">{previewSummary.update}</strong>
                                </>
                            )}
                            <div className="mt-1 font-medium text-blue-900">
                                {previewSummary?.mode_aman_note || 'Role dan gelar_depan/gelar_belakang TIDAK akan diubah.'}
                            </div>
                        </div>

                        {previewError && (
                            <div className="mb-4 rounded bg-red-100 p-3 text-red-700">{previewError}</div>
                        )}

                        <div className="max-h-[420px] overflow-auto rounded border">
                            <table className="min-w-full text-sm">
                                <thead className="bg-gray-100">
                                     <tr>
                                         <th className="px-3 py-2 text-left">Baris</th>
                                         <th className="px-3 py-2 text-left">Nama</th>
                                         <th className="px-3 py-2 text-left">Email</th>
                                         <th className="px-3 py-2">Aksi</th>
                                         <th className="px-3 py-2 text-left">NIDN</th>
                                         <th className="px-3 py-2 text-left">Jabatan Fungsional</th>
                                         <th className="px-3 py-2 text-left">Status Serdos</th>
                                         <th className="px-3 py-2 text-left">Pendidikan</th>
                                         {/* Additional SISTER fields (A2) */}
                                         <th className="px-3 py-2 text-left">Kepangkatan</th>
                                         <th className="px-3 py-2 text-left">Rumpun Ilmu</th>
                                         <th className="px-3 py-2 text-left">Status Pegawai</th>
                                         <th className="px-3 py-2 text-left">Ikatan Kerja</th>
                                         <th className="px-3 py-2 text-left">Penempatan</th>
                                         <th className="px-3 py-2 text-left">Link ke Dosen</th>
                                         <th className="px-3 py-2 text-left">Prodi</th>
                                     </tr>
                                </thead>
                                <tbody>
                                      {previewResults.length === 0 && (
                                          <tr><td colSpan={15} className="p-4 text-center text-gray-500">Tidak ada data hasil simulasi.</td></tr>
                                      )}
                                      {previewResults.map((r, idx) => (
                                          <tr key={idx} className="border-t">
                                              <td className="px-3 py-1.5 text-gray-500">{r.row}</td>
                                              <td className="px-3 py-1.5 font-medium">{r.nama}</td>
                                              <td className="px-3 py-1.5 text-gray-600 text-xs">{r.email}</td>
                                              <td className="px-3 py-1.5 text-center">
                                                  <span className={`inline-block rounded px-2 py-0.5 text-xs font-semibold ${r.action === 'CREATE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
                                                      {r.action}
                                                  </span>
                                              </td>
                                              <td className="px-3 py-1.5 font-mono text-xs">{r.normalized_nidn}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.jabatan_fungsional || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.status_serdos || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.pendidikan_terakhir || <span className="text-gray-400">—</span>}</td>
                                              {/* Additional SISTER fields (A2) */}
                                              <td className="px-3 py-1.5 text-xs">{r.kepangkatan || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.rumpun_ilmu || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.status_pegawai || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.ikatan_kerja || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.penempatan || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.would_link_dosen || <span className="text-gray-400">—</span>}</td>
                                              <td className="px-3 py-1.5 text-xs">{r.would_update_prodi ?? '—'}</td>
                                          </tr>
                                      ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-4 text-xs text-gray-600">
                            Catatan: Data gelar dan role yang sudah ada <strong>tidak akan diubah</strong>. Gelar hanya dapat diisi secara manual melalui form edit Dosen.
                        </div>

                        <div className="mt-6 flex justify-end gap-3">
                            <button
                                onClick={() => { setShowPreviewModal(false); setPendingImportFile(null); }}
                                className="rounded border border-gray-300 px-4 py-2 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={confirmAndImport}
                                disabled={!pendingImportFile}
                                className="rounded bg-emerald-600 px-5 py-2 font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                Ya, Lakukan Import Sekarang
                            </button>
                        </div>
                    </Dialog.Panel>
                </div>
            </Dialog>

            {/* ========== CREATE / EDIT USER MODAL (existing) ========== */}
            <Dialog open={showModal} onClose={() => setShowModal(false)} className="relative z-50">
                <div className="fixed inset-0 bg-black/30" />
                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <Dialog.Panel className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <Dialog.Title className="mb-4 text-lg font-bold">
                            {editingUser ? 'Edit Pengguna' : 'Tambah Pengguna'}
                        </Dialog.Title>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Nama</label>
                                <input
                                    type="text"
                                    required
                                    value={formData.name}
                                    onChange={e => setFormData(p => ({ ...p, name: e.target.value }))}
                                    className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    type="email"
                                    required
                                    value={formData.email}
                                    onChange={e => setFormData(p => ({ ...p, email: e.target.value }))}
                                    className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Password {editingUser && '(kosongkan jika tidak ingin mengubah)'}
                                </label>
                                <input
                                    type="password"
                                    {...(!editingUser ? { required: true } : {})}
                                    value={formData.password}
                                    onChange={e => setFormData(p => ({ ...p, password: e.target.value }))}
                                    className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                />
                            </div>
                            {!editingUser && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                    <input
                                        type="password"
                                        required
                                        value={formData.password_confirmation}
                                        onChange={e => setFormData(p => ({ ...p, password_confirmation: e.target.value }))}
                                        className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                    />
                                </div>
                            )}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Role</label>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {roles.map(role => (
                                        <label key={role.id} className="inline-flex items-center rounded border border-gray-300 px-3 py-1">
                                            <input
                                                type="checkbox"
                                                checked={formData.role_ids.includes(role.id)}
                                                onChange={() => toggleRole(role.id)}
                                                className="mr-2"
                                            />
                                            <span className="text-sm">{role.name}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                            <div className="flex items-center gap-4">
                                <label className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={formData.is_active}
                                        onChange={e => setFormData(p => ({ ...p, is_active: e.target.checked }))}
                                        className="mr-2"
                                    />
                                    <span className="text-sm font-medium text-gray-700">Aktif</span>
                                </label>
                            </div>
                            <div className="flex justify-end gap-3 pt-4">
                                <button type="button" onClick={() => setShowModal(false)} className="rounded border border-gray-300 px-4 py-2 hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" disabled={saving} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50">
                                    {saving ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </Dialog.Panel>
                </div>
            </Dialog>
        </AuthenticatedLayout>
    );
}
