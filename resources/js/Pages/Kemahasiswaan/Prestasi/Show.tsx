import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Award, Calendar, Globe, Download, ExternalLink, User } from 'lucide-react';

interface KategoriItem {
    id: number;
    nama_kategori: string;
}

interface PrestasiMember {
    id: number;
    mahasiswa_id: number;
    peran: string;
    mahasiswa?: { nama: string; nim: string; prodi?: { nama_prodi: string } };
}

interface PrestasiItem {
    id: number;
    kategori_id: number;
    kategori?: KategoriItem;
    nama_kompetisi: string;
    penyelenggara: string;
    tanggal_pelaksanaan: string;
    tingkat: string;
    peringkat: string;
    bukti_url: string;
    file_sertifikat: string;
    status_verifikasi: string;
    catatan_reviewer: string;
    verified_at: string;
    created_at: string;
    members?: PrestasiMember[];
}

interface Props {
    prestasi: PrestasiItem;
    anggota: PrestasiMember[];
}

export default function Show({ prestasi, anggota }: Props) {
    const statusBadge: Record<string, string> = {
        DRAFT: 'bg-gray-100 text-gray-800',
        SUBMITTED: 'bg-blue-100 text-blue-800',
        REVISION_REQUESTED: 'bg-yellow-100 text-yellow-800',
        VERIFIED: 'bg-green-100 text-green-800',
    };

    function formatDate(date: string | null): string {
        if (!date) return '-';
        try {
            return new Date(date).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric',
            });
        } catch { return date; }
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Detail Prestasi</h2>}
        >
            <Head title="Detail Prestasi" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <Link href={route('kemahasiswaan.prestasi')} className="text-indigo-600 hover:text-indigo-900">Prestasi</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">{prestasi.nama_kompetisi}</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('kemahasiswaan.prestasi')} className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900">
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar Prestasi
                        </Link>
                    </div>

                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                            <div className="min-w-0 flex-1">
                                <h1 className="text-xl font-bold text-gray-900">{prestasi.nama_kompetisi}</h1>
                                <div className="mt-2">
                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[prestasi.status_verifikasi] || 'bg-gray-100 text-gray-800'}`}>
                                        {prestasi.status_verifikasi}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                    <Award className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Kategori</p>
                                    <p className="text-sm font-semibold text-gray-900">{prestasi.kategori?.nama_kategori || '-'}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                                    <Globe className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tingkat</p>
                                    <p className="text-sm font-semibold text-gray-900">{prestasi.tingkat}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggal Pelaksanaan</p>
                                    <p className="text-sm font-semibold text-gray-900">{formatDate(prestasi.tanggal_pelaksanaan)}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                    <Award className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Peringkat</p>
                                    <p className="text-sm font-semibold text-gray-900">{prestasi.peringkat}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Penyelenggara</p>
                                    <p className="text-sm font-semibold text-gray-900">{prestasi.penyelenggara}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Diverifikasi Pada</p>
                                    <p className="text-sm font-semibold text-gray-900">{formatDate(prestasi.verified_at)}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {prestasi.catatan_reviewer && (
                        <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Catatan Reviewer</h3>
                            </div>
                            <div className="p-6">
                                <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{prestasi.catatan_reviewer}</p>
                            </div>
                        </div>
                    )}

                    <div className="mb-6 flex gap-4">
                        {prestasi.bukti_url && (
                            <a href={prestasi.bukti_url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                <ExternalLink className="h-4 w-4" />
                                Lihat Bukti URL
                            </a>
                        )}
                        {prestasi.file_sertifikat && (
                            <a href={`/storage/${prestasi.file_sertifikat}`} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                <Download className="h-4 w-4" />
                                Download Sertifikat
                            </a>
                        )}
                    </div>

                    <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="border-b border-gray-100 px-6 py-4">
                            <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Anggota / Peserta</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">NIM</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prodi</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Peran</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {anggota.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="px-6 py-12 text-center text-gray-500">Tidak ada anggota</td>
                                        </tr>
                                    ) : (
                                        anggota.map((a) => (
                                            <tr key={a.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{a.mahasiswa?.nama || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{a.mahasiswa?.nim || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{a.mahasiswa?.prodi?.nama_prodi || '-'}</td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">{a.peran}</span>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
