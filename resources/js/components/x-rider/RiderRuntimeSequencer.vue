<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage, RiderRuntimeAction } from './types';
import { useRiderRuntimeActions } from './useRiderRuntimeActions';

interface Props {
  stages?: RawRiderStage[];
}

const props = withDefaults(defineProps<Props>(), {
  stages: () => [],
});

const visibleStageKeys = ref<string[]>([]);
const executedActionKeys = ref<string[]>([]);

const runtime = useRiderRuntimeActions({
  onShowStage: (stageKey: string) => {
    if (!visibleStageKeys.value.includes(stageKey)) {
      visibleStageKeys.value.push(stageKey);
    }
  },
  onTrackEvent: (event: string, meta?: Record<string, unknown>) => {
    console.debug('[x-rider] runtime event', event, meta ?? {});
  },
  onError: (error: unknown, action: RiderRuntimeAction) => {
    console.warn('[x-rider] runtime action failed', action, error);
  },
});

const enabledStages = computed<RawRiderStage[]>(() =>
    props.stages.filter((stage) => stage.enabled !== false)
);

const visibleStages = computed<RawRiderStage[]>(() =>
    enabledStages.value.filter((stage, index) => {
      const key = stage.key ?? `${stage.type}-${index}`;

      return visibleStageKeys.value.includes(key);
    })
);

function stageKey(stage: RawRiderStage, index: number): string {
  return stage.key ?? `${stage.type}-${index}`;
}

function actionKey(
    stage: RawRiderStage,
    action: RiderRuntimeAction,
    stageIndex: number,
    actionIndex: number
): string {
  return action.key ?? `${stageKey(stage, stageIndex)}:${action.type}:${actionIndex}`;
}

function hasExecuted(key: string): boolean {
  return executedActionKeys.value.includes(key);
}

function markExecuted(key: string): void {
  if (!hasExecuted(key)) {
    executedActionKeys.value.push(key);
  }
}

async function executeStageActions(
    stage: RawRiderStage,
    stageIndex: number,
    timing: 'on_mount' | 'after_delay' | 'on_complete'
): Promise<void> {
  const actions = runtime.actionsForTiming(stage.actions, timing);

  for (let actionIndex = 0; actionIndex < actions.length; actionIndex += 1) {
    const action = actions[actionIndex];
    const key = actionKey(stage, action, stageIndex, actionIndex);

    if (hasExecuted(key)) {
      continue;
    }

    markExecuted(key);

    await runtime.execute(action);
  }
}

async function runStage(stage: RawRiderStage, stageIndex: number): Promise<void> {
  const key = stageKey(stage, stageIndex);

  if (!visibleStageKeys.value.includes(key)) {
    visibleStageKeys.value.push(key);
  }

  await executeStageActions(stage, stageIndex, 'on_mount');
  await executeStageActions(stage, stageIndex, 'after_delay');
  await executeStageActions(stage, stageIndex, 'on_complete');
}

async function runSequence(): Promise<void> {
  for (let index = 0; index < enabledStages.value.length; index += 1) {
    await runStage(enabledStages.value[index], index);
  }
}

onMounted(() => {
  void runSequence();
});
</script>

<template>
  <div
      v-if="visibleStages.length"
      class="space-y-3"
  >
    <RiderStagePresenter
        v-for="(stage, index) in visibleStages"
        :key="stage.key ?? `${stage.type}-${index}`"
        :stage="stage"
    />
  </div>
</template>