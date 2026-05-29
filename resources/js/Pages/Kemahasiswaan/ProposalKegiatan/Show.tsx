import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, FileText, Download, User, DollarSign, Clock } from 'lucide-react';

interface OrmawaItem { id: number; nama: string }
interface ProdiItem { id: number; nama_prodi: string }
interface PeriodeItem { id: number; nama_periode: string }

interface ProposalItem {
    id: number;
    jenis_proposal: string;
    ormawa_id: number;
    ormawa?: OrmawaItem;
    prodi_id: number;
    prodi?: ProdiItem;
    periode_id: number;
    periode?: PeriodeItem;
    judul_kegiatan: string;
    latar_belakang: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    rab_diajukan: number;
    rab_disetujui: number;
    file_proposal: string;
    file_lpj: string;
    status_kegiatan: string;
    status_hima: string;
    created_at: string;
}

interface TimelineItem {
    id: number;
    action: string;
    old_value: string | null;
    new_value: string | null;
    created_at: string;
    user?: { name: string };
}

interface Props {
    proposal: ProposalItem;
    timeline?: TimelineItem[];
}

export default function Show({ proposal, timeline }: Props) {
    const statusBadge: Record<string, string> = {
        Draft: 'bg-gray-100 text-gray-800',
        Review_Pembina: 'bg-blue-100 text-blue-800',
        Review_Kaprodi: 'bg-blue-100 text-blue-800',
        Review_Fakultas: 'bg-purple-100 text-purple-800',
        Review_WR3: 'bg-orange-100 text-orange-800',
        Approved: 'bg-green-100 text-green-800',
        Rejected: 'bg-red-100 text-red-800',
        LPJ_Submitted: 'bg-teal-100 text-teal-800',
        LPJ_Approved: 'bg-emerald-100 text-emerald-800',
        Review_Dekan: 'bg-blue-100 text-blue-800',
    };

    function formatDate(date: string | null): string {
        if (!date) return '-';
        try {
            return new Date(date).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric',
            });
        } catch { return date; }
    }

    function handleAction(action: string) {
        router.post(route('kemahasiswaan.proposal-kegiatan.action', [proposal.id, action]), {}, {
            preserveScroll: true,
        });
    }

    function renderActions() {
        const status = proposal.status_kegiatan;
        const btns: { label: string; action: string; color: string }[] = [];

        if (status === 'Draft') btns.push({ label: 'Submit Proposal', action: 'submit', color: 'bg-blue-600' });
        if (status === 'Review_Pembina') {
            btns.push({ label: 'Approve Pembina', action: 'approve-pembina', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_Kaprodi') {
            btns.push({ label: 'Approve Kaprodi', action: 'approve-kaprodi', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_Fakultas') {
            btns.push({ label: 'Approve Fakultas', action: 'approve-fakultas', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Review_WR3') {
            btns.push({ label: 'Approve WR3', action: 'approve-wr3', color: 'bg-green-600' });
            btns.push({ label: 'Reject', action: 'reject', color: 'bg-red-600' });
        }
        if (status === 'Approved') btns.push({ label: 'Submit LPJ', action: 'submit-lpj', color: 'bg-teal-600' });
        if (status === 'LPJ_Submitted') btns.push({ label: 'Approve LPJ', action: 'approve-lpj', color: 'bg-emerald-600' });

        return btns.length > 0 ? (
            <div className="flex flex-wrap gap-2">
                {btns.map((btn) => (
                    <button
                        key={btn.action}
                        onClick={() => handleAction(btn.action)}
                        className={`rounded-lg px-4 py-2 text-sm font-medium text-white ${btn.color} hover:opacity-90`}
                    >
                        {btn.label}
                    </button>
                ))}
            </div>
        ) : null;
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Detail Proposal Kegiatan</h2>}
        >
            <Head title="Detail Proposal Kegiatan" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <Link href={route('kemahasiswaan.proposal-kegiatan')} className="text-indigo-600 hover:text-indigo-900">Proposal Kegiatan</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">{proposal.judul_kegiatan}</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('kemahasiswaan.proposal-kegiatan')} className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900">
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar Proposal
                        </Link>
                    </div>

                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                            <div className="min-w-0 flex-1">
                                <h1 className="text-xl font-bold text-gray-900">{proposal.judul_kegiatan}</h1>
                                <div className="mt-2 flex items-center gap-2">
                                    <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${statusBadge[proposal.status_kegiatan] || 'bg-gray-100 text-gray-800'}`}>
                                        {proposal.status_kegiatan}
                                    </span>
                                    <span className="text-xs text-gray-500">{proposal.jenis_proposal}</span>
                                </div>
                            </div>
                            {renderActions()}
                        </div>

                        <div className="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ormawa</p>
                                    <p className="text-sm font-semibold text-gray-900">{proposal.ormawa?.nama || '-'}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                                    <User className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Prodi</p>
                                    <p className="text-sm font-semibold text-gray-900">{proposal.prodi?.nama_prodi || '-'}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Periode</p>
                                    <p className="text-sm font-semibold text-gray-900">{proposal.periode?.nama_periode || '-'}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggal Mulai</p>
                                    <p className="text-sm font-semibold text-gray-900">{formatDate(proposal.tanggal_mulai)}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                                    <Calendar className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggal Selesai</p>
                                    <p className="text-sm font-semibold text-gray-900">{formatDate(proposal.tanggal_selesai)}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                                    <DollarSign className="h-4 w-4" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">RAB Diajukan</p>
                                    <p className="text-sm font-semibold text-gray-900">Rp {proposal.rab_diajukan?.toLocaleString() || '-'}</p>
                                </div>
                            </div>
                            {proposal.rab_disetujui > 0 && (
                                <div className="flex items-start gap-3">
                                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                        <DollarSign className="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">RAB Disetujui</p>
                                        <p className="text-sm font-semibold text-gray-900">Rp {proposal.rab_disetujui.toLocaleString()}</p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="border-b border-gray-100 px-6 py-4">
                            <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Latar Belakang</h3>
                        </div>
                        <div className="p-6">
                            <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{proposal.latar_belakang || '-'}</p>
                        </div>
                    </div>

                    <div className="mb-6 flex gap-4">
                        {proposal.file_proposal && (
                            <a href={`/storage/${proposal.file_proposal}`} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                <FileText className="h-4 w-4" />
                                Download Proposal
                            </a>
                        )}
                        {proposal.file_lpj && (
                            <a href={`/storage/${proposal.file_lpj}`} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                <Download className="h-4 w-4" />
                                Download LPJ
                            </a>
                        )}
                    </div>

                    {timeline && timeline.length > 0 && (
                        <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div className="border-b border-gray-100 px-6 py-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                                    <Clock className="mr-1 inline h-3 w-3" />
                                    Timeline Aktivitas
                                </h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {timeline.map((t) => (
                                        <div key={t.id} className="flex gap-3 border-l-2 border-indigo-200 pl-4">
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-gray-900">{t.action}</p>
                                                {t.user && <p className="text-xs text-gray-500">oleh {t.user.name}</p>}
                                                <p className="text-xs text-gray-400">{formatDate(t.created_at)}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
