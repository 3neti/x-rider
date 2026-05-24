<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage } from './types';

const props = withDefaults(defineProps<{
  stages?: RawRiderStage[] | null;
  dismissLabel?: string;
}>(), {
  dismissLabel: 'Continue',
});

const dismissedKeys = ref<Set<string>>(new Set());

const fullscreenStages = computed<RawRiderStage[]>(() =>
    (props.stages ?? []).filter((stage) => {
      const presentation = String(
          stage.payload?.presentation
          ?? stage.presentation
          ?? 'inline'
      ).trim().toLowerCase();

      return stage.enabled !== false
          && presentation === 'fullscreen'
          && ['splash', 'message', 'image', 'link', 'cta'].includes(stage.type);
    })
);

const activeStage = computed<RawRiderStage | null>(() =>
    fullscreenStages.value.find((stage, index) => {
      const key = stage.key ?? `${stage.type}-${index}`;

      return !dismissedKeys.value.has(key);
    }) ?? null
);

const activeStageKey = computed(() => {
  if (!activeStage.value) {
    return null;
  }

  const index = fullscreenStages.value.indexOf(activeStage.value);

  return activeStage.value.key ?? `${activeStage.value.type}-${index}`;
});

const visible = computed(() => !!activeStage.value);

watch(
    () => props.stages,
    () => {
      dismissedKeys.value = new Set();
    },
    { deep: true }
);

function dismiss(): void {
  if (!activeStageKey.value) {
    return;
  }

  const next = new Set(dismissedKeys.value);
  next.add(activeStageKey.value);
  dismissedKeys.value = next;
}
</script>

<template>
  <Teleport to="body">
    <section
        v-if="visible"
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
              @click="dismiss"
          >
            {{ dismissLabel }}
          </button>
        </div>
      </div>
    </section>
  </Teleport>
</template>
