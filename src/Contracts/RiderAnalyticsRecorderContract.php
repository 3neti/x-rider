<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderAnalyticsEventData;

interface RiderAnalyticsRecorderContract
{
    public function record(RiderAnalyticsEventData $event): void;
}
