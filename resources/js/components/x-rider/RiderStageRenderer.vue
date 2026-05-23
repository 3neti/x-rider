<script setup lang="ts">
import { computed } from 'vue';
import RiderMessageStage from '@/components/x-rider/stages/RiderMessageStage.vue';
import RiderSplashStage from '@/components/x-rider/stages/RiderSplashStage.vue';
import RiderLinkStage from '@/components/x-rider/stages/RiderLinkStage.vue';
import RiderImageStage from '@/components/x-rider/stages/RiderImageStage.vue';
import type { RiderStage } from './types';

const props = defineProps<{
  stages?: RiderStage[] | null;
}>();

const enabledStages = computed(() =>
    (props.stages ?? []).filter((stage) => stage.enabled)
);

const componentFor = (stage: RiderStage) => {
  switch (stage.type) {
    case 'message':
      return RiderMessageStage;
    case 'splash':
      return RiderSplashStage;
    case 'link':
      return RiderLinkStage;
    case 'image':
      return RiderImageStage;
    default:
      return null;
  }
};
</script>

<template>
  <div v-if="enabledStages.length" class="space-y-4">
    <template
        v-for="stage in enabledStages"
        :key="stage.key ?? `${stage.type}-${enabledStages.indexOf(stage)}`"
    >
      <component
          :is="componentFor(stage)"
          v-if="componentFor(stage)"
          :stage="stage"
      />
    </template>
  </div>
</template>