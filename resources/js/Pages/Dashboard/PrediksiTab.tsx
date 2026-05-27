import { useState } from 'react';
import PrediksiWidget from '@/Components/Agent/PrediksiWidget';
import RadarChart from '@/Components/Agent/RadarChart';
import KriteriaDetailModal from '@/Components/Agent/KriteriaDetailModal';

interface Lembaga {
    id: number;
    nama_lembaga: string;
    singkatan: string;
}

interface LatestPrediction {
    id: number;
    skor_prediksi: number;
    prob_unggul: number;
    prob_baik_sekali: number;
    prob_baik: number;
    confidence_interval: string;
    created_at: string;
}

interface Props {
    latestPrediction?: LatestPrediction | null;
    kriteriaStats: any[];
    lembaga_list: Lembaga[];
    selectedInstrumenId: number;
    filters: { periode_id?: string; instrumen_id?: string };
}

export default function PrediksiTab({ latestPrediction, kriteriaStats, lembaga_list, selectedInstrumenId, filters }: Props) {
    const [selectedKriteria, setSelectedKriteria] = useState<any>(null);

    const handleSelectKriteria = (kriteria: any) => {
        setSelectedKriteria({
            ...kriteria,
            indikator: [
                { id: 1, kode: `${kriteria.kode}.1`, nama: 'Indikator 1', target: 100, tercapai: kriteria.skor, status: kriteria.skor >= 100 ? 'hijau' : kriteria.skor >= 60 ? 'kuning' : 'merah' },
                { id: 2, kode: `${kriteria.kode}.2`, nama: 'Indikator 2', target: 100, tercapai: Math.floor(kriteria.skor * 0.9), status: kriteria.skor >= 90 ? 'hijau' : kriteria.skor >= 54 ? 'kuning' : 'merah' },
                { id: 3, kode: `${kriteria.kode}.3`, nama: 'Indikator 3', target: 100, tercapai: Math.floor(kriteria.skor * 0.8), status: kriteria.skor >= 80 ? 'hijau' : kriteria.skor >= 48 ? 'kuning' : 'merah' },
            ],
        });
    };

    return (
        <>
            <div className="mb-12">
                <h3 className="mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                    Analisis Agent AI : {lembaga_list.find((l) => l.id === selectedInstrumenId)?.singkatan}
                </h3>

                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12 items-stretch">
                    <div className="lg:col-span-7">
                        <div className="h-full">
                            {latestPrediction ? (
                                <PrediksiWidget
                                    skor_prediksi={latestPrediction.skor_prediksi}
                                    prob_unggul={latestPrediction.prob_unggul}
                                    prob_baik_sekali={latestPrediction.prob_baik_sekali}
                                    prob_baik={latestPrediction.prob_baik}
                                    last_updated={latestPrediction.created_at}
                                    showRunButton={true}
                                    filters={{
                                        periode_id: filters.periode_id ? Number(filters.periode_id) : undefined,
                                        instrumen_id: filters.instrumen_id ? Number(filters.instrumen_id) : undefined,
                                    }}
                                />
                            ) : (
                                <PrediksiWidget
                                    skor_prediksi={0}
                                    prob_unggul={0}
                                    prob_baik_sekali={0}
                                    prob_baik={0}
                                    showRunButton={true}
                                    filters={{
                                        periode_id: filters.periode_id ? Number(filters.periode_id) : undefined,
                                        instrumen_id: filters.instrumen_id ? Number(filters.instrumen_id) : undefined,
                                    }}
                                />
                            )}
                        </div>
                    </div>

                    <div className="lg:col-span-5">
                        <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100 h-full flex flex-col items-center justify-center">
                            <RadarChart
                                data={kriteriaStats}
                                title="Capaian Kriteria (9 Kriteria / 4 Aspek)"
                                showTarget={true}
                                onSelectKriteria={handleSelectKriteria}
                            />
                        </div>
                    </div>
                </div>
            </div>

            <KriteriaDetailModal
                kriteria={selectedKriteria}
                isOpen={!!selectedKriteria}
                onClose={() => setSelectedKriteria(null)}
            />
        </>
    );
}
