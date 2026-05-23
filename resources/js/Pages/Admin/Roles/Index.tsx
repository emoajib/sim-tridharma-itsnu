import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Dialog } from '@headlessui/react';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface RoleItem {
    id: number;
    name: string;
    guard_name: string;
    permissions: Permission[];
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    roles: Paginated<RoleItem>;
    allPermissions: Permission[];
    filters: { search?: string };
}

export default function Index({ roles, allPermissions, filters }: Props) {
    const { props } = usePage();
    const user = (props as any).auth?.user;
    const permissions = new Set(user?.permissions ?? []);
    const can = (perm: string) => permissions.has(perm);

    const [search, setSearch] = useState(filters.search || '');
    const [showModal, setShowModal] = useState(false);
    const [editingRole, setEditingRole] = useState<RoleItem | null>(null);
    const [formData, setFormData] = useState({
        name: '',
        guard_name: 'web',
        permission_ids: [] as number[],
    });
    const [saving, setSaving] = useState(false);
    const [matrixOpen, setMatrixOpen] = useState<number | null>(null);
    const { props } = usePage();
    const flashSuccess = (props as any).flash?.success;

    // Group permissions by module
    const groupedPermissions = allPermissions.reduce((acc, perm) => {
        const [module] = perm.name.split('.');
        if (!acc[module]) acc[module] = [];
        acc[module].push(perm);
        return acc;
    }, {} as Record<string, Permission[]>);

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get(route('admin.roles.index'), { search }, { preserveState: true });
    }

    function handleOpenCreate() {
        setEditingRole(null);
        setFormData({ name: '', guard_name: 'web', permission_ids: [] });
        setShowModal(true);
    }

    function handleOpenEdit(role: RoleItem) {
        setEditingRole(role);
        setFormData({
            name: role.name,
            guard_name: role.guard_name,
            permission_ids: role.permissions.map(p => p.id),
        });
        setShowModal(true);
    }

    function handleSave(e: React.FormEvent) {
        e.preventDefault();
        setSaving(true);
        if (!editingRole) {
            router.post(route('admin.roles.store'), formData, {
                onFinish: () => { setSaving(false); setShowModal(false); },
            });
        } else {
            router.put(route('admin.roles.update', editingRole.id), formData, {
                onFinish: () => { setSaving(false); setShowModal(false); },
            });
        }
    }

    function handleDelete(role: RoleItem) {
        if (role.name === 'Super Admin') {
            alert('Tidak dapat menghapus role Super Admin.');
            return;
        }
        if (!confirm(`Hapus role "${role.name}"?`)) return;
        router.delete(route('admin.roles.destroy', role.id));
    }

    function togglePermission(permId: number) {
        setFormData(prev => ({
            ...prev,
            permission_ids: prev.permission_ids.includes(permId)
                ? prev.permission_ids.filter(id => id !== permId)
                : [...prev.permission_ids, permId],
        }));
    }

    function toggleAllInModule(module: string, checked: boolean) {
        const permIds = groupedPermissions[module].map(p => p.id);
        setFormData(prev => ({
            ...prev,
            permission_ids: checked
                ? [...new Set([...prev.permission_ids, ...permIds])]
                : prev.permission_ids.filter(id => !permIds.includes(id)),
        }));
    }

    return (
        <AuthenticatedLayout>
            <Head title="Manajemen Role" />
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h1 className="text-2xl font-bold text-gray-900">Manajemen Role & Permission</h1>
                        {can('roles.create') && (
                            <button onClick={handleOpenCreate} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                + Tambah Role
                            </button>
                        )}
                    </div>

                    {flashSuccess && <div className="mb-4 rounded bg-green-50 p-4 text-green-700">{flashSuccess}</div>}

                    <form onSubmit={handleSearch} className="mb-6 flex gap-4">
                        <input
                            type="text"
                            placeholder="Cari nama role..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="flex-1 rounded border border-gray-300 px-4 py-2"
                        />
                        <button type="submit" className="rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900">Cari</button>
                    </form>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Role</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah Permission</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Guard</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {roles.data.map(role => (
                                    <tr key={role.id}>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{role.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{role.permissions.length} permissions</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{role.guard_name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button onClick={() => setMatrixOpen(matrixOpen === role.id ? null : role.id)} className="mr-2 text-indigo-600 hover:text-indigo-900">
                                                {matrixOpen === role.id ? 'Tutup' : 'Matrix'}
                                            </button>
                                            {can('roles.edit') && (
                                                <button onClick={() => handleOpenEdit(role)} className="mr-2 text-indigo-600 hover:text-indigo-900">Edit</button>
                                            )}
                                            {can('roles.delete') && (
                                                <button onClick={() => handleDelete(role)} className="text-red-600 hover:text-red-900">Hapus</button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Permission Matrix per Role */}
                    {matrixOpen && (
                        <div className="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow">
                            <h3 className="mb-4 text-lg font-bold">
                                Permission Matrix: {roles.data.find(r => r.id === matrixOpen)?.name}
                            </h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Module</th>
                                            {Object.values(groupedPermissions).flat().map(perm => (
                                                <th key={perm.id} className="px-2 py-2 text-center text-xs font-medium uppercase text-gray-500">
                                                    {perm.name.split('.')[1]}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {Object.entries(groupedPermissions).map(([module, perms]) => {
                                            const role = roles.data.find(r => r.id === matrixOpen);
                                            const allChecked = perms.every(p => role?.permissions.some(rp => rp.id === p.id));
                                            return (
                                                <tr key={module}>
                                                    <td className="px-4 py-2 text-sm font-medium text-gray-900 capitalize">{module}</td>
                                                    {perms.map(perm => (
                                                        <td key={perm.id} className="px-2 py-2 text-center">
                                                            <input
                                                                type="checkbox"
                                                                checked={role?.permissions.some(p => p.id === perm.id) || false}
                                                                onChange={() => {}} // read-only view
                                                                className="mx-auto"
                                                            />
                                                        </td>
                                                    ))}
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    <div className="mt-4 flex items-center justify-between">
                        <div className="text-sm text-gray-500">
                            Menampilkan {roles.data.length} dari {roles.total} role
                        </div>
                        <div className="flex gap-2">
                            {Array.from({ length: roles.last_page }, (_, i) => i + 1).map(page => (
                                <button
                                    key={page}
                                    onClick={() => router.get(route('admin.roles.index'), { page, search }, { preserveState: true })}
                                    className={`rounded px-3 py-1 text-sm ${page === roles.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
                                >
                                    {page}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal Create/Edit Role */}
            <Dialog open={showModal} onClose={() => setShowModal(false)} className="relative z-50">
                <div className="fixed inset-0 bg-black/30" />
                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <Dialog.Panel className="w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                        <Dialog.Title className="mb-4 text-lg font-bold">
                            {editingRole ? 'Edit Role' : 'Tambah Role'}
                        </Dialog.Title>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Nama Role</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.name}
                                        onChange={e => setFormData(p => ({ ...p, name: e.target.value }))}
                                        className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Guard</label>
                                    <input
                                        type="text"
                                        value={formData.guard_name}
                                        onChange={e => setFormData(p => ({ ...p, guard_name: e.target.value }))}
                                        className="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                    />
                                </div>
                            </div>

                            <div>
                                <div className="mb-2 flex items-center justify-between">
                                    <label className="block text-sm font-medium text-gray-700">Permissions</label>
                                    <button
                                        type="button"
                                        onClick={() => setFormData(p => ({ ...p, permission_ids: allPermissions.map(perm => perm.id) }))}
                                        className="text-xs text-indigo-600 hover:text-indigo-900"
                                    >
                                        Pilih Semua
                                    </button>
                                </div>
                                <div className="max-h-96 space-y-4 overflow-y-auto rounded border border-gray-200 p-4">
                                    {Object.entries(groupedPermissions).map(([module, perms]) => {
                                        const allInModuleChecked = perms.every(p => formData.permission_ids.includes(p.id));
                                        const someInModuleChecked = perms.some(p => formData.permission_ids.includes(p.id));
                                        return (
                                            <div key={module} className="rounded border border-gray-100 p-3">
                                                <label className="flex items-center font-medium capitalize">
                                                    <input
                                                        type="checkbox"
                                                        checked={allInModuleChecked}
                                                        ref={el => { if (el) el.indeterminate = someInModuleChecked && !allInModuleChecked; }}
                                                        onChange={e => toggleAllInModule(module, e.target.checked)}
                                                        className="mr-2"
                                                    />
                                                    {module}
                                                </label>
                                                <div className="mt-2 ml-6 flex flex-wrap gap-2">
                                                    {perms.map(perm => (
                                                        <label key={perm.id} className="inline-flex items-center rounded border border-gray-200 px-2 py-1 text-xs">
                                                            <input
                                                                type="checkbox"
                                                                checked={formData.permission_ids.includes(perm.id)}
                                                                onChange={() => togglePermission(perm.id)}
                                                                className="mr-1"
                                                            />
                                                            {perm.name.split('.')[1]}
                                                        </label>
                                                    ))}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
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
