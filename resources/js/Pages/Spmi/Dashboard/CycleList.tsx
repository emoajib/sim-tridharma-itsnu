import { Link } from '@inertiajs/react';
import StatusBadge from '@/Components/SPMI/StatusBadge';

interface PpeppStage {
    key: string;
    label: string;
    count: number;
    percentage: number;
    icon: string;
    color: string;
}

interface PpeppProgress {
    stages: PpeppStage[];
    total_audits: number;
}

interface Props {
    ppepp: PpeppProgress;
}

const PPEPP_KEY_MAP: Record<string, { letter: string }> = {
    penetapan: { letter: 'P' },
    pelaksanaan: { letter: 'P' },
    evaluasi: { letter: 'E' },
    pengendalian: { letter: 'P' },
    peningkatan: { letter: 'P' },
};

export default function CycleList({ ppepp }: Props) {
    return (
        <div className="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Siklus PPEPP</h3>
                <Link
                    href={route('spmi.standar-mutu')}
                    className="text-[10px] font-bold uppercase tracking-widest text-indigo-600 underline decoration-indigo-200 underline-offset-4 hover:text-indigo-800"
                >
                    Lihat Detail
                </Link>
            </div>
            <div className="grid grid-cols-1 gap-4 p-6 sm:grid-cols-5">
                {ppepp.stages.map((stage) => {
                    const stageMeta = PPEPP_KEY_MAP[stage.key] || { letter: stage.key.charAt(0).toUpperCase() };
                    return (
                        <div key={stage.key} className="flex flex-col items-center rounded-lg border border-gray-100 bg-gray-50/50 p-4 text-center transition-all hover:shadow-sm">
                            <div className="mb-2 flex h-10 w-10 items-center justify-center rounded-full text-white" style={{ backgroundColor: stage.color }}>
                                <span className="text-sm font-black">{stageMeta.letter}</span>
                            </div>
                            <p className="text-[10px] font-bold uppercase tracking-widest text-gray-500">{stage.label}</p>
                            <p className="mt-1 text-2xl font-black text-gray-900 tabular-nums">{stage.percentage}%</p>
                            <StatusBadge
                                status={stage.percentage >= 80 ? 'verified' : stage.percentage >= 50 ? 'in_progress' : 'draft'}
                                workflowType="audit"
                                size="sm"
                            />
                            <div className="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                <div className="h-full rounded-full transition-all duration-500" style={{ width: `${stage.percentage}%`, backgroundColor: stage.color }} />
                            </div>
                            <p className="mt-1 text-[10px] font-medium text-gray-400">{stage.count} item</p>
                        </div>
                    );
                })}
            </div>
            <div className="border-t border-gray-100 px-6 py-3 text-center text-[10px] font-medium text-gray-400">
                Total Audit: {ppepp.total_audits} item tersebar di 5 tahap PPEPP
            </div>
        </div>
    );
}
