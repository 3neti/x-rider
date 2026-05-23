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

const isInline = computed(() => presentation.value === 'inline');

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

const linkUrl = computed(() =>
    props.stage?.payload?.url as string | undefined
);

const linkLabel = computed(() =>
    (props.stage?.payload?.label as string | undefined)
    ?? 'Open Link'
);

const shouldRenderContent = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && isInline.value
    && ['message', 'splash', 'image'].includes(props.stage.type)
    && !!normalizedContent.value?.content
);

const shouldRenderLink = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && isInline.value
    && props.stage.type === 'link'
    && !!linkUrl.value
);
</script>

<template>
  <RiderRenderer
      v-if="shouldRenderContent"
      :content="normalizedContent"
  />

  <a
      v-else-if="shouldRenderLink"
      :href="linkUrl"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex w-full items-center justify-center rounded-full border border-primary/20 px-4 py-2 text-sm font-medium text-primary transition hover:bg-primary/5"
  >
    {{ linkLabel }}
  </a>
</template>