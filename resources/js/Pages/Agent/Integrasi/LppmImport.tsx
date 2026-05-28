import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/Components';

export default function LppmImport() {
    const [importType, setImportType] = useState<'penelitian' | 'pkm'>('penelitian');
    const [isUploading, setIsUploading] = useState(false);

    const { data, setData, post, progress } = useForm({
        file: null as File | null,
    });

    function handleImport(e: React.FormEvent) {
        e.preventDefault();
        if (!data.file) return;

        setIsUploading(true);
        const routeName = importType === 'penelitian'
            ? 'lppm.import.penelitian'
            : 'lppm.import.pkm';

        post(route(routeName), {
            preserveScroll: true,
            onFinish: () => {
                setIsUploading(false);
                setData('file', null);
            },
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Import Hibah Internal LPPM</h2>}
        >
            <Head title="Import LPPM" />

            <div className="py-8">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <h3 className="text-lg font-medium text-gray-900">Import Data Hibah Internal LPPM</h3>
                                <div className="flex gap-2">
                                    <Link
                                        href={route('lppm.template.penelitian')}
                                        className="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider hover:bg-emerald-700 transition-all"
                                    >
                                        Template Penelitian
                                    </Link>
                                    <Link
                                        href={route('lppm.template.pkm')}
                                        className="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider hover:bg-emerald-700 transition-all"
                                    >
                                        Template PKM
                                    </Link>
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={() => setImportType('penelitian')}
                                    className={`px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all ${
                                        importType === 'penelitian'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                                >
                                    Penelitian
                                </button>
                                <button
                                    onClick={() => setImportType('pkm')}
                                    className={`px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all ${
                                        importType === 'pkm'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                                >
                                    PKM
                                </button>
                            </div>

                            <form onSubmit={handleImport} className="space-y-4">
                                <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                                    <input
                                        type="file"
                                        accept=".xlsx,.xls"
                                        onChange={(e) => setData('file', e.target.files?.[0] || null)}
                                        className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    <p className="mt-2 text-xs text-gray-500">
                                        Format: XLSX atau XLS (Excel)
                                    </p>
                                </div>

                                {progress && (
                                    <div className="w-full bg-gray-200 rounded-full h-2.5">
                                        <div
                                            className="bg-blue-600 h-2.5 rounded-full transition-all"
                                            style={{ width: `${progress.percentage}%` }}
                                        />
                                    </div>
                                )}

                                <Button
                                    type="submit"
                                    disabled={!data.file || isUploading}
                                    className="w-full"
                                >
                                    {isUploading ? 'Mengupload...' : `Import ${importType === 'penelitian' ? 'Penelitian' : 'PKM'}`}
                                </Button>
                            </form>
                        </div>
                    </div>

                    <div className="mt-6 bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Petunjuk</h3>
                            <ol className="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                <li>Download template yang sesuai (Penelitian atau PKM)</li>
                                <li>Isi data sesuai format di template</li>
                                <li>Pastikan kolom NIDN sudah terdaftar di database dosen</li>
                                <li>Upload file yang sudah diisi</li>
                                <li>Hasil import akan muncul di <Link href={route('data-import.history')} className="text-blue-600 hover:underline">riwayat import</Link></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
