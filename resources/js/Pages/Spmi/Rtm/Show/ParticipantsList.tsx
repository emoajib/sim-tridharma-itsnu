import { User, Calendar } from 'lucide-react';

interface UserItem {
    id: number;
    name: string;
}

interface Props {
    pimpinan?: UserItem | null;
    tanggalRapat: string | null;
    status?: string | null;
    formatDate: (date: string | null) => string;
}

export default function ParticipantsList({ pimpinan, tanggalRapat, status, formatDate }: Props) {
    return (
        <div className="px-6 py-4 border-b border-gray-100 bg-gray-50/30">
            <h4 className="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Informasi Rapat</h4>
            <div className="flex flex-wrap items-center gap-6 text-sm text-gray-600">
                <div className="flex items-center gap-1.5">
                    <Calendar className="h-4 w-4 text-gray-400" />
                    <span className="font-medium">{formatDate(tanggalRapat)}</span>
                </div>
                <div className="flex items-center gap-1.5">
                    <User className="h-4 w-4 text-gray-400" />
                    <span>Pimpinan: <span className="font-semibold text-gray-900">{pimpinan?.name || '-'}</span></span>
                </div>
                {status && (
                    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                        status === 'conducted' ? 'bg-green-100 text-green-800' :
                        status === 'cancelled' ? 'bg-red-100 text-red-800' :
                        'bg-gray-100 text-gray-800'
                    }`}>
                        {status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                )}
            </div>
        </div>
    );
}
