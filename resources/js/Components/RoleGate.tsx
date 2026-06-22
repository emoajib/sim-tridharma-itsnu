import { usePage } from '@inertiajs/react';
import { ReactNode } from 'react';

interface RoleGateProps {
    roles?: string[];
    permissions?: string[];
    fallback?: ReactNode;
    children: ReactNode;
}

export default function RoleGate({ roles, permissions, fallback = null, children }: RoleGateProps) {
    const { auth } = usePage().props as unknown as {
        auth: {
            user: {
                role_list?: string[];
                permissions?: string[];
            };
        };
    };

    if (!auth?.user) return fallback;

    if (roles && roles.length > 0) {
        const userRoles = auth.user.role_list ?? [];
        if (!roles.some(r => userRoles.includes(r))) return fallback;
    }

    if (permissions && permissions.length > 0) {
        const userPermissions = auth.user.permissions ?? [];
        if (!permissions.some(p => userPermissions.includes(p))) return fallback;
    }

    return <>{children}</>;
}
