import { toRefs, computed } from "vue";
import type { User } from "@/types";
import { useRouter } from "vue-router";

type AccessibleTo = Array<User["roles"][number] | "all">;

export const useRouteVisibility = (dependency) => {
  const router = useRouter();

  const routes = router?.getRoutes().filter((route) => !route.meta?.isChild);
  const { userRoles } = toRefs(dependency);

  // const sortRouteByOrder = (routes, key: any) => {
  //     return routes.sort((a: any, b: any) => a.meta[key] - b.meta[key])
  // }

  const routesByRoles = computed(() => {
    const filtered = routes.filter((route) => {
      const accessibleTo = route.meta?.accessibleTo as AccessibleTo;
      if (!accessibleTo) {
        return false;
      }
      if (accessibleTo.includes("all")) {
        return true;
      }

      const accessableRole = accessibleTo.some((role) =>
        userRoles.value?.includes(role)
      );
      return accessableRole;
    });

    return filtered.sort((a: any, b: any) => a.meta.order - b.meta.order);

    // sortRouteByOrder(filtered, 'order')
  });

  return {
    routesByRoles,
  };
};
