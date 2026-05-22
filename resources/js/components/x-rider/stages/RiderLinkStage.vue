<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ExternalLink } from 'lucide-vue-next';
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
  <Button
      v-if="stage.enabled && href"
      as="a"
      :href="href"
      target="_blank"
      rel="noopener noreferrer"
      variant="secondary"
      class="w-full rounded-full"
  >
    {{ label }}
    <ExternalLink :size="14" class="ml-1.5" />
  </Button>
</template>