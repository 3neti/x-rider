<?php

namespace LBHurtado\XRider\Enums;

enum RiderOutcomeState: string
{
    case AcceptedSuccess = 'accepted_success';
    case AcceptedPending = 'accepted_pending';
    case RejectedFailure = 'rejected_failure';

    public function riderMayRun(): bool
    {
        return $this !== self::RejectedFailure;
    }
}
