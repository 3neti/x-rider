<script setup lang="ts">
import { computed } from 'vue';
import type { RiderStage } from '../types';

const props = defineProps<{
  stage: RiderStage;
}>();

const src = computed(() =>
    props.stage.payload?.src as string | undefined
);

const alt = computed(() =>
    (props.stage.payload?.alt as string | undefined) ?? ''
);

const presentation = computed(() =>
    (props.stage.payload?.presentation as string | undefined) ?? 'inline'
);
</script>

<template>
  <img
      v-if="stage.enabled && src && presentation === 'inline'"
      :src="src"
      :alt="alt"
      class="w-full rounded-xl border object-cover"
  />
</template>