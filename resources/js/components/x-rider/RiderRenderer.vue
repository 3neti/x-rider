<script setup lang="ts">
import { computed } from 'vue';

type RenderableRiderContent = {
  enabled?: boolean | null;
  type?: string | null;
  content?: unknown;
  meta?: Record<string, unknown>;
};

const props = defineProps<{
  content?: RenderableRiderContent | null;
}>();

const text = computed(() => typeof props.content?.content === 'string' ? props.content.content : '');
const enabled = computed(() =>
    props.content?.enabled !== false && text.value !== ''
);

const type = computed(() => props.content?.type ?? 'markdown');

const canRenderHtml = computed(() =>
        type.value === 'html'
        && (
            props.content?.meta?.sanitized === true
            || props.content?.meta?.trusted_html === true
        )
);
</script>

<template>
  <article
      v-if="enabled"
      class="prose prose-sm mx-auto max-w-none text-center dark:prose-invert"
  >
    <div
        v-if="canRenderHtml"
        v-html="text"
    />

    <p
        v-else
        class="whitespace-pre-line"
    >
      {{ text }}
    </p>
  </article>
</template>
