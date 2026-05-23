<script setup lang="ts">
import { computed } from 'vue';
import RiderRenderer from './RiderRenderer.vue';
import type { RawRiderStage, RiderPresentationMode } from './types';

const props = defineProps<{
  stage?: RawRiderStage | null;
}>();

const presentation = computed<RiderPresentationMode>(() => {
  const value = props.stage?.payload?.presentation
      ?? props.stage?.presentation
      ?? 'inline';

  if (value === 'modal' || value === 'fullscreen') {
    return value;
  }

  return 'inline';
});

const isRenderable = computed(() => {
  return !!props.stage
      && props.stage.enabled !== false
      && props.stage.type !== 'redirect'
      && presentation.value === 'inline'
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
      presentation: presentation.value,
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