import { AlertTriangle, Clock, CheckCircle2, TrendingUp } from 'lucide-react';
import KpiCard from '@/Components/SPMI/KpiCard';

interface Overview {
    total_temuan: number;
    open_temuan: number;
    in_progress_temuan: number;
    closed_temuan: number;
    close_rate: number;
    skor_mutu: number;
    capa_overdue_count: number;
    capa_approaching_count: number;
}

interface Props {
    overview: Overview;
}

export default function StatsCards({ overview }: Props) {
    return (
        <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <KpiCard title="Total Temuan" value={overview.total_temuan} icon={<AlertTriangle className="h-5 w-5" />} color="blue" />
            <KpiCard title="Open Temuan" value={overview.open_temuan} icon={<Clock className="h-5 w-5" />} color="yellow" />
            <KpiCard
                title="Close Rate"
                value={`${overview.close_rate}%`}
                icon={<CheckCircle2 className="h-5 w-5" />}
                color="green"
                trend={{ value: overview.close_rate, direction: overview.close_rate >= 70 ? 'up' : overview.close_rate >= 40 ? 'flat' : 'down' }}
            />
            <KpiCard title="Skor Mutu" value={overview.skor_mutu.toFixed(2)} icon={<TrendingUp className="h-5 w-5" />} color="purple" />
            <KpiCard title="CAPA Overdue" value={overview.capa_overdue_count} icon={<AlertTriangle className="h-5 w-5" />} color="red" />
            <KpiCard title="CAPA Mendekat" value={overview.capa_approaching_count} icon={<Clock className="h-5 w-5" />} color="yellow" />
        </div>
    );
}
