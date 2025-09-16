<script setup lang="ts">
import { onMounted } from "vue";
import { useUserStore } from "@/stores/userStore";
import { storeToRefs } from "pinia";
import MainNav from "@/components/MainNav.vue";
import UserNav from "@/components/UserNav.vue";
import MobileMenu from "@/components/MobileMenu.vue";
import { useAppStore } from "@/stores/appStore";
import MoodSelector from "@/components/MoodSelector.vue";
import ThemeManager from "@/components/ThemeManager.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";

const appStore = useAppStore();
const { themes, setTheme } = appStore;
const { activeTheme } = storeToRefs(appStore);

defineProps<{
  activeRouteName: string;
}>();

const { menuItems, authUser } = storeToRefs(useUserStore());

onMounted(() => {
  appStore.getTheme();
});
</script>

<template>
  <div class="min-h-screen">
    <div class="border-b sticky top-0 z-50 bg-background">
      <div class="flex h-14 items-center px-2">
        <MobileMenu :activeRouteName="activeRouteName" :routes="menuItems" />
        <MainNav :activeRouteName="activeRouteName" :routes="menuItems" />
        <div class="ml-auto flex items-center space-x-2 md:space-x-4">
          <ThemeManager
            v-model="activeTheme"
            :themes="themes"
            @changeTheme="setTheme"
          />
          <MoodSelector />
          <UserNav :user="authUser" />
        </div>
      </div>
    </div>
    <ScrollToTop />
    <slot />
  </div>
</template>
