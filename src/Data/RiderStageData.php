<?php

namespace LBHurtado\XRider\Data;

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\Support\NormalizesRiderRuntimeActions;
use Spatie\LaravelData\Data;

class RiderStageData extends Data
{
    use NormalizesRiderRuntimeActions;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     * @param array<int, RiderRuntimeActionData> $actions
     */
    public function __construct(
        public RiderStageType|string $type,
        public bool $enabled = true,
        public ?string $key = null,
        public ?string $phase = null,
        public ?string $presentation = null,
        public ?string $content = null,
        public ?string $content_type = null,
        public int|string|null $timeout = null,
        public ?string $action = null,
        public ?string $label = null,
        public ?string $url = null,
        public ?string $src = null,
        public ?string $alt = null,
        public array $payload = [],
        public array $meta = [],
        public array $actions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: RiderStageType::tryFrom((string) ($data['type'] ?? 'message'))
            ?? (string) ($data['type'] ?? 'message'),

            enabled: (bool) ($data['enabled'] ?? true),
            key: isset($data['key']) ? (string) $data['key'] : null,

            phase: isset($data['phase']) ? (string) $data['phase'] : null,
            presentation: isset($data['presentation']) ? (string) $data['presentation'] : null,

            content: isset($data['content']) ? (string) $data['content'] : null,
            content_type: isset($data['content_type']) ? (string) $data['content_type'] : null,
            timeout: $data['timeout'] ?? null,

            action: isset($data['action']) ? (string) $data['action'] : null,
            label: isset($data['label']) ? (string) $data['label'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,

            src: isset($data['src']) ? (string) $data['src'] : null,
            alt: isset($data['alt']) ? (string) $data['alt'] : null,

            payload: is_array($data['payload'] ?? null) ? $data['payload'] : [],
            meta: is_array($data['meta'] ?? null) ? $data['meta'] : [],

            actions: (new self(type: 'message'))->normalizeRuntimeActions($data['actions'] ?? []),
        );
    }

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