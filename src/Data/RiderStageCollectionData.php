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

    public static function fromArray(array $data): self
    {
        $stageRows = array_is_list($data)
            ? $data
            : ($data['stages'] ?? []);

        return new self(
            stages: collect($stageRows)
                ->filter(fn ($stage) => is_array($stage))
                ->map(fn (array $stage) => RiderStageData::fromArray($stage))
                ->values()
                ->all(),

            meta: array_is_list($data)
                ? []
                : (is_array($data['meta'] ?? null) ? $data['meta'] : []),
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