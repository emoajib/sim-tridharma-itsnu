import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
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
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;
    const flashError = (props as any).flash?.error;

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get(route('admin.users.index'), { search, role: roleFilter }, { preserveState: true });
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
