<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderRedirectData extends Data
{
    public function __construct(
        public bool $enabled = false,
        public ?string $url = null,
        public int $timeout = 5,
        public ?string $fallbackUrl = null,
        public array $meta = [],
    ) {}

    public function hasUrl(): bool
    {
        return $this->enabled && filled($this->url);
    }
}
