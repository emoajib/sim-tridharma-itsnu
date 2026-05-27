import React, { Suspense, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, User, Download } from 'lucide-react';

// ─── Lazy-loaded tab components ───────────────────────────────────────────────
const AgendaSection = React.lazy(() => import('./AgendaSection'));
const MinutesSection = React.lazy(() => import('./MinutesSection'));
const ActionItems = React.lazy(() => import('./ActionItems'));
const ParticipantsList = React.lazy(() => import('./ParticipantsList'));

// ─── Types ────────────────────────────────────────────────────────────────────

interface UserItem {
    id: number;
    name: string;
}

interface ActionItem {
    id: number;
    rtm_id: number;
    deskripsi: string;
    pic_user_id: number | null;
    deadline: string | null;
    hasil: string | null;
    status: string;
    created_at: string;
    picUser?: UserItem | null;
}

interface RtmDetail {
    id: number;
    judul: string;
    tanggal_rapat: string | null;
    dipimpin_oleh_id: number | null;
    agenda: string | null;
    notulen: string | null;
    file_notulen: string | null;
    status: string;
    created_at: string;
    pimpinan?: UserItem | null;
    actionItems: ActionItem[];
}

interface Props {
    rtm: RtmDetail;
    user_list: UserItem[];
    success?: string;
    errors?: Record<string, string>;
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(date: string | null): string {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    } catch {
        return date;
    }
}

const TabFallback = () => (
    <div className="animate-pulse p-6">
        <div className="h-4 bg-gray-200 rounded w-3/4 mb-3" />
        <div className="h-4 bg-gray-200 rounded w-1/2 mb-3" />
        <div className="h-4 bg-gray-200 rounded w-2/3" />
    </div>
);

// ─── Main Component ───────────────────────────────────────────────────────────

export default function Show({ rtm, user_list, success }: Props) {
    const [activeTab, setActiveTab] = useState<'agenda' | 'notulen' | 'actions'>('agenda');
    const [deleteActionId, setDeleteActionId] = useState<number | null>(null);

    function handleDeleteAction() {
        if (!deleteActionId) return;
        router.delete(route('spmi.rtm.action-item.destroy', deleteActionId), {
            preserveScroll: true,
            onSuccess: () => setDeleteActionId(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Detail RTM — {rtm.judul}</h2>}
        >
            <Head title="Detail RTM" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <Link href={route('spmi.rtm')} className="text-indigo-600 hover:text-indigo-900">RTM</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">{rtm.judul}</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('spmi.rtm')} className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900">
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar RTM
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    {/* Header Card */}
                    <div className="mb-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div className="border-b border-gray-100 px-6 py-5">
                            <h1 className="text-xl font-bold text-gray-900">{rtm.judul}</h1>
                            <div className="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                <div className="flex items-center gap-1.5">
                                    <Calendar className="h-4 w-4" />
                                    {formatDate(rtm.tanggal_rapat)}
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <User className="h-4 w-4" />
                                    Pimpinan: {rtm.pimpinan?.name || '-'}
                                </div>
                                <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                    rtm.status === 'conducted' ? 'bg-green-100 text-green-800' :
                                    rtm.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                    'bg-gray-100 text-gray-800'
                                }`}>
                                    {rtm.status ? rtm.status.charAt(0).toUpperCase() + rtm.status.slice(1) : '-'}
                                </span>
                            </div>
                        </div>

                        {rtm.file_notulen && (
                            <div className="px-6 py-3">
                                <a href={`/storage/${rtm.file_notulen}`} target="_blank" rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                    <Download className="h-3.5 w-3.5" />
                                    Download File Notulen
                                </a>
                            </div>
                        )}

                        {/* Tabs */}
                        <div className="flex border-b border-gray-100 px-6">
                            {(['agenda', 'notulen', 'actions'] as const).map((tab) => (
                                <button
                                    key={tab}
                                    onClick={() => setActiveTab(tab)}
                                    className={`px-4 py-3 text-sm font-medium transition-colors ${
                                        activeTab === tab
                                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                                            : 'text-gray-500 hover:text-gray-700'
                                    }`}
                                >
                                    {tab === 'agenda' ? 'Agenda' : tab === 'notulen' ? 'Notulen' : `Action Items (${rtm.actionItems.length})`}
                                </button>
                            ))}
                        </div>

                        {/* Participants info */}
                        <Suspense fallback={null}>
                            <ParticipantsList
                                pimpinan={rtm.pimpinan}
                                tanggalRapat={rtm.tanggal_rapat}
                                status={rtm.status}
                                formatDate={formatDate}
                            />
                        </Suspense>
                    </div>

                    {/* Tab Content */}
                    <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <Suspense fallback={<TabFallback />}>
                            {activeTab === 'agenda' && <AgendaSection agenda={rtm.agenda} />}
                            {activeTab === 'notulen' && <MinutesSection notulen={rtm.notulen} />}
                            {activeTab === 'actions' && (
                                <ActionItems
                                    rtmId={rtm.id}
                                    actionItems={rtm.actionItems}
                                    user_list={user_list}
                                    onDeleteAction={setDeleteActionId}
                                />
                            )}
                        </Suspense>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            {deleteActionId && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">Yakin ingin menghapus action item ini?</p>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setDeleteActionId(null)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                            <button onClick={handleDeleteAction} className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">Hapus</button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
