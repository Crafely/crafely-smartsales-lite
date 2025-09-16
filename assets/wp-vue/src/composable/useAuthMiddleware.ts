import { computed } from "vue";
import { useUserStore } from "@/stores/userStore";
import { useRouteVisibility } from "@/composable/useRouteVisibility";
import type { RouteLocationNormalized } from "vue-router";

/**
 * Authentication middleware composable
 * Checks if user is authenticated and authorized for specific routes
 * @returns Object with checkRouteAccess function
 */
export const useAuthMiddleware = () => {
  const userStore = useUserStore();
  /**
   * Check if the current route is accessible to the user
   * @param to The target route the user is navigating to
   * @returns Boolean indicating if user can access the route
   */
  const checkRouteAccess = async (
    to: RouteLocationNormalized
  ): Promise<boolean> => {
    // If no authenticated user, try to get current user
    if (!userStore.authUser) {
      try {
        await userStore.getCurrentUser();
      } catch (error) {
        console.error("Failed to get current user:", error);
        return false;
      }
    }

    // If still no authenticated user after trying to get current user, deny access
    if (!userStore.authUser) {
      return false;
    }
    const { routesByRoles } = useRouteVisibility({
      userRoles: computed(() => userStore.authUser?.roles || []),
    });
    // Get route name without parameters
    const routeName = to.name?.toString() || "";
    // Check if route exists in routesByRoles (routes user is allowed to access)
    const isRouteAllowed = routesByRoles.value?.some((route) => {
      // Check main route
      if (route.name === routeName) {
        return true;
      }
      // Check submenus if they exist
      if (route?.children) {
        return route?.children?.some((children) => children.name === routeName);
      }

      return false;
    });

    return isRouteAllowed;
  };

  return {
    checkRouteAccess,
  };
};
