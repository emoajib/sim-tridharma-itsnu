import { Link } from '@inertiajs/react';

interface Props {
    portofolioStats: {
        pendidikan_count: number;
        penelitian_count: number;
        publikasi_count: number;
        pkm_count: number;
        penunjang_count: number;
        bkd_count: number;
        bimbingan_count: number;
        dokumen_count: number;
    };
    isTheme3: boolean;
}

function StatCard({ label, value, color, href, isTheme3 }: { label: string; value: number | string; color: string; href?: string; isTheme3?: boolean }) {
    if (isTheme3) {
        const card3 = (
            <div className="kpi-card">
                <p className="kpi-label">{label}</p>
                <p className="kpi-value">{value}</p>
            </div>
        );
        return href ? <Link href={href} className="block transition hover:scale-105">{card3}</Link> : card3;
    }

    const card = (
        <div className="rounded-xl bg-white p-6 shadow-sm border border-gray-100 group hover:border-indigo-200 transition-all">
            <p className="text-sm font-bold text-gray-400 uppercase tracking-widest">{label}</p>
            <p className="mt-2 text-4xl font-black text-gray-800">{value}</p>
            <div className={`mt-4 h-1 w-12 rounded-full ${color}`}></div>
        </div>
    );
    return href ? <Link href={href} className="block transition-transform hover:-translate-y-1">{card}</Link> : card;
}

export default function KinerjaTab({ portofolioStats, isTheme3 }: Props) {
    return (
        <div className="mb-12">
            <div className="flex items-center gap-2 mb-4 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                <span>▼ Akumulasi Kinerja Tridharma</span>
            </div>
            <div className={isTheme3 ? 'kpi-grid' : 'grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8'}>
                <StatCard label="Pendidikan" value={portofolioStats.pendidikan_count} color="bg-blue-400" href={route('portofolio.pendidikan')} isTheme3={isTheme3} />
                <StatCard label="Penelitian" value={portofolioStats.penelitian_count} color="bg-emerald-400" href={route('portofolio.penelitian')} isTheme3={isTheme3} />
                <StatCard label="Publikasi" value={portofolioStats.publikasi_count} color="bg-purple-400" href={route('portofolio.publikasi')} isTheme3={isTheme3} />
                <StatCard label="PKM" value={portofolioStats.pkm_count} color="bg-orange-400" href={route('portofolio.pkm')} isTheme3={isTheme3} />
                <StatCard label="Penunjang" value={portofolioStats.penunjang_count} color="bg-teal-400" href={route('portofolio.penunjang')} isTheme3={isTheme3} />
                <StatCard label="BKD" value={portofolioStats.bkd_count} color="bg-rose-400" href={route('bkd')} isTheme3={isTheme3} />
                <StatCard label="Bimbingan" value={portofolioStats.bimbingan_count} color="bg-cyan-400" href={route('bimbingan')} isTheme3={isTheme3} />
                <StatCard label="Dokumen" value={portofolioStats.dokumen_count} color="bg-amber-400" href={route('dokumen')} isTheme3={isTheme3} />
            </div>
        </div>
    );
}
