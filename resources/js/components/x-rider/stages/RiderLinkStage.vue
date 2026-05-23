<script setup lang="ts">
import { computed } from 'vue';
import type { RiderStage } from '../types';

const props = defineProps<{
  stage: RiderStage;
}>();

const label = computed(() =>
    (props.stage.payload?.label as string | undefined) ?? 'Open Link'
);

const href = computed(() =>
    props.stage.payload?.url as string | undefined
);
</script>

<template>
  <a
      v-if="stage.enabled && href"
      :href="href"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex w-full items-center justify-center rounded-full border border-primary/20 px-4 py-2 text-sm font-medium text-primary transition hover:bg-primary/5"
  >
    {{ label }}
  </a>
</template>