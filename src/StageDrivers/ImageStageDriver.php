<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

class ImageStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Image->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $payload = (array) data_get($config, 'payload', []);

        $src = data_get($payload, 'src')
            ?? data_get($config, 'src')
            ?? data_get($config, 'url')
            ?? data_get($context, 'src');

        $alt = data_get($payload, 'alt')
            ?? data_get($config, 'alt')
            ?? '';

        return new RiderStageData(
            type: RiderStageType::Image,
            enabled: (bool) data_get($config, 'enabled', filled($src)),
            key: data_get($config, 'key', 'image'),
            payload: array_filter([
                'src' => $src,
                'alt' => $alt,
                'presentation' => data_get($payload, 'presentation', data_get($config, 'presentation', 'inline')),
            ], fn ($value) => filled($value)),
            meta: (array) data_get($config, 'meta', []),
        );
    }
}