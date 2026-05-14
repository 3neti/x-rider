<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderAnalyticsEventData extends Data
{
    public function __construct(
        public string $event,
        public string $reference,
        public ?string $sourceType = null,
        public string|int|null $sourceId = null,
        public array $context = [],
        public array $meta = [],
    ) {}
}
