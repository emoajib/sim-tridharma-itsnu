import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState, useEffect, FormEventHandler } from 'react';
import { Search, X, Plus, Eye, Copy, CheckCircle2, BarChart3, ExternalLink } from 'lucide-react';

// ─── Types ────────────────────────────────────────────────────────────────────

interface PeriodeItem {
    id: number;
    nama_periode: string;
}

interface SurveyItem {
    id: number;
    periode_id: number;
    responden_type: string;
    jumlah_responden: number;
    skor_rata_rata: number | null;
    token: string | null;
    link_survey: string | null;
    responses: any[] | null;
    tanggal_diisi: string | null;
    created_at: string;
    periode?: PeriodeItem;
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

interface Filters {
    periode_id: string;
    responden_type: string;
}

interface Props {
    surveys: PaginatedData<SurveyItem>;
    periode_list: PeriodeItem[];
    filters: Filters;
    success?: string;
    errors?: Record<string, string>;
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(date: string | null): string {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return date;
    }
}

const respondenTypeLabels: Record<string, string> = {
    mahasiswa: 'Mahasiswa',
    dosen: 'Dosen',
    alumni: 'Alumni',
    pengguna_lulusan: 'Pengguna Lulusan',
};

const respondenTypeColors: Record<string, string> = {
    mahasiswa: 'bg-blue-100 text-blue-800',
    dosen: 'bg-green-100 text-green-800',
    alumni: 'bg-purple-100 text-purple-800',
    pengguna_lulusan: 'bg-orange-100 text-orange-800',
};

function skorColor(skor: number | null): string {
    if (skor === null) return 'text-gray-400';
    if (skor >= 4) return 'text-green-600 font-semibold';
    if (skor >= 3) return 'text-yellow-600 font-semibold';
    return 'text-red-600 font-semibold';
}

// ─── Component ─────────────────────────────────────────────────────────────────

export default function Index({ surveys, periode_list, filters, success }: Props) {
    // ── Filter state ──
    const [periodeFilter, setPeriodeFilter] = useState(filters.periode_id || '');
    const [respondenTypeFilter, setRespondenTypeFilter] = useState(filters.responden_type || '');

    // ── Modal state ──
    const [showModal, setShowModal] = useState(false);
    const [showResponses, setShowResponses] = useState<SurveyItem | null>(null);
    const [copiedToken, setCopiedToken] = useState<number | null>(null);

    // ── Form ──
    const { data, setData, post, errors, processing, reset } = useForm({
        periode_id: '',
        responden_type: 'mahasiswa',
    });

    // ── Debounced filter ──
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route('spmi.survey'),
                {
                    periode_id: periodeFilter,
                    responden_type: respondenTypeFilter,
                },
                { preserveState: true, replace: true }
            );
        }, 500);
        return () => clearTimeout(timer);
    }, [periodeFilter, respondenTypeFilter]);

    // ── Modal handlers ──
    function openCreate() {
        reset();
        setData('responden_type', 'mahasiswa');
        setShowModal(true);
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('spmi.survey.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            },
        });
    };

    function handleCopyToken(item: SurveyItem) {
        if (item.link_survey) {
            navigator.clipboard.writeText(item.link_survey).then(() => {
                setCopiedToken(item.id);
                setTimeout(() => setCopiedToken(null), 2000);
            }).catch(() => {
                // fallback: copy token only
                if (item.token) {
                    navigator.clipboard.writeText(item.token).then(() => {
                        setCopiedToken(item.id);
                        setTimeout(() => setCopiedToken(null), 2000);
                    });
                }
            });
        } else if (item.token) {
            navigator.clipboard.writeText(item.token).then(() => {
                setCopiedToken(item.id);
                setTimeout(() => setCopiedToken(null), 2000);
            });
        }
    }

    function renderResponses(item: SurveyItem) {
        if (!item.responses || item.responses.length === 0) {
            return <p className="text-sm text-gray-400">Belum ada respons.</p>;
        }
        return (
            <div className="max-h-64 overflow-y-auto space-y-2">
                {item.responses.map((resp: any, idx: number) => (
                    <div key={idx} className="rounded-lg border border-gray-100 bg-gray-50 p-3">
                        <p className="text-xs text-gray-500 mb-1">Respons #{idx + 1}</p>
                        <pre className="whitespace-pre-wrap text-xs text-gray-700">
                            {typeof resp === 'string' ? resp : JSON.stringify(resp, null, 2)}
                        </pre>
                    </div>
                ))}
            </div>
        );
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Survey SPMI</h2>}
        >
            <Head title="Survey SPMI" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="text-indigo-600 hover:text-indigo-900">
                            Dashboard
                        </Link>
                        <span className="mx-2">/</span>
                        <span className="text-indigo-600 hover:text-indigo-900">SPMI</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Survey</span>
                    </nav>

                    <div className="mb-4">
                        <Link href={route('spmi.dashboard')} className="text-sm text-indigo-600 hover:text-indigo-900">
                            &larr; Kembali ke Dashboard SPMI
                        </Link>
                    </div>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    {/* Main Card */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {/* Filter Bar */}
                        <div className="border-b border-gray-200 p-6">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div className="flex flex-wrap items-center gap-3">
                                    {/* Periode Filter */}
                                    <select
                                        value={periodeFilter}
                                        onChange={(e) => setPeriodeFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Periode</option>
                                        {periode_list.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.nama_periode}
                                            </option>
                                        ))}
                                    </select>

                                    {/* Responden Type Filter */}
                                    <select
                                        value={respondenTypeFilter}
                                        onChange={(e) => setRespondenTypeFilter(e.target.value)}
                                        className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Semua Responden</option>
                                        <option value="mahasiswa">Mahasiswa</option>
                                        <option value="dosen">Dosen</option>
                                        <option value="alumni">Alumni</option>
                                        <option value="pengguna_lulusan">Pengguna Lulusan</option>
                                    </select>
                                </div>

                                <button
                                    onClick={openCreate}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Buat Survey
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Periode
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tipe Responden
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Jumlah Responden
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Skor Rata-rata
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal Diisi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Token / Link
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {surveys.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-12 text-center text-sm text-gray-500">
                                                Tidak ada data survey.
                                            </td>
                                        </tr>
                                    ) : (
                                        surveys.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-gray-50">
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.periode?.nama_periode || '-'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                            respondenTypeColors[item.responden_type] ||
                                                            'bg-gray-100 text-gray-800'
                                                        }`}
                                                    >
                                                        {respondenTypeLabels[item.responden_type] || item.responden_type}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {item.jumlah_responden}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <span className={skorColor(item.skor_rata_rata)}>
                                                        {item.skor_rata_rata !== null
                                                            ? item.skor_rata_rata.toFixed(2)
                                                            : '-'}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                    {formatDate(item.tanggal_diisi)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <div className="flex items-center gap-1">
                                                        {item.link_survey ? (
                                                            <span className="inline-flex max-w-[140px] truncate text-xs text-indigo-600">
                                                                {item.link_survey}
                                                            </span>
                                                        ) : item.token ? (
                                                            <span className="inline-flex rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700">
                                                                {item.token}
                                                            </span>
                                                        ) : (
                                                            <span className="text-xs text-gray-400">-</span>
                                                        )}
                                                        {(item.token || item.link_survey) && (
                                                            <button
                                                                onClick={() => handleCopyToken(item)}
                                                                className="rounded p-1 text-gray-400 hover:text-indigo-600"
                                                                title="Salin Link/Token"
                                                            >
                                                                {copiedToken === item.id ? (
                                                                    <CheckCircle2 className="h-3.5 w-3.5 text-green-500" />
                                                                ) : (
                                                                    <Copy className="h-3.5 w-3.5" />
                                                                )}
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                    <div className="flex items-center gap-1">
                                                        <button
                                                            onClick={() => setShowResponses(item)}
                                                            className="rounded p-1.5 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600"
                                                            title="Lihat Respons"
                                                        >
                                                            <BarChart3 className="h-4 w-4" />
                                                        </button>
                                                        {item.link_survey && (
                                                            <a
                                                                href={item.link_survey}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="rounded p-1.5 text-gray-500 hover:bg-green-50 hover:text-green-600"
                                                                title="Buka Survey"
                                                            >
                                                                <ExternalLink className="h-4 w-4" />
                                                            </a>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {surveys.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                                <div className="text-sm text-gray-700">
                                    Menampilkan {surveys.from} - {surveys.to} dari {surveys.total}
                                </div>
                                <div className="flex gap-1">
                                    {surveys.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => {
                                                if (link.url) router.get(link.url, {}, { preserveState: true, replace: true });
                                            }}
                                            className={`rounded px-3 py-1 text-sm ${
                                                link.active
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-white text-gray-700 hover:bg-gray-100'
                                            } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* ─── Create Survey Modal ─── */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Buat Survey Baru</h3>
                            <button
                                onClick={() => {
                                    setShowModal(false);
                                    reset();
                                }}
                                className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                                <select
                                    value={data.periode_id}
                                    onChange={(e) => setData('periode_id', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Periode</option>
                                    {periode_list.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.nama_periode}
                                        </option>
                                    ))}
                                </select>
                                {errors.periode_id && <p className="mt-1 text-xs text-red-600">{errors.periode_id}</p>}
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-gray-700">Tipe Responden</label>
                                <select
                                    value={data.responden_type}
                                    onChange={(e) => setData('responden_type', e.target.value)}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="dosen">Dosen</option>
                                    <option value="alumni">Alumni</option>
                                    <option value="pengguna_lulusan">Pengguna Lulusan</option>
                                </select>
                                {errors.responden_type && (
                                    <p className="mt-1 text-xs text-red-600">{errors.responden_type}</p>
                                )}
                            </div>

                            <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowModal(false);
                                        reset();
                                    }}
                                    className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {processing ? 'Menyimpan...' : 'Buat Survey'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* ─── View Responses Modal ─── */}
            {showResponses && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Respons Survey
                                <span className="ml-2 text-sm font-normal text-gray-500">
                                    — {respondenTypeLabels[showResponses.responden_type] || showResponses.responden_type}
                                </span>
                            </h3>
                            <button
                                onClick={() => setShowResponses(null)}
                                className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="mb-4 flex items-center gap-4 text-sm text-gray-600">
                            <span>Jumlah: <strong>{showResponses.jumlah_responden}</strong></span>
                            <span>
                                Skor Rata-rata:{' '}
                                <strong className={skorColor(showResponses.skor_rata_rata)}>
                                    {showResponses.skor_rata_rata !== null
                                        ? showResponses.skor_rata_rata.toFixed(2)
                                        : '-'}
                                </strong>
                            </span>
                        </div>

                        {renderResponses(showResponses)}
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
