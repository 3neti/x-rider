<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

class CtaStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Cta->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $payload = (array) data_get($config, 'payload', []);

        $label = data_get($payload, 'label')
            ?? data_get($config, 'label')
            ?? 'Continue';

        $action = data_get($payload, 'action')
            ?? data_get($config, 'action')
            ?? 'open_url';

        $url = data_get($payload, 'url')
            ?? data_get($config, 'url');

        $presentation = data_get($payload, 'presentation')
            ?? data_get($config, 'presentation')
            ?? 'inline';

        $enabled = (bool) data_get(
            $config,
            'enabled',
            $action !== 'open_url' || filled($url)
        );

        return new RiderStageData(
            type: RiderStageType::Cta,
            enabled: $enabled,
            key: data_get($config, 'key', 'cta'),
            payload: array_filter([
                'label' => $label,
                'action' => $action,
                'url' => $url,
                'presentation' => $presentation,
                'phase' => data_get($payload, 'phase', data_get($config, 'phase')),
            ], fn ($value) => filled($value)),
            meta: (array) data_get($config, 'meta', []),
        );
    }
}
