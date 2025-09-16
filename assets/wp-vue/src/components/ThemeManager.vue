<script setup lang="ts">
import { useColorMode } from "@vueuse/core";
import { Palette, Moon, Sun, Contrast } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";

const mode = useColorMode();

const props = defineProps<{
  modelValue: string;
  themes: string[];
}>();
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="link" size="icon" class="w-7 h-7 text-accent-foreground">
        <Palette />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <Card class="border-none shadow-none">
        <CardHeader class="p-1 md:p-3 pb-0">
          <CardTitle>Theme Customizer</CardTitle>
          <CardDescription>Customize your theme settings</CardDescription>
        </CardHeader>
        <CardContent class="p-1 md:p-3">
          <RadioGroup
            default-value="comfortable"
            class="flex flex-wrap gap-2 max-w-64"
            :model-value="modelValue"
          >
            <Button
              variant="outline"
              class="px-2 py-1.5 h-auto"
              v-for="theme in props.themes"
              :class="theme === modelValue ? 'border-2 border-primary' : ''"
            >
              <RadioGroupItem
                :id="`r-${theme}`"
                :value="theme"
                :class="`theme-${theme}`"
                class="bg-primary border-none text-white"
                @click="$emit('changeTheme', theme)"
              />
              <Label :for="`r-${theme}`" class="capitalize">{{ theme }}</Label>
            </Button>
          </RadioGroup>
        </CardContent>
        <CardFooter class="p-1 md:p-3 gap-2">
          <Button
            variant="outline"
            class="px-2 py-1 h-auto bg-accent border-2 border-primary dark:bg-background dark:border dark:border-input"
            @click="mode = 'light'"
          >
            Light <Sun
          /></Button>
          <Button
            variant="outline"
            class="px-2 py-1 h-auto dark:bg-accent dark:border-2 dark:border-primary"
            @click="mode = 'dark'"
          >
            Dark <Moon
          /></Button>
          <Button
            variant="outline"
            class="px-2 py-1 h-auto"
            @click="mode = 'auto'"
          >
            Auto <Contrast
          /></Button>
        </CardFooter>
      </Card>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
