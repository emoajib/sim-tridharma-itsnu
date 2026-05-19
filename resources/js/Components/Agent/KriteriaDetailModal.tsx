import { Link } from '@inertiajs/react';
import { useState } from 'react';

interface IndikatorItem {
    id: number;
    kode: string;
    nama: string;
    target: number;
    tercapai: number;
    status: 'hijau' | 'kuning' | 'merah';
    bukti?: string;
}

interface KriteriaItem {
    kode: string;
    nama: string;
    skor: number;
    target: number;
    indikator: IndikatorItem[];
}

interface Props {
    kriteria: KriteriaItem | null;
    isOpen: boolean;
    onClose: () => void;
}

export default function KriteriaDetailModal({ kriteria, isOpen, onClose }: Props) {
    if (!isOpen || !kriteria) return null;

    const statusColors = {
        hijau: 'bg-green-100 text-green-800',
        kuning: 'bg-yellow-100 text-yellow-800',
        merah: 'bg-red-100 text-red-800',
    };

    const totalIndikator = kriteria.indikator.length;
    const terpenuhi = kriteria.indikator.filter(i => i.status === 'hijau').length;
    const hampir = kriteria.indikator.filter(i => i.status === 'kuning').length;
    const kurang = kriteria.indikator.filter(i => i.status === 'merah').length;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
                <div className="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 className="text-xl font-bold text-white">{kriteria.kode} - {kriteria.nama}</h2>
                        <p className="text-indigo-200 text-sm">Detail Capaian dan Indikator</p>
                    </div>
                    <button
                        onClick={onClose}
                        className="text-white hover:text-indigo-200 transition-colors"
                    >
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div className="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div className="grid grid-cols-4 gap-4 mb-6">
                        <div className="text-center p-3 bg-gray-50 rounded-lg">
                            <div className="text-2xl font-bold text-gray-900">{totalIndikator}</div>
                            <div className="text-xs text-gray-500">Total</div>
                        </div>
                        <div className="text-center p-3 bg-green-50 rounded-lg">
                            <div className="text-2xl font-bold text-green-600">{terpenuhi}</div>
                            <div className="text-xs text-green-600">Terpenuhi</div>
                        </div>
                        <div className="text-center p-3 bg-yellow-50 rounded-lg">
                            <div className="text-2xl font-bold text-yellow-600">{hampir}</div>
                            <div className="text-xs text-yellow-600">Hampir</div>
                        </div>
                        <div className="text-center p-3 bg-red-50 rounded-lg">
                            <div className="text-2xl font-bold text-red-600">{kurang}</div>
                            <div className="text-xs text-red-600">Kurang</div>
                        </div>
                    </div>

                    <div className="mb-4">
                        <div className="flex justify-between items-center mb-2">
                            <span className="text-sm font-medium text-gray-700">Skor Capaian</span>
                            <span className="text-lg font-bold text-indigo-600">{kriteria.skor}%</span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-3">
                            <div
                                className="bg-indigo-600 h-3 rounded-full transition-all"
                                style={{ width: `${kriteria.skor}%` }}
                            />
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Target: {kriteria.target}%</div>
                    </div>

                    <div className="border-t border-gray-200 pt-4">
                        <h3 className="font-semibold text-gray-900 mb-3">Daftar Indikator</h3>
                        <div className="space-y-2">
                            {kriteria.indikator.map((ind) => (
                                <div key={ind.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs font-mono bg-gray-200 px-2 py-0.5 rounded">{ind.kode}</span>
                                            <span className="text-sm font-medium text-gray-900">{ind.nama}</span>
                                        </div>
                                        <div className="text-xs text-gray-500 mt-1">
                                            Target: {ind.target} | Tercapai: {ind.tercapai}
                                        </div>
                                    </div>
                                    <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusColors[ind.status]}`}>
                                        {ind.status === 'hijau' ? 'Terpenuhi' : ind.status === 'kuning' ? 'Hampir' : 'Kurang'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {kurang > 0 && (
                        <div className="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                            <h4 className="font-semibold text-red-800 mb-2">Tindakan yang Diperlukan:</h4>
                            <ul className="text-sm text-red-700 space-y-1">
                                {kriteria.indikator
                                    .filter(i => i.status === 'merah')
                                    .map(i => (
                                        <li key={i.id}>• Selesaikan indikator {i.kode}: {i.nama}</li>
                                    ))}
                            </ul>
                        </div>
                    )}
                </div>

                <div className="bg-gray-50 px-6 py-3 flex justify-end gap-2">
                    <button
                        onClick={onClose}
                        className="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Tutup
                    </button>
                    <Link
                        href="/verifikasi"
                        className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                    >
                        Lihat di Verifikasi
                    </Link>
                </div>
            </div>
        </div>
    );
}