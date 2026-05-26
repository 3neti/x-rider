<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderContentType;
use LBHurtado\XRider\Enums\RiderStageType;

class SplashStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Splash->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $content = data_get($config, 'content')
            ?? data_get($config, 'splash')
            ?? data_get($context, 'splash');

        $contentType = RiderContentType::tryFrom((string) data_get($config, 'content_type', RiderContentType::Markdown->value))
            ?? RiderContentType::Markdown;

        return new RiderStageData(
            type: RiderStageType::Splash,
            enabled: (bool) data_get($config, 'enabled', filled($content)),
            key: data_get($config, 'key', 'splash'),
            payload: [
                'content' => $content,
                'content_type' => $contentType->value,
                'timeout' => data_get($config, 'timeout', data_get($context, 'splash_timeout')),
                'presentation' => data_get($config, 'presentation', data_get($context, 'splash_presentation', 'inline')),
                'phase' => data_get($config, 'phase', data_get($context, 'phase')),
            ],
            meta: (array) data_get($config, 'meta', []),
        );
    }
}