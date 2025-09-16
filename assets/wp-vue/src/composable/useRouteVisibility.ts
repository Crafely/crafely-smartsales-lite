import { toRefs, computed } from "vue";
import type { User } from "@/types";
import { freeRoutes } from "@/packages/free/routes/index";
import { flatMapDeep } from "lodash";

type AccessibleTo = Array<User["roles"][number] | "all">;

export const useRouteVisibility = (dependency) => {
  const { userRoles } = toRefs(dependency);

  const flatten = (arr, key = "children") =>
    flatMapDeep(arr, (item) => [
      item,
      ...(item[key] ? flatten(item[key], key) : []),
    ]);

  const routes = flatten(freeRoutes).filter((route) => route.meta);

  // const sortRouteByOrder = (routes, key: any) => {
  //     return routes.sort((a: any, b: any) => a.meta[key] - b.meta[key])
  // }

  const routesByRoles = computed(() => {
    const filtered = routes?.filter((route) => {
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

    return filtered?.sort((a: any, b: any) => a.meta.order - b.meta.order);

    // sortRouteByOrder(filtered, 'order')
  });

  const menuItems = computed(() => {
    return routesByRoles.value.filter((route) => !route.meta.isChild);
  });

  return {
    routesByRoles,
    menuItems,
  };
};
