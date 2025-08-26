<script setup lang="ts">
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
  navigationMenuTriggerStyle,
} from "@/components/ui/navigation-menu";

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

defineProps<{
  activeRouteName: String;
  routes: any[];
}>();
</script>

<template>
  <NavigationMenu>
    <NavigationMenuList>
      <template v-for="route in routes" :key="route.path">
        <DropdownMenu v-if="route.children?.length">
          <DropdownMenuTrigger
            :class="[
              'text-sm font-medium transition-all hover:opacity-100 px-4 py-2 ',
              activeRouteName === route.name
                ? 'text-primary opacity-100'
                : 'opacity-60',
            ]"
            >{{ route.meta?.title }}</DropdownMenuTrigger
          >
          <DropdownMenuContent>
            <template v-for="child in route.children" :key="child.path">
              <router-link :to="{ name: child.name }" class="cursor-pointer">
                <DropdownMenuItem>{{ child.meta?.title }}</DropdownMenuItem>
              </router-link>
            </template>
          </DropdownMenuContent>
        </DropdownMenu>

        <NavigationMenuItem v-else>
          <NavigationMenuLink
            :class="navigationMenuTriggerStyle()"
            class="hover:bg-background"
          >
            <router-link
              :to="{ name: route.name }"
              class="text-sm font-medium transition-all hover:opacity-100"
              :class="[
                activeRouteName === route.name
                  ? 'text-primary opacity-100'
                  : 'opacity-60',
              ]"
            >
              {{ route.meta?.title }}
            </router-link>
          </NavigationMenuLink>
        </NavigationMenuItem>
      </template>
    </NavigationMenuList>
  </NavigationMenu>
</template>
