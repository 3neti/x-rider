<?php

namespace LBHurtado\XRider\Data;

use LBHurtado\XRider\Enums\RiderContentType;
use Spatie\LaravelData\Data;

class RiderContentData extends Data
{
    public function __construct(
        public bool $enabled = true,
        public RiderContentType|string $type = RiderContentType::Markdown,
        public ?string $content = null,
        public array $meta = [],
    ) {}

    public function normalizedType(): string
    {
        return $this->type instanceof RiderContentType ? $this->type->value : $this->type;
    }
}
