<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage } from './types';
import RiderRedirectRuntime from '@/components/x-rider/RiderRedirectRuntime.vue';

const props = withDefaults(defineProps<{
  stages?: RawRiderStage[] | null;
  continueLabel?: string;
}>(), {
  continueLabel: 'Continue',
});

const currentIndex = ref(0);

const runtimeStages = computed<RawRiderStage[]>(() =>
    (props.stages ?? []).filter((stage) => {
      const presentation = String(
          stage.payload?.presentation
          ?? stage.presentation
          ?? 'inline'
      ).trim().toLowerCase();

      return stage.enabled !== false
          && ['modal', 'fullscreen'].includes(presentation)
          && ['splash', 'message', 'image', 'link', 'cta'].includes(stage.type);
    })
);

const activeStage = computed<RawRiderStage | null>(() =>
    runtimeStages.value[currentIndex.value] ?? null
);

const activePresentation = computed(() => {
  const value = activeStage.value?.payload?.presentation
      ?? activeStage.value?.presentation
      ?? 'inline';

  return String(value).trim().toLowerCase();
});

const visible = computed(() => !!activeStage.value);

const isModal = computed(() => activePresentation.value === 'modal');
const isFullscreen = computed(() => activePresentation.value === 'fullscreen');

const redirectStage = computed<RawRiderStage | null>(() => {
  const redirects = (props.stages ?? []).filter((stage) =>
      stage.enabled !== false
      && stage.type === 'redirect'
  );

  return redirects.length > 0
      ? redirects[redirects.length - 1]
      : null;
});

const sequenceComplete = computed(() =>
    currentIndex.value >= runtimeStages.value.length
);

watch(
    () => props.stages,
    () => {
      currentIndex.value = 0;
    },
    { deep: true }
);

function proceed(): void {
  currentIndex.value += 1;
}
</script>

<template>
  <Teleport to="body">
    <!-- Modal presentation -->
    <div
        v-if="visible && isModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 px-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
    >
      <div class="w-full max-w-md rounded-2xl border bg-card p-5 shadow-lg">
        <div class="space-y-5">
          <RiderStagePresenter :stage="activeStage" force />

          <button
              type="button"
              class="inline-flex w-full items-center justify-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
              @click="proceed"
          >
            {{ continueLabel }}
          </button>
        </div>
      </div>
    </div>

    <!-- Fullscreen presentation -->
    <section
        v-else-if="visible && isFullscreen"
        class="fixed inset-0 z-[60] flex min-h-screen items-center justify-center bg-background px-6 py-10"
        role="dialog"
        aria-modal="true"
    >
      <div class="mx-auto flex h-full w-full max-w-2xl flex-col justify-center">
        <div class="space-y-8 text-center">
          <RiderStagePresenter :stage="activeStage" force />

          <button
              type="button"
              class="inline-flex w-full items-center justify-center rounded-full bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition hover:bg-primary/90 sm:w-auto sm:px-8"
              @click="proceed"
          >
            {{ continueLabel }}
          </button>
        </div>
      </div>
    </section>
  </Teleport>
  <RiderRedirectRuntime
      v-if="sequenceComplete && redirectStage"
      :stage="redirectStage"
  />
</template>
