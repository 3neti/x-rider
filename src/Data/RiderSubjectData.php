<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderSubjectData extends Data
{
    public function __construct(
        public string $reference,
        public ?string $sourceType = null,
        public string|int|null $sourceId = null,
        public ?string $code = null,
        public array $payload = [],
        public array $meta = [],
    ) {}
}
