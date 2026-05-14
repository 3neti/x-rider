<?php

namespace LBHurtado\XRider\Data;

use Spatie\LaravelData\Data;

class RiderCampaignData extends Data
{
    public function __construct(
        public ?string $id = null,
        public ?string $merchant = null,
        public array $tags = [],
        public array $meta = [],
    ) {}
}
