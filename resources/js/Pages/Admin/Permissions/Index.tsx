import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    permissions: Paginated<Permission>;
    modules: string[];
    filters: { search?: string; module?: string };
}

export default function Index({ permissions, modules, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [moduleFilter, setModuleFilter] = useState(filters.module || '');
    const { props } = usePage();

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get(route('admin.permissions.index'), { search, module: moduleFilter }, { preserveState: true });
    }

    // Group by module for display
    const grouped = permissions.data.reduce((acc, perm) => {
        const [module, action] = perm.name.split('.');
        if (!acc[module]) acc[module] = [];
        acc[module].push(perm);
        return acc;
    }, {} as Record<string, Permission[]>);

    return (
        <AuthenticatedLayout>
            <Head title="Daftar Permission" />
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">Daftar Permission</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Permission didefinisikan otomatis dari sistem. Untuk mengubah permission, edit RolePermissionSeeder.php.
                        </p>
                    </div>

                    <form onSubmit={handleSearch} className="mb-6 flex gap-4">
                        <input
                            type="text"
                            placeholder="Cari nama permission..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="flex-1 rounded border border-gray-300 px-4 py-2"
                        />
                        <select
                            value={moduleFilter}
                            onChange={e => setModuleFilter(e.target.value)}
                            className="rounded border border-gray-300 px-4 py-2"
                        >
                            <option value="">Semua Module</option>
                            {modules.map(m => (
                                <option key={m} value={m}>{m}</option>
                            ))}
                        </select>
                        <button type="submit" className="rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900">Cari</button>
                    </form>

                    <div className="space-y-6">
                        {Object.entries(grouped).map(([module, perms]) => (
                            <div key={module} className="rounded-lg border border-gray-200 bg-white shadow">
                                <div className="border-b border-gray-200 bg-gray-50 px-6 py-3">
                                    <h2 className="text-lg font-semibold capitalize text-gray-900">{module}</h2>
                                    <p className="text-xs text-gray-500">{perms.length} permissions</p>
                                </div>
                                <div className="p-4">
                                    <div className="flex flex-wrap gap-2">
                                        {perms.map(perm => (
                                            <span key={perm.id} className="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                                {perm.name}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-4 flex items-center justify-between">
                        <div className="text-sm text-gray-500">
                            Total {permissions.total} permissions
                        </div>
                        <div className="flex gap-2">
                            {Array.from({ length: permissions.last_page }, (_, i) => i + 1).map(page => (
                                <button
                                    key={page}
                                    onClick={() => router.get(route('admin.permissions.index'), { page, search, module: moduleFilter }, { preserveState: true })}
                                    className={`rounded px-3 py-1 text-sm ${page === permissions.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
                                >
                                    {page}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
