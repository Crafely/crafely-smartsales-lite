import { toRefs } from 'vue'
import type { User } from '@/types'

/**
 * Composable function to check user permissions
 * @param dependency Object containing user permissions
 * @returns Object with can function to check permissions
 */
export const usePermission = (dependency: { userPermissions: User['permissions'] }) => {
  const { userPermissions } = toRefs(dependency)

  /**
   * Check if user has the required permission(s)
   * @param permission Single permission name or array of permission names
   * @returns Boolean indicating if user has all the required permissions
   */
  const can = (permission: keyof User['permissions'] | Array<keyof User['permissions']>): boolean => {
    // If permissions are not available, return false
    if (!userPermissions.value) return false

    // If permission is an array, check if user has all permissions
    if (Array.isArray(permission)) {
      return permission.every(perm => userPermissions.value?.[perm] === true)
    }

    // Check single permission
    return userPermissions.value[permission] === true
  }

  return {
    can
  }
}