<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\RiderStageResolverContract;
use LBHurtado\XRider\Data\RiderStageCollectionData;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\Support\RiderStageDriverRegistry;

class DefaultRiderStageResolver implements RiderStageResolverContract
{
    public function __construct(
        protected RiderStageDriverRegistry $drivers,
    ) {}

    public function resolve(array $rider = [], array $context = []): RiderStageCollectionData
    {
        $stages = [];

        foreach ($this->explicitStages($rider) as $stageConfig) {
            $stage = $this->makeStage($stageConfig, $context);

            if ($stage instanceof RiderStageData) {
                $stages[] = $stage;
            }
        }

        foreach ($this->legacyStages($rider) as $stageConfig) {
            $stage = $this->makeStage($stageConfig, $rider + $context);

            if ($stage instanceof RiderStageData) {
                $stages[] = $stage;
            }
        }

        return new RiderStageCollectionData(
            stages: $stages,
            meta: [
                'source' => $this->explicitStages($rider) !== [] ? 'explicit' : 'legacy',
            ],
        );
    }

    protected function explicitStages(array $rider): array
    {
        $stages = data_get($rider, 'stages', []);

        return is_array($stages) ? $stages : [];
    }

    protected function legacyStages(array $rider): array
    {
        $stages = [];

        if (filled(data_get($rider, 'message'))) {
            $stages[] = [
                'type' => RiderStageType::Message->value,
                'content' => data_get($rider, 'message'),
                'content_type' => data_get($rider, 'type'),
                'key' => 'legacy-message',
            ];
        }

        if (filled(data_get($rider, 'splash'))) {
            $stages[] = [
                'type' => RiderStageType::Splash->value,
                'content' => data_get($rider, 'splash'),
                'timeout' => data_get($rider, 'splash_timeout'),
                'key' => 'legacy-splash',
            ];
        }

        if (filled(data_get($rider, 'url'))) {
            $stages[] = [
                'type' => RiderStageType::Redirect->value,
                'url' => data_get($rider, 'url'),
                'timeout' => data_get($rider, 'redirect_timeout'),
                'fallback_url' => data_get($rider, 'fallback_url'),
                'key' => 'legacy-redirect',
            ];
        }

        return $stages;
    }

    protected function makeStage(array $stageConfig, array $context = []): ?RiderStageData
    {
        $type = (string) data_get($stageConfig, 'type');

        if ($type === '' || ! $this->drivers->has($type)) {
            return null;
        }

        return $this->drivers->get($type)->make($stageConfig, $context);
    }
}