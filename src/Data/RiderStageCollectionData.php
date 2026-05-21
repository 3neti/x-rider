<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderStageCollectionData extends Data
{
    /**
     * @param array<int, RiderStageData> $stages
     */
    public function __construct(
        public array $stages = [],
        public array $meta = [],
    ) {}

    public static function fromArray(array $stages, array $meta = []): self
    {
        return new self(
            stages: collect($stages)
                ->map(fn (RiderStageData|array $stage): RiderStageData => $stage instanceof RiderStageData
                    ? $stage
                    : new RiderStageData(
                        type: data_get($stage, 'type'),
                        enabled: (bool) data_get($stage, 'enabled', true),
                        key: data_get($stage, 'key'),
                        payload: (array) data_get($stage, 'payload', []),
                        meta: (array) data_get($stage, 'meta', []),
                    ))
                ->values()
                ->all(),
            meta: $meta,
        );
    }

    public function enabled(): array
    {
        return collect($this->stages)
            ->filter(fn (RiderStageData $stage): bool => $stage->enabled)
            ->values()
            ->all();
    }

    public function renderable(): array
    {
        return collect($this->stages)
            ->filter(fn (RiderStageData $stage): bool => $stage->isRenderable())
            ->values()
            ->all();
    }

    public function redirectLike(): array
    {
        return collect($this->stages)
            ->filter(fn (RiderStageData $stage): bool => $stage->isRedirectLike())
            ->values()
            ->all();
    }

    public function firstOfType(string $type): ?RiderStageData
    {
        return collect($this->stages)
            ->first(fn (RiderStageData $stage): bool => $stage->normalizedType() === $type);
    }
}