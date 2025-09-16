<script setup lang="ts">
import { ref } from "vue";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { PlusIcon, MinusIcon } from "@radix-icons/vue";
import { formatAmount } from "@/utils";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  aspectRatio: {
    type: String,
    default: "portrait",
  },
  width: {
    type: Number || null,
    default: 300,
  },
  height: {
    type: Number || null,
    default: 300,
  },
  increment: {
    type: Function,
    required: true,
  },
  decrement: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div :class="cn('space-y-3 group relative', $attrs.class ?? '')">
    <div class="overflow-hidden rounded-md relative">
      <!-- src="https://picsum.photos/300/300" -->
      {{ product.cart }}
      <img
        :src="product?.image_url || '/01.jpeg'"
        :alt="product.name"
        :width="width"
        :height="height"
        :class="
          cn(
            'h-auto w-auto object-cover transition-all group-hover:scale-105',
            aspectRatio === 'portrait' ? 'aspect-[3/4]' : 'aspect-square'
          )
        "
      />
      <!-- Add overlay -->
      <div
        class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
      >
        <div class="flex items-center gap-3">
          <Button
            variant="secondary"
            size="icon"
            class="h-8 w-8 rounded-full"
            @click="decrement(product)"
          >
            <MinusIcon class="h-4 w-4" />
          </Button>
          <Button
            variant="secondary"
            size="icon"
            class="h-8 w-8 rounded-full"
            @click="increment(product)"
          >
            <PlusIcon class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
    <div class="space-y-3 text-sm">
      <h3 class="font-medium leading-none text-center w-full">
        {{ product.name }}
      </h3>
      <div class="flex flex-col text-center gap-y-1">
        <div class="text-muted-foreground text-xs">
          Stock {{ product.stock || "out" }}
        </div>
        <div class="flex gap-x-1 justify-center items-center">
          <p class="font-bold text-lg">
            {{ formatAmount(product.price, product.currency) }}
          </p>
          <sub
            v-if="product.sale_price"
            class="text-xs line-through text-muted-foreground"
            >{{ formatAmount(product.sale_price, product.currency) }}</sub
          >
        </div>
      </div>
    </div>
  </div>
</template>
