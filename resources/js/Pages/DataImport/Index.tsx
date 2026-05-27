import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useRef } from 'react';
import { Upload, FileSpreadsheet, Download, Eye, AlertCircle, CheckCircle, XCircle } from 'lucide-react';

interface Template {
    type: string;
    label: string;
    fields: string[];
}

interface Props {
    templates: Template[];
    success?: string;
    warning?: string;
    errors?: Record<string, string>;
}

interface PreviewRow {
    row_number: number;
    data: string[];
    mapped: Record<string, any> | null;
    valid: boolean;
    errors: string[];
}

export default function Index({ templates, success, warning }: Props) {
    const { props } = usePage();
    const user = (props as any).auth?.user;
    const permissions = new Set(user?.permissions ?? []);
    const can = (perm: string) => permissions.has(perm);

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [uploading, setUploading] = useState(false);
    const [previewing, setPreviewing] = useState(false);
    const [previewData, setPreviewData] = useState<PreviewRow[] | null>(null);
    const [previewTotal, setPreviewTotal] = useState(0);
    const [previewValid, setPreviewValid] = useState(0);
    const [error, setError] = useState('');

    const pddiktiTemplate = templates.find(t => t.type === 'dosen_pddikti');

    function triggerFileSelect() {
        fileInputRef.current?.click();
    }

    function handleFileSelect(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (file) {
            setSelectedFile(file);
            setPreviewData(null);
            setError('');
        }
    }

    async function handlePreview() {
        if (!selectedFile) return;
        setPreviewing(true);
        setError('');
        setPreviewData(null);

        const formData = new FormData();
        formData.append('file', selectedFile);

        try {
            const res = await fetch(route('data-import.preview-pddikti'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': (window as any).csrf_token },
                body: formData,
            });

            if (!res.ok) {
                const err = await res.json();
                setError(err.message || 'Gagal memproses preview');
                return;
            }

            const json = await res.json();
            setPreviewData(json.rows);
            setPreviewTotal(json.total);
            setPreviewValid(json.valid_count);
        } catch (err: any) {
            setError(err.message || 'Gagal menghubungi server');
        } finally {
            setPreviewing(false);
        }
    }

    async function handleUpload() {
        if (!selectedFile) return;
        setUploading(true);
        setError('');

        const formData = new FormData();
        formData.append('file', selectedFile);

        try {
            const res = await fetch(route('data-import.upload-pddikti'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': (window as any).csrf_token },
                body: formData,
            });

            if (!res.ok) {
                const err = await res.json();
                setError(err.message || 'Gagal mengupload file');
                return;
            }

            window.location.href = route('data-import.history');
        } catch (err: any) {
            setError(err.message || 'Gagal menghubungi server');
        } finally {
            setUploading(false);
        }
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Import Data PDDikti/SISTER
                </h2>
            }
        >
            <Head title="Import Data PDDikti" />

            <div className="py-6">
                <div className="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {success && (
                        <div className="rounded-lg bg-green-100 p-4 text-green-800 border border-green-200">
                            {success}
                        </div>
                    )}
                    {warning && (
                        <div className="rounded-lg bg-yellow-100 p-4 text-yellow-800 border border-yellow-200">
                            {warning}
                        </div>
                    )}

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow">
                        <div className="flex items-center gap-3 mb-6">
                            <FileSpreadsheet className="h-8 w-8 text-indigo-600" />
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900">
                                    Import Dosen dari PDDikti/SISTER
                                </h3>
                                <p className="text-sm text-gray-500">
                                    Upload file export SISTER/PDDikti (.xlsx, .xls, .csv) untuk import data dosen.
                                    Data akan otomatis dipetakan ke master data dosen termasuk split nama, pemetaan prodi, dan pembersihan data.
                                </p>
                            </div>
                        </div>

                        {pddiktiTemplate && (
                            <div className="mb-4 rounded-lg bg-gray-50 p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <span className="text-sm font-medium text-gray-700">Template:</span>
                                        <span className="ml-2 text-sm text-gray-600">{pddiktiTemplate.label}</span>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {pddiktiTemplate.fields.slice(0, 7).map((f, i) => (
                                                <span key={i} className="inline-block rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{f}</span>
                                            ))}
                                            <span className="inline-block rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-500">+{pddiktiTemplate.fields.length - 7} lainnya</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="mt-4">
                            <div
                                onClick={triggerFileSelect}
                                className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition hover:border-indigo-400 hover:bg-indigo-50"
                            >
                                <Upload className="mb-2 h-10 w-10 text-gray-400" />
                                <p className="text-sm font-medium text-gray-600">
                                    {selectedFile ? selectedFile.name : 'Klik untuk pilih file Data_dosen.xlsx'}
                                </p>
                                <p className="mt-1 text-xs text-gray-400">
                                    Format: .xlsx, .xls, .csv (maks 5MB)
                                </p>
                            </div>

                            <input
                                ref={fileInputRef}
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                className="hidden"
                                onChange={handleFileSelect}
                            />
                        </div>

                        {error && (
                            <div className="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                                <AlertCircle className="mr-1 inline h-4 w-4" />
                                {error}
                            </div>
                        )}

                        {selectedFile && (
                            <div className="mt-4 flex gap-3">
                                <button
                                    onClick={handlePreview}
                                    disabled={previewing || uploading}
                                    className="inline-flex items-center gap-2 rounded-lg border border-indigo-600 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 disabled:opacity-50"
                                >
                                    <Eye className="h-4 w-4" />
                                    {previewing ? 'Memproses...' : 'Preview'}
                                </button>
                                <button
                                    onClick={handleUpload}
                                    disabled={previewing || uploading}
                                    className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    <Upload className="h-4 w-4" />
                                    {uploading ? 'Mengimport...' : 'Import Data'}
                                </button>
                            </div>
                        )}

                        {previewData && (
                            <div className="mt-6">
                                <div className="mb-3 flex items-center gap-4 text-sm">
                                    <span className="text-gray-600">
                                        Total: <strong>{previewTotal}</strong> baris
                                    </span>
                                    <span className="flex items-center gap-1 text-green-600">
                                        <CheckCircle className="h-4 w-4" />
                                        Valid: {previewValid}
                                    </span>
                                    <span className="flex items-center gap-1 text-red-600">
                                        <XCircle className="h-4 w-4" />
                                        Gagal: {previewTotal - previewValid}
                                    </span>
                                </div>

                                <div className="max-h-96 overflow-auto rounded border border-gray-200">
                                    <table className="min-w-full text-sm">
                                        <thead className="bg-gray-100 sticky top-0">
                                            <tr>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIDN</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Depan</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Belakang</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {previewData.length === 0 && (
                                                <tr>
                                                    <td colSpan={7} className="p-4 text-center text-gray-400">
                                                        Tidak ada data
                                                    </td>
                                                </tr>
                                            )}
                                            {previewData.map((row, idx) => (
                                                <tr key={idx} className={row.valid ? '' : 'bg-red-50'}>
                                                    <td className="px-3 py-2 text-gray-500">{row.row_number}</td>
                                                    <td className="px-3 py-2 font-mono text-xs">{row.mapped?.nidn || '-'}</td>
                                                    <td className="px-3 py-2">{row.mapped?.nama_depan || '-'}</td>
                                                    <td className="px-3 py-2 text-gray-600">{row.mapped?.nama_belakang || '-'}</td>
                                                    <td className="px-3 py-2 text-xs">{row.mapped?.prodi_id || '-'}</td>
                                                    <td className="px-3 py-2 text-xs text-gray-600">{row.mapped?.jabatan_fungsional || '-'}</td>
                                                    <td className="px-3 py-2">
                                                        {row.valid ? (
                                                            <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                                                <CheckCircle className="h-3 w-3" />
                                                                OK
                                                            </span>
                                                        ) : (
                                                            <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                                <XCircle className="h-3 w-3" />
                                                                {row.errors[0]}
                                                            </span>
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow">
                        <h3 className="mb-3 text-lg font-semibold text-gray-900">Petunjuk</h3>
                        <ol className="list-decimal space-y-2 pl-5 text-sm text-gray-600">
                            <li>Download file <strong>Data_dosen.xlsx</strong> dari SISTER/PDDikti</li>
                            <li>Upload file di atas (klik area upload atau drag-drop)</li>
                            <li>Klik <strong>Preview</strong> untuk melihat hasil transformasi data</li>
                            <li>Jika preview OK, klik <strong>Import Data</strong> untuk menyimpan ke database</li>
                            <li>Cek riwayat import di halaman <strong>Riwayat Import</strong></li>
                        </ol>
                        <div className="mt-3 rounded bg-yellow-50 p-3 text-xs text-yellow-800">
                            <strong>Catatan:</strong> Data yang diimport akan otomatis: split nama jadi depan/belakang,
                            bersihkan HTML tags, petakan program studi, dan update data dosen yang sudah ada berdasarkan NIDN.
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
