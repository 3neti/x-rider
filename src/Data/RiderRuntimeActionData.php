<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderRuntimeActionData extends Data
{
    public const ALLOWED_TYPES = [
        'redirect',
        'open_url',
        'copy_to_clipboard',
        'track_event',
        'delay',
        'show_stage',
        'close',
    ];

    public const ALLOWED_TIMINGS = [
        'on_mount',
        'on_click',
        'after_delay',
        'on_complete',
    ];

    public function __construct(
        public string $type,
        public ?string $key = null,
        public string $timing = 'on_click',
        public bool $enabled = true,
        public bool $requires_user_gesture = false,
        public bool $external = false,
        public array $payload = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $type = self::normalizeType($data['type'] ?? null);
        $timing = self::normalizeTiming($data['timing'] ?? null);

        return new self(
            type: $type,
            key: isset($data['key']) ? (string) $data['key'] : null,
            timing: $timing,
            enabled: (bool) ($data['enabled'] ?? true),
            requires_user_gesture: (bool) ($data['requires_user_gesture'] ?? false),
            external: (bool) ($data['external'] ?? false),
            payload: self::normalizePayload($type, $data['payload'] ?? []),
        );
    }

    public function isExecutable(): bool
    {
        return $this->enabled
            && in_array($this->type, self::ALLOWED_TYPES, true)
            && in_array($this->timing, self::ALLOWED_TIMINGS, true);
    }

    protected static function normalizeType(mixed $type): string
    {
        $type = is_string($type) ? trim($type) : '';

        return in_array($type, self::ALLOWED_TYPES, true)
            ? $type
            : 'track_event';
    }

    protected static function normalizeTiming(mixed $timing): string
    {
        $timing = is_string($timing) ? trim($timing) : '';

        return in_array($timing, self::ALLOWED_TIMINGS, true)
            ? $timing
            : 'on_click';
    }

    protected static function normalizePayload(string $type, mixed $payload): array
    {
        $payload = is_array($payload) ? $payload : [];

        return match ($type) {
            'redirect',
            'open_url' => [
                'url' => isset($payload['url']) ? (string) $payload['url'] : '',
                'target' => isset($payload['target']) ? (string) $payload['target'] : '_blank',
                'label' => isset($payload['label']) ? (string) $payload['label'] : null,
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            'copy_to_clipboard' => [
                'text' => isset($payload['text']) ? (string) $payload['text'] : '',
                'label' => isset($payload['label']) ? (string) $payload['label'] : null,
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            'track_event' => [
                'event' => isset($payload['event']) ? (string) $payload['event'] : 'rider.runtime.action',
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            'delay' => [
                'delay_ms' => max(0, (int) ($payload['delay_ms'] ?? 0)),
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            'show_stage' => [
                'stage_key' => isset($payload['stage_key']) ? (string) $payload['stage_key'] : '',
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            'close' => [
                'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
            ],

            default => [],
        };
    }
}