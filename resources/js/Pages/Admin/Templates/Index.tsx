import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

interface TemplateItem {
    name: string;
    file: string;
    desc: string;
}

interface TemplateCategory {
    category: string;
    items: TemplateItem[];
}

interface Props {
    templates: TemplateCategory[];
}

export default function Index({ templates }: Props) {
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Pusat Download Template Akreditasi</h2>}
        >
            <Head title="Templates Akreditasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-indigo-700">Standardized Templates</h3>
                        <p className="mt-2 text-gray-600">
                            Unduh template Excel yang sudah disesuaikan dengan format instrumen akreditasi terbaru (2024-2025). 
                            Cukup salin data dari laporan lama Anda ke file ini, lalu unggah kembali melalui menu Import.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {templates.map((cat, idx) => (
                            <div key={idx} className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                <div className="bg-gray-50 border-b border-gray-200 px-6 py-4">
                                    <h4 className="font-bold text-gray-800 uppercase tracking-wider text-sm">{cat.category}</h4>
                                </div>
                                <div className="p-6">
                                    <ul className="space-y-4">
                                        {cat.items.map((item, iidx) => (
                                            <li key={iidx} className="flex items-center justify-between group">
                                                <div className="flex items-center gap-4">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600 font-bold">
                                                        XL
                                                    </div>
                                                    <div>
                                                        <p className="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{item.name}</p>
                                                        <p className="text-xs text-gray-500">{item.desc}</p>
                                                    </div>
                                                </div>
                                                <a 
                                                    href={route('admin.templates.download', item.file)}
                                                    className="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-indigo-600 border border-indigo-200 hover:bg-indigo-50 transition-all"
                                                >
                                                    Download &darr;
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Guidelines Card */}
                    <div className="mt-12 rounded-xl bg-indigo-50 border border-indigo-100 p-8">
                        <div className="flex gap-6">
                            <div className="text-4xl">🚀</div>
                            <div>
                                <h4 className="text-lg font-bold text-indigo-900 mb-2">Panduan Penggunaan "No-Code"</h4>
                                <ol className="list-decimal list-inside space-y-2 text-indigo-800 text-sm">
                                    <li>Pilih template sesuai dengan <strong>Lembaga Akreditasi</strong> prodi Anda.</li>
                                    <li>Isi data tridharma Anda pada kolom yang tersedia (jangan ubah nama kolom).</li>
                                    <li>Simpan file dan masuk ke menu <strong>Portofolio &rarr; Import Data</strong>.</li>
                                    <li>Pilih file yang sudah diisi dan klik <strong>Sinkronkan</strong>. AI akan otomatis memproses sisanya.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
