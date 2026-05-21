<?php

namespace LBHurtado\XRider\Data;

use LBHurtado\XRider\Enums\RiderStageType;
use Spatie\LaravelData\Data;

class RiderStageData extends Data
{
    public function __construct(
        public RiderStageType|string $type,
        public bool $enabled = true,
        public ?string $key = null,
        public array $payload = [],
        public array $meta = [],
    ) {}

    public function normalizedType(): string
    {
        return $this->type instanceof RiderStageType
            ? $this->type->value
            : $this->type;
    }

    public function typeEnum(): ?RiderStageType
    {
        if ($this->type instanceof RiderStageType) {
            return $this->type;
        }

        return RiderStageType::tryFrom($this->type);
    }

    public function isRenderable(): bool
    {
        return $this->enabled
            && ($this->typeEnum()?->isRenderable() ?? false);
    }

    public function isRedirectLike(): bool
    {
        return $this->enabled
            && ($this->typeEnum()?->isRedirectLike() ?? false);
    }
}