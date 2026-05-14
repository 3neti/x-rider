<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderSubjectData extends Data
{
    public function __construct(
        public string $type,
        public string|int|null $id = null,
        public ?string $code = null,
        public array $meta = [],
    ) {}

    public function reference(): string
    {
        return collect([
            $this->type,
            $this->id,
            $this->code,
        ])
            ->filter(fn ($value) => filled($value))
            ->implode(':');
    }
}