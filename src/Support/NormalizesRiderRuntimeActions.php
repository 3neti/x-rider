<?php

namespace LBHurtado\XRider\Support;

use LBHurtado\XRider\Data\RiderRuntimeActionData;

trait NormalizesRiderRuntimeActions
{
    /**
     * @return array<int, RiderRuntimeActionData>
     */
    protected function normalizeRuntimeActions(mixed $actions): array
    {
        if (! is_array($actions)) {
            return [];
        }

        return collect($actions)
            ->filter(fn ($action) => is_array($action))
            ->map(fn (array $action) => RiderRuntimeActionData::fromArray($action))
            ->values()
            ->all();
    }
}