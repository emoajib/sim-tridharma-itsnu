import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useRef } from 'react';
import {
    ArrowLeft,
    Calendar,
    User,
    FileText,
    Download,
    Plus,
    CheckCircle2,
    Clock,
    AlertCircle,
    XCircle,
    Edit3,
    Trash2,
    PlayCircle,
} from 'lucide-react';

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
        return new Date(date).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return date;
    }
}

function countdownLabel(deadline: string | null): { text: string; isOverdue: boolean; isUrgent: boolean } | null {
    if (!deadline) return null;
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diffTime = deadlineDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return { text: `Terlambat ${Math.abs(diffDays)} hari`, isOverdue: true, isUrgent: false };
    }
    if (diffDays === 0) {
        return { text: 'Hari ini', isOverdue: false, isUrgent: true };
    }
    if (diffDays <= 7) {
        return { text: `${diffDays} hari lagi`, isOverdue: false, isUrgent: true };
    }
    return { text: `${diffDays} hari lagi`, isOverdue: false, isUrgent: false };
}

const actionStatusBadge: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-800',
};

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Show({ rtm, user_list, success }: Props) {
    const [activeTab, setActiveTab] = useState<'agenda' | 'notulen' | 'actions'>('agenda');

    // Action Item form
    const [showActionForm, setShowActionForm] = useState(false);
    const [actionDeskripsi, setActionDeskripsi] = useState('');
    const [actionPicId, setActionPicId] = useState('');
    const [actionDeadline, setActionDeadline] = useState('');
    const [actionProcessing, setActionProcessing] = useState(false);

    // Action edit / result
    const [editActionId, setEditActionId] = useState<number | null>(null);
    const [editActionDeskripsi, setEditActionDeskripsi] = useState('');
    const [editActionPicId, setEditActionPicId] = useState('');
    const [editActionDeadline, setEditActionDeadline] = useState('');
    const [resultInput, setResultInput] = useState<{ id: number; hasil: string } | null>(null);
    const [deleteActionId, setDeleteActionId] = useState<number | null>(null);

    const formRef = useRef<HTMLDivElement>(null);

    // ── Add action item ──
    function handleAddAction() {
        if (!actionDeskripsi.trim()) return;
        setActionProcessing(true);
        router.post(
            route('spmi.rtm.action-item.store', rtm.id),
            {
                deskripsi: actionDeskripsi,
                pic_user_id: actionPicId,
                deadline: actionDeadline,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setActionDeskripsi('');
                    setActionPicId('');
                    setActionDeadline('');
                    setShowActionForm(false);
                    setActionProcessing(false);
                },
                onError: () => setActionProcessing(false),
            }
        );
    }

    // ── Edit action item ──
    function startEditAction(item: ActionItem) {
        setEditActionId(item.id);
        setEditActionDeskripsi(item.deskripsi);
        setEditActionPicId(item.pic_user_id?.toString() || '');
        setEditActionDeadline(item.deadline || '');
    }

    function handleUpdateAction() {
        if (!editActionId || !editActionDeskripsi.trim()) return;
        setActionProcessing(true);
        router.put(
            route('spmi.rtm.action-item.update', editActionId),
            {
                deskripsi: editActionDeskripsi,
                pic_user_id: editActionPicId,
                deadline: editActionDeadline,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEditActionId(null);
                    setEditActionDeskripsi('');
                    setEditActionPicId('');
                    setEditActionDeadline('');
                    setActionProcessing(false);
                },
                onError: () => setActionProcessing(false),
            }
        );
    }

    // ── Transition action item ──
    function transitionAction(item: ActionItem, status: string) {
        router.post(
            route('spmi.rtm.action-item.transition', item.id),
            { status },
            { preserveScroll: true }
        );
    }

    // ── Save hasil (complete) ──
    function handleSaveResult() {
        if (!resultInput) return;
        setActionProcessing(true);
        router.post(
            route('spmi.rtm.action-item.transition', resultInput.id),
            { status: 'completed', hasil: resultInput.hasil },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setResultInput(null);
                    setActionProcessing(false);
                },
                onError: () => setActionProcessing(false),
            }
        );
    }

    // ── Delete action item ──
    function handleDeleteAction() {
        if (!deleteActionId) return;
        router.delete(route('spmi.rtm.action-item.destroy', deleteActionId), {
            preserveScroll: true,
            onSuccess: () => setDeleteActionId(null),
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Detail RTM — {rtm.judul}
                </h2>
            }
        >
            <Head title="Detail RTM" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                            Dashboard
                        </Link>
                        <span className="mx-2">/</span>
                        <Link href={route('spmi.rtm')} className="text-indigo-600 hover:text-indigo-900">
                            RTM
                        </Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">{rtm.judul}</span>
                    </nav>

                    <div className="mb-4">
                        <Link
                            href={route('spmi.rtm')}
                            className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke Daftar RTM
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    {/* ════════════════════════════════════════════════════════════════
                        Header Card
                        ════════════════════════════════════════════════════════════════ */}
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
                                <span
                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                        rtm.status === 'conducted'
                                            ? 'bg-green-100 text-green-800'
                                            : rtm.status === 'cancelled'
                                              ? 'bg-red-100 text-red-800'
                                              : 'bg-gray-100 text-gray-800'
                                    }`}
                                >
                                    {rtm.status
                                        ? rtm.status.charAt(0).toUpperCase() + rtm.status.slice(1)
                                        : '-'}
                                </span>
                            </div>
                        </div>

                        {rtm.file_notulen && (
                            <div className="px-6 py-3">
                                <a
                                    href={`/storage/${rtm.file_notulen}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50"
                                >
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
                                    {tab === 'agenda'
                                        ? 'Agenda'
                                        : tab === 'notulen'
                                          ? 'Notulen'
                                          : `Action Items (${rtm.actionItems.length})`}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* ════════════════════════════════════════════════════════════════
                        Tab Content
                        ════════════════════════════════════════════════════════════════ */}
                    <div className="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        {/* Agenda Tab */}
                        {activeTab === 'agenda' && (
                            <div className="p-6">
                                {rtm.agenda ? (
                                    <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                        {rtm.agenda}
                                    </p>
                                ) : (
                                    <p className="text-sm italic text-gray-400">Belum ada agenda.</p>
                                )}
                            </div>
                        )}

                        {/* Notulen Tab */}
                        {activeTab === 'notulen' && (
                            <div className="p-6">
                                {rtm.notulen ? (
                                    <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                        {rtm.notulen}
                                    </p>
                                ) : (
                                    <p className="text-sm italic text-gray-400">Belum ada notulen.</p>
                                )}
                            </div>
                        )}

                        {/* Action Items Tab */}
                        {activeTab === 'actions' && (
                            <div className="p-6">
                                {/* Add Action Item Button */}
                                {!showActionForm && (
                                    <button
                                        onClick={() => setShowActionForm(true)}
                                        className="mb-4 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        <Plus className="h-4 w-4" />
                                        Tambah Action Item
                                    </button>
                                )}

                                {/* Action Item Form */}
                                {showActionForm && (
                                    <div ref={formRef} className="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                                        <h4 className="mb-3 text-sm font-semibold text-indigo-900">Tambah Action Item Baru</h4>
                                        <div className="space-y-3">
                                            <div>
                                                <label className="mb-1 block text-xs font-medium text-gray-700">Deskripsi</label>
                                                <textarea
                                                    value={actionDeskripsi}
                                                    onChange={(e) => setActionDeskripsi(e.target.value)}
                                                    rows={2}
                                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Deskripsi action item..."
                                                />
                                            </div>
                                            <div className="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label className="mb-1 block text-xs font-medium text-gray-700">
                                                        PIC
                                                    </label>
                                                    <select
                                                        value={actionPicId}
                                                        onChange={(e) => setActionPicId(e.target.value)}
                                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                        <option value="">Pilih PIC</option>
                                                        {user_list.map((u) => (
                                                            <option key={u.id} value={u.id}>
                                                                {u.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="mb-1 block text-xs font-medium text-gray-700">
                                                        Deadline
                                                    </label>
                                                    <input
                                                        type="date"
                                                        value={actionDeadline}
                                                        onChange={(e) => setActionDeadline(e.target.value)}
                                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-end gap-2">
                                                <button
                                                    onClick={() => {
                                                        setShowActionForm(false);
                                                        setActionDeskripsi('');
                                                        setActionPicId('');
                                                        setActionDeadline('');
                                                    }}
                                                    className="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                >
                                                    Batal
                                                </button>
                                                <button
                                                    onClick={handleAddAction}
                                                    disabled={!actionDeskripsi.trim() || actionProcessing}
                                                    className="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                                >
                                                    {actionProcessing ? 'Menyimpan...' : 'Simpan'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Action Items Table */}
                                {rtm.actionItems.length === 0 ? (
                                    <p className="py-8 text-center text-sm text-gray-400">
                                        Belum ada action item.
                                    </p>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        Deskripsi
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        PIC
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        Deadline
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        Hasil
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        Status
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 bg-white">
                                                {rtm.actionItems.map((item) => {
                                                    const countdown = countdownLabel(item.deadline);
                                                    const isEditable = item.status === 'open' || item.status === 'in_progress';
                                                    const isEditing = editActionId === item.id;

                                                    return (
                                                        <tr key={item.id} className="hover:bg-gray-50">
                                                            <td className="px-4 py-4 text-sm text-gray-900">
                                                                {isEditing ? (
                                                                    <textarea
                                                                        value={editActionDeskripsi}
                                                                        onChange={(e) => setEditActionDeskripsi(e.target.value)}
                                                                        rows={2}
                                                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    />
                                                                ) : (
                                                                    <span className="whitespace-pre-wrap">{item.deskripsi}</span>
                                                                )}
                                                            </td>
                                                            <td className="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                                                {isEditing ? (
                                                                    <select
                                                                        value={editActionPicId}
                                                                        onChange={(e) => setEditActionPicId(e.target.value)}
                                                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    >
                                                                        <option value="">Pilih PIC</option>
                                                                        {user_list.map((u) => (
                                                                            <option key={u.id} value={u.id}>
                                                                                {u.name}
                                                                            </option>
                                                                        ))}
                                                                    </select>
                                                                ) : (
                                                                    item.picUser?.name || '-'
                                                                )}
                                                            </td>
                                                            <td className="whitespace-nowrap px-4 py-4 text-sm">
                                                                {isEditing ? (
                                                                    <input
                                                                        type="date"
                                                                        value={editActionDeadline}
                                                                        onChange={(e) => setEditActionDeadline(e.target.value)}
                                                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    />
                                                                ) : (
                                                                    <>
                                                                        <span className="text-gray-700">
                                                                            {item.deadline
                                                                                ? new Date(item.deadline).toLocaleDateString('id-ID', {
                                                                                      day: 'numeric',
                                                                                      month: 'short',
                                                                                      year: 'numeric',
                                                                                  })
                                                                                : '-'}
                                                                        </span>
                                                                        {countdown && (
                                                                            <span
                                                                                className={`ml-1 text-[10px] ${
                                                                                    countdown.isOverdue
                                                                                        ? 'text-red-600 font-semibold'
                                                                                        : countdown.isUrgent
                                                                                          ? 'text-orange-600 font-semibold'
                                                                                          : 'text-gray-400'
                                                                                }`}
                                                                            >
                                                                                ({countdown.text})
                                                                            </span>
                                                                        )}
                                                                    </>
                                                                )}
                                                            </td>
                                                            <td className="px-4 py-4 text-sm text-gray-700">
                                                                {resultInput?.id === item.id ? (
                                                                    <div className="flex items-center gap-1">
                                                                        <input
                                                                            type="text"
                                                                            value={resultInput.hasil}
                                                                            onChange={(e) =>
                                                                                setResultInput({ ...resultInput, hasil: e.target.value })
                                                                            }
                                                                            className="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                            placeholder="Hasil..."
                                                                        />
                                                                        <button
                                                                            onClick={handleSaveResult}
                                                                            disabled={!resultInput.hasil.trim() || actionProcessing}
                                                                            className="rounded bg-green-600 px-2 py-1 text-[10px] text-white hover:bg-green-700 disabled:opacity-50"
                                                                        >
                                                                            Simpan
                                                                        </button>
                                                                        <button
                                                                            onClick={() => setResultInput(null)}
                                                                            className="rounded bg-gray-200 px-2 py-1 text-[10px] text-gray-600 hover:bg-gray-300"
                                                                        >
                                                                            Batal
                                                                        </button>
                                                                    </div>
                                                                ) : (
                                                                    item.hasil || '-'
                                                                )}
                                                            </td>
                                                            <td className="whitespace-nowrap px-4 py-4">
                                                                <span
                                                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                                        actionStatusBadge[item.status] ||
                                                                        'bg-gray-100 text-gray-800'
                                                                    }`}
                                                                >
                                                                    {item.status === 'open'
                                                                        ? 'Open'
                                                                        : item.status === 'in_progress'
                                                                          ? 'In Progress'
                                                                          : item.status === 'completed'
                                                                            ? 'Completed'
                                                                            : item.status === 'cancelled'
                                                                              ? 'Cancelled'
                                                                              : item.status}
                                                                </span>
                                                            </td>
                                                            <td className="whitespace-nowrap px-4 py-4 text-sm">
                                                                {isEditing ? (
                                                                    <div className="flex items-center gap-1">
                                                                        <button
                                                                            onClick={handleUpdateAction}
                                                                            disabled={!editActionDeskripsi.trim() || actionProcessing}
                                                                            className="rounded bg-indigo-600 px-2 py-1 text-[10px] text-white hover:bg-indigo-700 disabled:opacity-50"
                                                                        >
                                                                            Simpan
                                                                        </button>
                                                                        <button
                                                                            onClick={() => setEditActionId(null)}
                                                                            className="rounded bg-gray-200 px-2 py-1 text-[10px] text-gray-600 hover:bg-gray-300"
                                                                        >
                                                                            Batal
                                                                        </button>
                                                                    </div>
                                                                ) : (
                                                                    <div className="flex items-center gap-1">
                                                                        {item.status === 'open' && (
                                                                            <button
                                                                                onClick={() => transitionAction(item, 'in_progress')}
                                                                                className="rounded p-1 text-yellow-600 hover:bg-yellow-50"
                                                                                title="Mulai"
                                                                            >
                                                                                <PlayCircle className="h-4 w-4" />
                                                                            </button>
                                                                        )}
                                                                        {isEditable && (
                                                                            <>
                                                                                <button
                                                                                    onClick={() => startEditAction(item)}
                                                                                    className="rounded p-1 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600"
                                                                                    title="Edit"
                                                                                >
                                                                                    <Edit3 className="h-4 w-4" />
                                                                                </button>
                                                                                {item.status === 'in_progress' && (
                                                                                    <button
                                                                                        onClick={() =>
                                                                                            setResultInput({
                                                                                                id: item.id,
                                                                                                hasil: item.hasil || '',
                                                                                            })
                                                                                        }
                                                                                        className="rounded p-1 text-green-600 hover:bg-green-50"
                                                                                        title="Selesaikan"
                                                                                    >
                                                                                        <CheckCircle2 className="h-4 w-4" />
                                                                                    </button>
                                                                                )}
                                                                            </>
                                                                        )}
                                                                        {item.status === 'open' && (
                                                                            <button
                                                                                onClick={() => setDeleteActionId(item.id)}
                                                                                className="rounded p-1 text-red-500 hover:bg-red-50"
                                                                                title="Hapus"
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </button>
                                                                        )}
                                                                        {item.status === 'in_progress' && (
                                                                            <button
                                                                                onClick={() => transitionAction(item, 'cancelled')}
                                                                                className="rounded p-1 text-gray-500 hover:bg-gray-100"
                                                                                title="Batalkan"
                                                                            >
                                                                                <XCircle className="h-4 w-4" />
                                                                            </button>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* ─── Delete Action Confirmation ─── */}
            {deleteActionId && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                        <p className="mb-4 text-sm text-gray-600">
                            Yakin ingin menghapus action item ini?
                        </p>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setDeleteActionId(null)}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleDeleteAction}
                                className="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
