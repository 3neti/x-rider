<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderRuntimeActionData extends Data
{
    public function __construct(
        public string $type,
        public ?string $key = null,
        public ?string $timing = 'on_click',
        public bool $enabled = true,
        public bool $requires_user_gesture = false,
        public bool $external = false,
        public array $payload = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) ($data['type'] ?? ''),
            key: $data['key'] ?? null,
            timing: $data['timing'] ?? 'on_click',
            enabled: (bool) ($data['enabled'] ?? true),
            requires_user_gesture: (bool) ($data['requires_user_gesture'] ?? false),
            external: (bool) ($data['external'] ?? false),
            payload: is_array($data['payload'] ?? null) ? $data['payload'] : [],
        );
    }
}