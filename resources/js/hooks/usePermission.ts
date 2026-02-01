import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export function usePermission() {
    const { auth } = usePage<SharedData>().props;

    const can = (permission: string): boolean => {
        if (!auth?.permissions) return false;
        return auth.permissions.includes(permission);
    };

    const hasRole = (role: string): boolean => {
        if (!auth?.roles) return false;
        return auth.roles.includes(role);
    };

    const hasAnyPermission = (permissions: string[]): boolean => {
        if (!auth?.permissions) return false;
        return permissions.some((permission) =>
            auth.permissions.includes(permission),
        );
    };

    return { can, hasRole, hasAnyPermission };
}
