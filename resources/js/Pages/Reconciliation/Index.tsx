import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface Reconciliation {
    id: number;
    source: string;
    status: string;
    message: string;
}

interface Props {
    reconciliations: Reconciliation[];
    stats: { total: number; pending: number; approved: number; rejected: number };
}

export default function Index({ reconciliations, stats }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Rekonsiliasi Data</h2>}>
            <Head title="Rekonsiliasi Data" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid grid-cols-4 gap-4 mb-8">
                        <div className="rounded-lg bg-white p-6 shadow">
                            <div className="text-2xl font-bold">{stats.total}</div>
                            <div className="text-sm text-gray-500">Total</div>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow">
                            <div className="text-2xl font-bold text-yellow-600">{stats.pending}</div>
                            <div className="text-sm text-gray-500">Pending</div>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow">
                            <div className="text-2xl font-bold text-green-600">{stats.approved}</div>
                            <div className="text-sm text-gray-500">Disetujui</div>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow">
                            <div className="text-2xl font-bold text-red-600">{stats.rejected}</div>
                            <div className="text-sm text-gray-500">Ditolak</div>
                        </div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-medium">Daftar Rekonsiliasi</h3>
                        {reconciliations.length === 0 ? (
                            <p className="text-gray-500">Belum ada data rekonsiliasi.</p>
                        ) : (
                            <div className="divide-y">
                                {reconciliations.map((item) => (
                                    <div key={item.id} className="flex items-center justify-between py-3">
                                        <div>
                                            <p className="font-medium">{item.source}</p>
                                            <p className="text-sm text-gray-500">{item.message}</p>
                                        </div>
                                        <span className={`rounded-full px-3 py-1 text-xs font-medium ${
                                            item.status === 'approved' ? 'bg-green-100 text-green-700' :
                                            item.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                            'bg-yellow-100 text-yellow-700'
                                        }`}>{item.status}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
