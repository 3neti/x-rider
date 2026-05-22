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
        $url = data_get($config, 'url') ?? data_get($context, 'url');

        return new RiderStageData(
            type: RiderStageType::Redirect,
            enabled: (bool) data_get($config, 'enabled', filled($url)),
            key: data_get($config, 'key', 'redirect'),
            payload: [
                'url' => $url,
                'timeout' => (int) data_get($config, 'timeout', data_get($context, 'redirect_timeout', 5)),
                'fallback_url' => data_get($config, 'fallback_url', data_get($context, 'fallback_url')),
            ],
            meta: (array) data_get($config, 'meta', []),
        );
    }
}