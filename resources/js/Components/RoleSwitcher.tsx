import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

const roleColors: Record<string, string> = {
    'Super Admin': 'bg-red-600',
    'Rektor': 'bg-purple-700',
    'WR 1 Akademik': 'bg-purple-500',
    'WR 2 Keuangan & Sarpras': 'bg-purple-500',
    'LPM': 'bg-blue-700',
    'Kepala LPPM': 'bg-blue-600',
    'Staf LPPM': 'bg-blue-400',
    'Kepala Kerjasama': 'bg-teal-600',
    'Staf Kerjasama': 'bg-teal-400',
    'Dekan': 'bg-indigo-600',
    'Kaprodi': 'bg-green-600',
    'Staf Prodi': 'bg-green-400',
    'Dosen': 'bg-gray-500',
    'Asesor Tamu': 'bg-orange-500',
    'Bagian Akademik': 'bg-cyan-600',
};

export default function RoleSwitcher() {
    const { auth } = usePage().props as any;
    const user = auth?.user;
    const [open, setOpen] = useState(false);

    if (!user || !user.role_list || user.role_list.length <= 1) return null;

    const activeRole = user.active_role || user.role_list[0];
    const color = roleColors[activeRole] || 'bg-gray-500';

    function switchRole(role: string) {
        setOpen(false);
        router.post(route('role.switch'), { role }, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    return (
        <div className="relative">
            <button
                onClick={() => setOpen(!open)}
                className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold text-white ${color} hover:opacity-90 transition`}
            >
                <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                {activeRole}
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div className="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                            Switch Role
                        </div>
                        <div className="py-1">
                            {user.role_list.map((role: string) => {
                                const c = roleColors[role] || 'bg-gray-500';
                                const isActive = role === activeRole;
                                return (
                                    <button
                                        key={role}
                                        onClick={() => switchRole(role)}
                                        disabled={isActive}
                                        className={`flex w-full items-center gap-2 px-4 py-2 text-sm ${
                                            isActive ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'
                                        }`}
                                    >
                                        <span className={`inline-block h-2.5 w-2.5 rounded-full ${c}`} />
                                        {role}
                                        {isActive && <span className="ml-auto text-xs text-indigo-600">&#10003;</span>}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
