import { useForm, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { X, Sparkles, Loader2 } from 'lucide-react';

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface PeriodeItem {
    id: number;
    nama_periode: string;
}

interface StandarItem {
    id: number;
    kode_standar: string;
    nama_standar: string;
}

interface UserItem {
    id: number;
    name: string;
}

interface AuditItem {
    id: number;
    prodi_id: number;
    periode_id: number;
    standar_mutu_id: number | null;
    judul_audit: string;
    tanggal_audit: string;
    auditor: string | null;
    temuan: string | null;
    rekomendasi: string | null;
    tindak_lanjut: string | null;
    status: string;
    severity: string | null;
    pic_user_id: number | null;
    auditor_user_id: number | null;
    deadline_tindak_lanjut: string | null;
    closed_at: string | null;
    evidence_file: string | null;
    verification_note: string | null;
    verified_by: number | null;
    verified_at: string | null;
    is_locked: boolean;
    locked_at: string | null;
    prodi?: ProdiItem;
    periode?: PeriodeItem;
    standarMutu?: StandarItem;
    picUser?: UserItem;
}

interface AuditFormModalProps {
    show: boolean;
    editing: AuditItem | null;
    prodi_list: ProdiItem[];
    periode_list: PeriodeItem[];
    standar_list: StandarItem[];
    user_list: UserItem[];
    onClose: () => void;
    onSuccess: () => void;
}

export default function AuditFormModal({
    show,
    editing,
    prodi_list,
    periode_list,
    standar_list,
    user_list,
    onClose,
    onSuccess,
}: AuditFormModalProps) {
    const [aiLoading, setAiLoading] = useState(false);
    const [standarSearch, setStandarSearch] = useState('');

    const { data, setData, post, put, errors, processing, reset } = useForm({
        prodi_id: editing?.prodi_id?.toString() || '',
        periode_id: editing?.periode_id?.toString() || '',
        judul_audit: editing?.judul_audit || '',
        tanggal_audit: editing?.tanggal_audit || '',
        auditor: editing?.auditor || '',
        standar_mutu_id: editing?.standar_mutu_id?.toString() || '',
        severity: editing?.severity || 'ringan',
        pic_user_id: editing?.pic_user_id?.toString() || '',
        deadline_tindak_lanjut: editing?.deadline_tindak_lanjut || '',
        temuan: editing?.temuan || '',
        rekomendasi: editing?.rekomendasi || '',
        evidence_file: null as File | null,
    });

    const filteredStandarList = standarSearch
        ? standar_list.filter(
              (s) =>
                  s.kode_standar.toLowerCase().includes(standarSearch.toLowerCase()) ||
                  s.nama_standar.toLowerCase().includes(standarSearch.toLowerCase())
          )
        : standar_list;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editing) {
            post(route('spmi.audit.update', editing.id), {
                forceFormData: true,
                onSuccess: () => {
                    reset();
                    onClose();
                    onSuccess();
                },
            });
        } else {
            post(route('spmi.audit.store'), {
                forceFormData: true,
                onSuccess: () => {
                    reset();
                    onClose();
                    onSuccess();
                },
            });
        }
    };

    async function handleAiResolve() {
        if (!editing) return;
        setAiLoading(true);
        try {
            const res = await fetch(route('spmi.audit.ai-resolve', editing.id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    Accept: 'application/json',
                },
            });
            const json = await res.json();
            if (json.success && json.suggestion) {
                setData('rekomendasi', json.suggestion);
            }
        } catch {
            // silent fail
        } finally {
            setAiLoading(false);
        }
    }

    if (!show) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900">
                        {editing ? 'Edit Audit Mutu' : 'Tambah Audit Mutu'}
                    </h3>
                    <button onClick={onClose} className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <form onSubmit={submit} className="max-h-[70vh] overflow-y-auto space-y-4 pr-2">
                    <div className="grid grid-cols-2 gap-4">
                        {/* Prodi */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Program Studi</label>
                            <select
                                value={data.prodi_id}
                                onChange={(e) => setData('prodi_id', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih Prodi</option>
                                {prodi_list.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.nama_prodi}
                                    </option>
                                ))}
                            </select>
                            {errors.prodi_id && <p className="mt-1 text-xs text-red-600">{errors.prodi_id}</p>}
                        </div>

                        {/* Periode */}
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
                    </div>

                    {/* Judul Audit */}
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Judul Audit</label>
                        <input
                            type="text"
                            value={data.judul_audit}
                            onChange={(e) => setData('judul_audit', e.target.value)}
                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Masukkan judul audit"
                        />
                        {errors.judul_audit && <p className="mt-1 text-xs text-red-600">{errors.judul_audit}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {/* Tanggal Audit */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Tanggal Audit</label>
                            <input
                                type="date"
                                value={data.tanggal_audit}
                                onChange={(e) => setData('tanggal_audit', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            {errors.tanggal_audit && <p className="mt-1 text-xs text-red-600">{errors.tanggal_audit}</p>}
                        </div>

                        {/* Auditor */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Auditor</label>
                            <input
                                type="text"
                                value={data.auditor}
                                onChange={(e) => setData('auditor', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Nama auditor"
                            />
                            {errors.auditor && <p className="mt-1 text-xs text-red-600">{errors.auditor}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {/* Standar Mutu */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Standar Mutu</label>
                            <input
                                type="text"
                                placeholder="Cari standar..."
                                value={standarSearch}
                                onChange={(e) => setStandarSearch(e.target.value)}
                                className="mb-1 w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <select
                                value={data.standar_mutu_id}
                                onChange={(e) => setData('standar_mutu_id', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih Standar</option>
                                {filteredStandarList.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.kode_standar} - {s.nama_standar}
                                    </option>
                                ))}
                            </select>
                            {errors.standar_mutu_id && <p className="mt-1 text-xs text-red-600">{errors.standar_mutu_id}</p>}
                        </div>

                        {/* Severity */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Severity (Tingkat Keparahan)</label>
                            <select
                                value={data.severity}
                                onChange={(e) => setData('severity', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="ringan">Ringan</option>
                                <option value="sedang">Sedang</option>
                                <option value="berat">Berat</option>
                                <option value="kritis">Kritis</option>
                            </select>
                            {errors.severity && <p className="mt-1 text-xs text-red-600">{errors.severity}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {/* PIC User */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">PIC (Penanggung Jawab)</label>
                            <select
                                value={data.pic_user_id}
                                onChange={(e) => setData('pic_user_id', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih PIC</option>
                                {user_list.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.name}
                                    </option>
                                ))}
                            </select>
                            {errors.pic_user_id && <p className="mt-1 text-xs text-red-600">{errors.pic_user_id}</p>}
                        </div>

                        {/* Deadline */}
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Deadline Tindak Lanjut</label>
                            <input
                                type="date"
                                value={data.deadline_tindak_lanjut}
                                onChange={(e) => setData('deadline_tindak_lanjut', e.target.value)}
                                className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            {errors.deadline_tindak_lanjut && (
                                <p className="mt-1 text-xs text-red-600">{errors.deadline_tindak_lanjut}</p>
                            )}
                        </div>
                    </div>

                    {/* Temuan */}
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Temuan</label>
                        <textarea
                            value={data.temuan}
                            onChange={(e) => setData('temuan', e.target.value)}
                            rows={5}
                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Deskripsi temuan audit"
                        />
                        {errors.temuan && <p className="mt-1 text-xs text-red-600">{errors.temuan}</p>}
                    </div>

                    {/* Rekomendasi + AI Button */}
                    <div>
                        <div className="mb-1 flex items-center justify-between">
                            <label className="block text-sm font-medium text-gray-700">Rekomendasi</label>
                            <button
                                type="button"
                                onClick={handleAiResolve}
                                disabled={!editing || aiLoading}
                                className="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {aiLoading ? (
                                    <Loader2 className="h-3 w-3 animate-spin" />
                                ) : (
                                    <Sparkles className="h-3 w-3" />
                                )}
                                Bantuan AI
                            </button>
                        </div>
                        <textarea
                            value={data.rekomendasi}
                            onChange={(e) => setData('rekomendasi', e.target.value)}
                            rows={3}
                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Rekomendasi tindak lanjut"
                        />
                        {errors.rekomendasi && <p className="mt-1 text-xs text-red-600">{errors.rekomendasi}</p>}
                    </div>

                    {/* File Upload */}
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">File Bukti</label>
                        <input
                            type="file"
                            onChange={(e) => {
                                const file = e.target.files?.[0] || null;
                                setData('evidence_file', file);
                            }}
                            className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        {editing?.evidence_file && (
                            <p className="mt-1 text-xs text-gray-500">
                                File saat ini: {editing.evidence_file}
                            </p>
                        )}
                        {errors.evidence_file && <p className="mt-1 text-xs text-red-600">{errors.evidence_file}</p>}
                    </div>

                    <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
