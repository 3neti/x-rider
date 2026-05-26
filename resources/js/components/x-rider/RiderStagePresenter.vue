<script setup lang="ts">
import { computed, ref } from 'vue';
import RiderRenderer from './RiderRenderer.vue';
import type { RawRiderStage } from './types';

interface Props {
  stage: RawRiderStage;
}

const props = defineProps<Props>();

const dismissed = ref(false);

const presentation = computed(() =>
    String(
        props.stage.payload?.presentation
        ?? props.stage.presentation
        ?? 'inline'
    ).trim().toLowerCase()
);

const isModal = computed(() => presentation.value === 'modal');
const isFullscreen = computed(() => presentation.value === 'fullscreen');

const label = computed(() =>
    String(props.stage.payload?.label ?? 'Continue')
);

const url = computed(() =>
    String(props.stage.payload?.url ?? props.stage.src ?? '')
);

const imageSrc = computed(() =>
    String(props.stage.src ?? props.stage.payload?.src ?? '')
);

const imageAlt = computed(() =>
    String(props.stage.alt ?? props.stage.payload?.alt ?? 'Rider image')
);

const stageContent = computed(() => ({
  enabled: props.stage.enabled !== false,
  type: props.stage.content_type ?? 'markdown',
  content: props.stage.content ?? '',
}));

function dismiss(): void {
  dismissed.value = true;
}
</script>

<template>
  <template v-if="!dismissed">
    <div
        :class="{
                'fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4': isModal,
                'fixed inset-0 z-50 flex items-center justify-center bg-background px-6': isFullscreen,
            }"
    >
      <div
          :class="{
                    'w-full max-w-md rounded-2xl bg-background p-5 shadow-xl': isModal,
                    'mx-auto w-full max-w-lg space-y-6 text-center': isFullscreen,
                    'space-y-3': !isModal && !isFullscreen,
                }"
      >
        <RiderRenderer
            v-if="stage.content"
            :content="stageContent"
        />

        <img
            v-else-if="stage.type === 'image' && imageSrc"
            :src="imageSrc"
            :alt="imageAlt"
            class="w-full rounded-xl object-cover"
        />

        <a
            v-else-if="stage.type === 'link' && url"
            :href="url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex text-sm font-medium text-primary underline"
        >
          {{ label }}
        </a>

        <a
            v-else-if="stage.type === 'cta' && url"
            :href="url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
        >
          {{ label }}
        </a>

        <button
            v-if="isModal || isFullscreen"
            type="button"
            class="w-full rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            @click="dismiss"
        >
          Continue
        </button>
      </div>
    </div>
  </template>
</template>