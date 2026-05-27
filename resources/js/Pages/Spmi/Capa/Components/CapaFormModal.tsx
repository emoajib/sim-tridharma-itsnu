import { X } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';

interface UserItem {
    id: number;
    name: string;
}

interface AuditItem {
    id: number;
    judul_audit: string;
    prodi?: { id: number; nama_prodi: string };
    standarMutu?: { id: number; kode_standar: string; nama_standar: string };
}

interface CapaItem {
    id: number;
    audit_mutu_id: number;
    pic_user_id: number | null;
    verified_by_user_id: number | null;
    root_cause_category: string | null;
    root_cause_analysis: string | null;
    corrective_action: string | null;
    corrective_deadline: string | null;
    corrective_completed_at: string | null;
    corrective_evidence_file: string | null;
    preventive_action: string | null;
    preventive_deadline: string | null;
    preventive_completed_at: string | null;
    preventive_evidence_file: string | null;
    status: string;
    verification_note: string | null;
    verified_at: string | null;
    created_at: string;
    auditMutu?: AuditItem;
    picUser?: UserItem | null;
    verifiedBy?: UserItem | null;
}

interface Props {
    show: boolean;
    editing: CapaItem | null;
    onClose: () => void;
    onSubmit: FormEventHandler;
    form: ReturnType<typeof useForm<{
        root_cause_category: string;
        root_cause_analysis: string;
        corrective_action: string;
        corrective_deadline: string;
        corrective_evidence_file: File | null;
        preventive_action: string;
        preventive_deadline: string;
        preventive_evidence_file: File | null;
    }>>;
}

export default function CapaFormModal({ show, editing, onClose, onSubmit, form }: Props) {
    if (!show || !editing) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900">Edit CAPA #{editing.id}</h3>
                    <button onClick={onClose} className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <form onSubmit={onSubmit} className="max-h-[70vh] overflow-y-auto space-y-4 pr-2">
                    {/* Root Cause Category */}
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Kategori Root Cause</label>
                        <select
                            value={form.data.root_cause_category}
                            onChange={(e) => form.setData('root_cause_category', e.target.value)}
                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Pilih Kategori</option>
                            <option value="sdm">SDM</option>
                            <option value="proses">Proses</option>
                            <option value="sarana">Sarana Prasarana</option>
                            <option value="keuangan">Keuangan</option>
                            <option value="kurikulum">Kurikulum</option>
                            <option value="organisasi">Organisasi</option>
                            <option value="eksternal">Eksternal</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    {/* Root Cause Analysis */}
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Analisis Akar Masalah (Root Cause Analysis)</label>
                        <textarea
                            value={form.data.root_cause_analysis}
                            onChange={(e) => form.setData('root_cause_analysis', e.target.value)}
                            rows={4}
                            className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Jelaskan analisis akar masalah..."
                        />
                        {form.errors.root_cause_analysis && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.root_cause_analysis}</p>
                        )}
                    </div>

                    {/* Corrective Action */}
                    <div className="border-t border-gray-100 pt-4">
                        <h4 className="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">Corrective Action (Tindakan Korektif)</h4>
                        <div className="space-y-3">
                            <div>
                                <textarea
                                    value={form.data.corrective_action}
                                    onChange={(e) => form.setData('corrective_action', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Deskripsi tindakan korektif..."
                                />
                                {form.errors.corrective_action && (
                                    <p className="mt-1 text-xs text-red-600">{form.errors.corrective_action}</p>
                                )}
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="mb-1 block text-xs font-medium text-gray-600">Deadline</label>
                                    <input
                                        type="date"
                                        value={form.data.corrective_deadline}
                                        onChange={(e) => form.setData('corrective_deadline', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-medium text-gray-600">File Bukti</label>
                                    <input
                                        type="file"
                                        onChange={(e) => {
                                            const file = e.target.files?.[0] || null;
                                            form.setData('corrective_evidence_file', file);
                                        }}
                                        className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                    {editing.corrective_evidence_file && (
                                        <p className="mt-1 text-xs text-gray-400">File saat ini: {editing.corrective_evidence_file}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Preventive Action */}
                    <div className="border-t border-gray-100 pt-4">
                        <h4 className="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">Preventive Action (Tindakan Preventif)</h4>
                        <div className="space-y-3">
                            <div>
                                <textarea
                                    value={form.data.preventive_action}
                                    onChange={(e) => form.setData('preventive_action', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Deskripsi tindakan preventif..."
                                />
                                {form.errors.preventive_action && (
                                    <p className="mt-1 text-xs text-red-600">{form.errors.preventive_action}</p>
                                )}
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="mb-1 block text-xs font-medium text-gray-600">Deadline</label>
                                    <input
                                        type="date"
                                        value={form.data.preventive_deadline}
                                        onChange={(e) => form.setData('preventive_deadline', e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-medium text-gray-600">File Bukti</label>
                                    <input
                                        type="file"
                                        onChange={(e) => {
                                            const file = e.target.files?.[0] || null;
                                            form.setData('preventive_evidence_file', file);
                                        }}
                                        className="w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                    {editing.preventive_evidence_file && (
                                        <p className="mt-1 text-xs text-gray-400">File saat ini: {editing.preventive_evidence_file}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <button type="button" onClick={onClose} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            {form.processing ? 'Menyimpan...' : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
