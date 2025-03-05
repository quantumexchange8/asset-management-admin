import { useDark, useToggle } from '@vueuse/core'
import { reactive } from 'vue'
import {usePage} from "@inertiajs/vue3";

export const isDark = useDark({disableTransition: false})
export const toggleDarkMode = useToggle(isDark)

export const sidebarState = reactive({
    isOpen: window.innerWidth > 1024,

    handleWindowResize() {
        if (window.innerWidth <= 1024) {
            sidebarState.isOpen = false
        } else {
            sidebarState.isOpen = true
        }
    },
})

export function usePermission() {
    const userRoles = usePage().props.auth.user.roles.map(role => role.name);
    const rawPermissions = usePage().props.permissions || [];

    const userPermissions = Array.isArray(rawPermissions)
        ? rawPermissions
        : flattenPermissions(rawPermissions);

    function flattenPermissions(permissions) {
        return Object.values(permissions) // Get all categories
            .flatMap(types => Object.values(types)) // Get all types
            .flatMap(permissionArray => permissionArray.map(p => p.name)); // Extract names
    }

    const hasAccess = (items, accessList) => {
        const flatItems = Array.isArray(items) ? items.flat() : [items];
        return flatItems.some(item => accessList.includes(item));
    };

    const hasRole = (role) => hasAccess(role, userRoles);
    const hasPermission = (permission) => hasAccess(permission, userPermissions);

    return { hasRole, hasPermission, userPermissions };
}
