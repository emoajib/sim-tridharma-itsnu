import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { FileSpreadsheet, CheckCircle, XCircle, AlertTriangle, Download } from 'lucide-react';

interface ImportHistory {
    id: number;
    type: string;
    file_name: string;
    total_rows: number;
    success_rows: number;
    failed_rows: number;
    errors: any[];
    status: string;
    created_at: string;
    user: { id: number; name: string } | null;
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
    imports: PaginatedData<ImportHistory>;
    success?: string;
}

export default function History({ imports, success }: Props) {
    const { props } = usePage();
    const user = (props as any).auth?.user;
    const permissions = new Set(user?.permissions ?? []);
    const can = (perm: string) => permissions.has(perm);

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function getStatusBadge(status: string) {
        switch (status) {
            case 'completed':
                return (
                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                        <CheckCircle className="h-3.5 w-3.5" />
                        Berhasil
                    </span>
                );
            case 'completed_with_errors':
                return (
                    <span className="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800">
                        <AlertTriangle className="h-3.5 w-3.5" />
                        Sebagian Gagal
                    </span>
                );
            case 'failed':
                return (
                    <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                        <XCircle className="h-3.5 w-3.5" />
                        Gagal
                    </span>
                );
            default:
                return (
                    <span className="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                        {status}
                    </span>
                );
        }
    }

    const labelMap: Record<string, string> = {
        dosen: 'Dosen',
        dosen_pddikti: 'Dosen (PDDikti)',
        mahasiswa: 'Mahasiswa',
        mata_kuliah: 'Mata Kuliah',
        prodi: 'Prodi',
        kurikulum: 'Kurikulum',
        mitra: 'Mitra',
        sarana: 'Sarana',
        users: 'User',
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Riwayat Import Data
                </h2>
            }
        >
            <Head title="Riwayat Import" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-green-800 border border-green-200">
                            {success}
                        </div>
                    )}

                    <div className="rounded-lg border border-gray-200 bg-white shadow">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <div className="flex items-center gap-2">
                                <FileSpreadsheet className="h-5 w-5 text-indigo-600" />
                                <h3 className="text-lg font-semibold text-gray-900">Daftar Import</h3>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipe</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">File</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Sukses</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Gagal</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Waktu</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Oleh</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {imports.data.length === 0 && (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center text-gray-400">
                                                Belum ada riwayat import
                                            </td>
                                        </tr>
                                    )}
                                    {imports.data.map((imp) => (
                                        <tr key={imp.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {labelMap[imp.type] || imp.type}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600 max-w-[200px] truncate">
                                                {imp.file_name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {getStatusBadge(imp.status)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                                {imp.total_rows}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                                                {imp.success_rows}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-red-600">
                                                {imp.failed_rows > 0 ? imp.failed_rows : '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {formatDate(imp.created_at)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {imp.user?.name || 'Sistem'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {imports.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-3">
                                <div className="text-sm text-gray-500">
                                    Menampilkan {imports.from}-{imports.to} dari {imports.total}
                                </div>
                                <div className="flex gap-2">
                                    {Array.from({ length: imports.last_page }, (_, i) => i + 1).map(page => (
                                        <Link
                                            key={page}
                                            href={route('data-import.history', { page })}
                                            className={`rounded px-3 py-1 text-sm ${
                                                page === imports.current_page
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            }`}
                                        >
                                            {page}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="mt-4 text-right">
                        <Link
                            href={route('data-import.templates')}
                            className="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            <Download className="h-4 w-4" />
                            Kembali ke halaman Import
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
