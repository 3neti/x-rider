<?php

namespace LBHurtado\XRider\Services;

use Illuminate\Support\Facades\Log;
use LBHurtado\XRider\Contracts\RiderAnalyticsRecorderContract;
use LBHurtado\XRider\Data\RiderAnalyticsEventData;

class LogRiderAnalyticsRecorder implements RiderAnalyticsRecorderContract
{
    public function record(RiderAnalyticsEventData $event): void
    {
        if (! (bool) config('x-rider.analytics.enabled', true)) {
            return;
        }

        Log::channel(config('x-rider.analytics.logger', 'stack'))->info('x-rider.analytics', $event->toArray());
    }
}
