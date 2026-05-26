<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

class LinkStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Link->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $payload = (array) data_get($config, 'payload', []);

        $url = data_get($payload, 'url')
            ?? data_get($config, 'url')
            ?? data_get($context, 'url');

        $label = data_get($payload, 'label')
            ?? data_get($config, 'label')
            ?? 'Open Link';

        return new RiderStageData(
            type: RiderStageType::Link,
            enabled: (bool) data_get($config, 'enabled', filled($url)),
            key: data_get($config, 'key', 'link'),
            payload: array_filter([
                'label' => $label,
                'url' => $url,
                'presentation' => data_get($payload, 'presentation', data_get($config, 'presentation', 'inline')),
                'phase' => data_get($payload, 'phase', data_get($config, 'phase')),
            ], fn ($value) => filled($value)),
            meta: (array) data_get($config, 'meta', []),
        );
    }
}