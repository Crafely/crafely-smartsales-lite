<script setup lang="ts">
import { Moon, Sun } from "lucide-vue-next";
import { useColorMode } from "@vueuse/core";
import { Button } from "@/components/ui/button";
const mode = useColorMode();
</script>

<template>
  <div>
    <Button
      variant="link"
      size="icon"
      class="w-7 h-7 text-accent-foreground"
      v-if="mode == 'light'"
      @click="mode = 'dark'"
    >
      <Moon />
    </Button>
    <Button
      v-else
      variant="link"
      size="icon"
      class="w-7 h-7 text-accent-foreground"
      @click="mode = 'light'"
    >
      <Sun />
    </Button>
  </div>
</template>
