<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import { useUserStore } from "@/stores/userStore";
import { storeToRefs } from "pinia";
import MainNav from "@/components/MainNav.vue";
import UserNav from "@/components/UserNav.vue";
import { DropdownMenuItem } from "@/components/ui/dropdown-menu";
import { LayoutIcon } from "@radix-icons/vue";
import MobileMenu from "@/components/MobileMenu.vue";
import {
  ResizableHandle,
  ResizablePanel,
  ResizablePanelGroup,
} from "@/components/ui/resizable";
import { useAppStore } from "@/stores/appStore";
import MoodSelector from "@/components/MoodSelector.vue";
import ThemeManager from "@/components/ThemeManager.vue";
import ScrollToTop from "@/components/ScrollToTop.vue";
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetClose,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { ArrowLeft, ArrowRight } from "lucide-vue-next";

const appStore = useAppStore();
const { themes, setTheme } = appStore;
const { activeTheme } = storeToRefs(appStore);

withDefaults(
  defineProps<{
    activeRouteName: string;
    hideSidebar?: boolean;
  }>(),
  {
    hideSidebar: false,
  }
);
const panelRef = ref<InstanceType<typeof ResizablePanel>>();
const { routesByRoles } = storeToRefs(useUserStore());

const userStore = useUserStore();
const { authUser } = storeToRefs(userStore);

const handleCollapse = () => {
  panelRef.value?.isCollapsed
    ? panelRef.value?.expand()
    : panelRef.value?.collapse();
};

const isSmallScreen = computed(
  () => window.matchMedia("(max-width: 767px)").matches
);

onMounted(() => {
  appStore.getTheme();
});
</script>

<template>
  <div class="min-h-screen">
    <div class="border-b sticky top-0 z-50 bg-background">
      <div class="flex h-14 items-center px-2">
        <MobileMenu
          :activeRouteName="activeRouteName"
          :routes="routesByRoles"
        />
        <MainNav :activeRouteName="activeRouteName" :routes="routesByRoles" />
        <div class="ml-auto flex items-center space-x-2">
          <div
            v-if="!hideSidebar"
            class="hidden md:flex items-center p-2 text-text"
          >
            <button @click="handleCollapse">
              <component :is="LayoutIcon" />
            </button>
          </div>

          <ThemeManager
            v-model="activeTheme"
            :themes="themes"
            @changeTheme="setTheme"
          />
          <MoodSelector />

          <UserNav :user="authUser">
            <router-link :to="{ name: 'app.settings' }" class="cursor-pointer">
              <DropdownMenuItem class="cursor-pointer">
                Settings
              </DropdownMenuItem>
            </router-link>
            <DropdownMenuItem @click="userStore.logout" class="cursor-pointer">
              Log out
              <!-- <DropdownMenuShortcut>⇧⌘Q</DropdownMenuShortcut> -->
            </DropdownMenuItem>
          </UserNav>
        </div>
      </div>
    </div>
    <ResizablePanelGroup direction="horizontal">
      <ResizablePanel :style="{ zoom: isSmallScreen ? 0.8 : 1 }">
        <ScrollToTop /><slot />
      </ResizablePanel>
      <ResizableHandle class="hidden md:flex" v-if="!hideSidebar" with-handle />
      <ResizablePanel
        v-if="!hideSidebar"
        :default-size="30"
        collapsible
        :minSize="28"
        ref="panelRef"
        class="hidden md:block"
      >
        <slot name="sidebar" />
      </ResizablePanel>

      <div
        v-if="!hideSidebar"
        class="flex md:hidden items-center justify-between relative"
      >
        <Sheet>
          <SheetTrigger
            v-if="!hideSidebar"
            class="fixed right-1 top-1/2 translate-y-1/2 z-50 md:hidden"
          >
            <Button variant="outline" size="icon" class="rounded-full"
              ><ArrowLeft /></Button
          ></SheetTrigger>
          <SheetContent
            class="w-[90%] [&>button:nth-last-of-type(1)]:top-2 [&>button:nth-last-of-type(1)]:right-3"
            side="right"
          >
            <ResizablePanel
              class="absolute top-0 left-0 h-full w-full"
              v-if="!hideSidebar"
              ref="panelRef"
            >
              <slot name="sidebar" />
            </ResizablePanel>
            <SheetClose
              class="absolute -left-3 top-1/2 -translate-y-1/2 rounded-full z-50 md:hidden"
            >
              <Button variant="outline" size="icon" class="rounded-full"
                ><ArrowRight
              /></Button>
            </SheetClose>
          </SheetContent>
        </Sheet>
      </div>
    </ResizablePanelGroup>
  </div>
</template>
