<script setup lang="ts">
import { computed } from 'vue';
import RiderRenderer from './RiderRenderer.vue';

interface RiderStage {
  type: string;
  enabled?: boolean;
  key?: string | null;
  payload?: Record<string, unknown>;
  meta?: Record<string, unknown>;
}

const props = defineProps<{
  stage?: RiderStage | null;
}>();

const isRenderable = computed(() => {
  return !!props.stage
      && props.stage.enabled !== false
      && ['message', 'splash', 'link', 'image'].includes(props.stage.type);
});

const normalizedContent = computed(() => {
  if (!props.stage) {
    return null;
  }

  return {
    enabled: props.stage.enabled !== false,
    type: (
        props.stage.payload?.content_type
        ?? props.stage.content_type
        ?? 'markdown'
    ) as string,
    content: (
        props.stage.payload?.content
        ?? props.stage.content
        ?? null
    ) as string | null,
    meta: {
      stage_key: props.stage.key,
      stage_type: props.stage.type,
      ...(props.stage.meta ?? {}),
    },
  };
});
</script>

<template>
  <RiderRenderer
      v-if="isRenderable"
      :content="normalizedContent"
  />
</template>