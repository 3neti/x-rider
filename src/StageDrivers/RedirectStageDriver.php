<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

class RedirectStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Redirect->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $payload = (array) data_get($config, 'payload', []);

        $url = data_get($payload, 'url')
            ?? data_get($config, 'url')
            ?? data_get($context, 'url');

        $timeout = data_get($payload, 'timeout')
            ?? data_get($config, 'timeout')
            ?? data_get($context, 'redirect_timeout');

        $fallbackUrl = data_get($payload, 'fallback_url')
            ?? data_get($config, 'fallback_url')
            ?? data_get($context, 'fallback_url');

        $external = data_get($payload, 'external')
            ?? data_get($config, 'external');

        $normalizedPayload = [
            'url' => $url,
            'timeout' => $timeout,
            'fallback_url' => $fallbackUrl,
            'phase' => data_get($payload, 'phase', data_get($config, 'phase')),
        ];

        if (is_bool($external)) {
            $normalizedPayload['external'] = $external;
        }

        return new RiderStageData(
            type: RiderStageType::Redirect,
            enabled: (bool) data_get($config, 'enabled', filled($url)),
            key: data_get($config, 'key', 'redirect'),
            payload: $normalizedPayload,
            meta: (array) data_get($config, 'meta', []),
        );
    }
}