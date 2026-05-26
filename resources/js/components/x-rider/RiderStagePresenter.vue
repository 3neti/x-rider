<script setup lang="ts">
import { computed } from 'vue';
import RiderRenderer from './RiderRenderer.vue';
import type { RawRiderStage, RiderPresentationMode } from './types';
import { inferStagePhase } from './useRiderStagePhase';

const props = withDefaults(defineProps<{
  stage?: RawRiderStage | null;
  force?: boolean;
}>(), {
  force: false,
});

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
      phase: inferStagePhase(props.stage),
      ...(props.stage.meta ?? {}),
    },
  };
});

const linkUrl = computed(() =>
    (props.stage?.payload?.url ?? props.stage?.url) as string | undefined
);

const linkLabel = computed(() =>
    ((props.stage?.payload?.label ?? props.stage?.label) as string | undefined)
    ?? 'Open Link'
);

const imageSrc = computed(() =>
    (props.stage?.payload?.src ?? props.stage?.src) as string | undefined
);

const imageAlt = computed(() =>
    (props.stage?.payload?.alt ?? props.stage?.alt ?? '') as string
);

const canRenderForPresentation = computed(() =>
    props.force || isInline.value
);

const shouldRenderContent = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && canRenderForPresentation.value
    && ['message', 'splash'].includes(props.stage.type)
    && !!normalizedContent.value?.content
);

const shouldRenderImage = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && canRenderForPresentation.value
    && props.stage.type === 'image'
    && !!imageSrc.value
);

const shouldRenderLink = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && canRenderForPresentation.value
    && props.stage.type === 'link'
    && !!linkUrl.value
);

const ctaAction = computed(() =>
    (props.stage?.payload?.action ?? props.stage?.action ?? 'open_url') as string
);

const ctaUrl = computed(() =>
    (props.stage?.payload?.url ?? props.stage?.url) as string | undefined
);

const ctaLabel = computed(() =>
    ((props.stage?.payload?.label ?? props.stage?.label) as string | undefined)
    ?? 'Continue'
);

const shouldRenderCta = computed(() =>
    !!props.stage
    && props.stage.enabled !== false
    && canRenderForPresentation.value
    && props.stage.type === 'cta'
    && !!ctaLabel.value
);

function handleCta(): void {
  if (ctaAction.value === 'open_url' && ctaUrl.value) {
    window.open(ctaUrl.value, '_blank', 'noopener,noreferrer');
  }
}
</script>

<template>
  <RiderRenderer
      v-if="shouldRenderContent"
      :content="normalizedContent"
  />

  <img
      v-else-if="shouldRenderImage"
      :src="imageSrc"
      :alt="imageAlt"
      class="w-full rounded-xl border object-cover"
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

  <button
      v-else-if="shouldRenderCta"
      type="button"
      class="inline-flex w-full items-center justify-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
      @click="handleCta"
  >
    {{ ctaLabel }}
  </button>
</template>
