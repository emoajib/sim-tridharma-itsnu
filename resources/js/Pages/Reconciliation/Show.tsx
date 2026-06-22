import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

interface Reconciliation {
    id: number;
    source: string;
    status: string;
    message: string;
    detail?: string;
}

interface Props {
    reconciliation: Reconciliation | null;
}

export default function Show({ reconciliation }: Props) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Detail Rekonsiliasi</h2>}>
            <Head title="Detail Rekonsiliasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <Link href={route('reconciliation.index')} className="mb-4 inline-block text-sm text-indigo-600 hover:text-indigo-900">
                        &larr; Kembali
                    </Link>

                    <div className="rounded-lg bg-white p-6 shadow">
                        {!reconciliation ? (
                            <p className="text-gray-500">Data tidak ditemukan.</p>
                        ) : (
                            <dl className="divide-y">
                                <div className="flex gap-4 py-3">
                                    <dt className="w-32 font-medium text-gray-500">Sumber</dt>
                                    <dd>{reconciliation.source}</dd>
                                </div>
                                <div className="flex gap-4 py-3">
                                    <dt className="w-32 font-medium text-gray-500">Status</dt>
                                    <dd>{reconciliation.status}</dd>
                                </div>
                                <div className="flex gap-4 py-3">
                                    <dt className="w-32 font-medium text-gray-500">Pesan</dt>
                                    <dd>{reconciliation.message}</dd>
                                </div>
                                {reconciliation.detail && (
                                    <div className="flex gap-4 py-3">
                                        <dt className="w-32 font-medium text-gray-500">Detail</dt>
                                        <dd className="whitespace-pre-wrap">{reconciliation.detail}</dd>
                                    </div>
                                )}
                            </dl>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
