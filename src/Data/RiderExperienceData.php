<?php

namespace LBHurtado\XRider\Data;

use LBHurtado\XRider\Enums\RiderOutcomeState;
use Spatie\LaravelData\Data;

class RiderExperienceData extends Data
{
    public function __construct(
        public RiderOutcomeState|string $state,
        public RiderSubjectData $subject,
        public ?RiderContentData $preClaim = null,
        public ?RiderContentData $success = null,
        public ?RiderRedirectData $redirect = null,
        public ?RiderCampaignData $campaign = null,
        public array $ads = [],
        public array $analytics = [],
        public array $meta = [],
    ) {}

    public function normalizedState(): string
    {
        return $this->state instanceof RiderOutcomeState ? $this->state->value : $this->state;
    }

    public function riderMayRun(): bool
    {
        if ($this->state instanceof RiderOutcomeState) {
            return $this->state->riderMayRun();
        }

        return $this->state !== RiderOutcomeState::RejectedFailure->value;
    }
}
