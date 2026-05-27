import { Search, X } from 'lucide-react';

interface ProdiItem {
    id: number;
    nama_prodi: string;
}

interface UserItem {
    id: number;
    name: string;
}

interface Props {
    search: string;
    setSearch: (value: string) => void;
    statusFilter: string;
    setStatusFilter: (value: string) => void;
    prodiFilter: string;
    setProdiFilter: (value: string) => void;
    picFilter: string;
    setPicFilter: (value: string) => void;
    overdueFilter: string;
    setOverdueFilter: (value: string) => void;
    prodi_list: ProdiItem[];
    user_list: UserItem[];
}

export default function FilterBar({
    search, setSearch,
    statusFilter, setStatusFilter,
    prodiFilter, setProdiFilter,
    picFilter, setPicFilter,
    overdueFilter, setOverdueFilter,
    prodi_list, user_list,
}: Props) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex flex-wrap items-center gap-3">
                {/* Search */}
                <div className="relative">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        type="text"
                        placeholder="Cari judul temuan..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-56 rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    {search && (
                        <button
                            onClick={() => setSearch('')}
                            className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    )}
                </div>

                {/* Status Filter */}
                <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="awaiting_verification">Awaiting Verification</option>
                    <option value="verified">Verified</option>
                    <option value="closed">Closed</option>
                    <option value="rejected">Rejected</option>
                </select>

                {/* Prodi Filter */}
                <select
                    value={prodiFilter}
                    onChange={(e) => setProdiFilter(e.target.value)}
                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Prodi</option>
                    {prodi_list.map((p) => (
                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                    ))}
                </select>

                {/* PIC Filter */}
                <select
                    value={picFilter}
                    onChange={(e) => setPicFilter(e.target.value)}
                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua PIC</option>
                    {user_list.map((u) => (
                        <option key={u.id} value={u.id}>{u.name}</option>
                    ))}
                </select>

                {/* Overdue Filter */}
                <select
                    value={overdueFilter}
                    onChange={(e) => setOverdueFilter(e.target.value)}
                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Deadline</option>
                    <option value="1">Overdue Saja</option>
                </select>
            </div>
        </div>
    );
}
