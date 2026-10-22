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
        class="fixed inset-0 z-[60] flex h-[100svh] flex-col overflow-y-auto bg-background px-4 pt-6 pb-[max(1rem,env(safe-area-inset-bottom))] sm:px-6"
        role="dialog"
        aria-modal="true"
        :aria-label="String(activeStage?.payload?.aria_label ?? activeStage?.payload?.label ?? activeStage?.key ?? 'Rider experience')"
    >
      <div class="mx-auto flex min-h-full w-full max-w-2xl flex-1 flex-col">
        <div class="flex flex-1 flex-col justify-center py-4 text-center">
          <RiderStagePresenter :stage="activeStage" embedded />
        </div>

        <button
            type="button"
            class="mt-4 inline-flex w-full shrink-0 items-center justify-center rounded-full bg-primary px-4 py-3 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
            @click="dismiss"
        >
          {{ dismissLabel }}
        </button>
      </div>
    </section>
  </Teleport>
</template>
