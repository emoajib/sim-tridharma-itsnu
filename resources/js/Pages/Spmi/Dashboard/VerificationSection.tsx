import { AlertTriangle } from 'lucide-react';
import EarlyWarning from '@/Components/SPMI/EarlyWarning';

interface EarlyWarningItem {
    type: 'kritis' | 'overdue' | 'mendekat' | 'info';
    message: string;
    prodi?: string;
    days?: number;
}

interface Props {
    early_warnings?: EarlyWarningItem[];
    polling: boolean;
}

export default function VerificationSection({ early_warnings = [], polling }: Props) {
    return (
        <div className="mb-8">
            <div className="mb-4 flex items-center gap-2">
                <AlertTriangle className="h-4 w-4 text-red-500" />
                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Early Warning System</h3>
                {polling && (
                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">
                        <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
                        Live
                    </span>
                )}
            </div>
            <EarlyWarning warnings={early_warnings} />
        </div>
    );
}
