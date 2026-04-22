import { usePage } from '@inertiajs/react';

/**
 * Hook lấy quyền của user cho một màn hình cụ thể.
 * @param {string} screenCode - Mã màn hình (ví dụ: 'thiet-bi', 'co-so')
 * @returns {{ can_view: boolean, can_create: boolean, can_edit: boolean, can_delete: boolean, can_regenerate_qr: boolean, can_import: boolean, can_export: boolean }}
 */
export default function usePermission(screenCode) {
    const { userPermissions } = usePage().props;
    const perm = (userPermissions ?? {})[screenCode] ?? {};
    return {
        can_view:          perm.can_view          ?? false,
        can_create:        perm.can_create        ?? false,
        can_edit:          perm.can_edit          ?? false,
        can_delete:        perm.can_delete        ?? false,
        can_regenerate_qr: perm.can_regenerate_qr ?? false,
        can_import:        perm.can_import        ?? false,
        can_export:        perm.can_export        ?? false,
    };
}
