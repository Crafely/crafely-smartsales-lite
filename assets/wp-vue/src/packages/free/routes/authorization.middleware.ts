import { useUserStore } from "@/stores/userStore";
import { useAuthMiddleware } from "@/composable/useAuthMiddleware";

export default (router) => {
  const userStore = useUserStore();
  const { checkRouteAccess } = useAuthMiddleware();

  router.beforeEach(async (to, from, next) => {
    // Skip middleware for permission deny page
    if (to.name === "permission.deny") {
      return next();
    }

    try {
      // If no auth user, try to get current user
      if (!userStore.authUser) {
        await userStore.getCurrentUser();
      }

      // Check if user has access to the route
      const hasAccess = await checkRouteAccess(to);

      if (hasAccess) {
        next();
      } else {
        next({ name: "permission.deny" });
      }
    } catch (error) {
      console.error("Authentication error:", error);
      next({ name: "permission.deny" });
    }
  });
};
