<?php

namespace LBHurtado\XRider\StageDrivers;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderContentType;
use LBHurtado\XRider\Enums\RiderStageType;

class MessageStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Message->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        $payload = (array) data_get($config, 'payload', []);

        $content = data_get($payload, 'content')
            ?? data_get($config, 'content')
            ?? data_get($config, 'message')
            ?? data_get($context, 'message');

        $contentType = RiderContentType::tryFrom((string) (
            data_get($payload, 'content_type')
            ?? data_get($config, 'content_type')
            ?? data_get($context, 'content_type')
            ?? RiderContentType::Markdown->value
        )) ?? RiderContentType::Markdown;

        return new RiderStageData(
            type: RiderStageType::Message,
            enabled: (bool) data_get($config, 'enabled', filled($content)),
            key: data_get($config, 'key', 'message'),
            payload: [
                'content' => $content,
                'content_type' => $contentType->value,
                'phase' => data_get($payload, 'phase', data_get($config, 'phase')),
            ],
            meta: (array) data_get($config, 'meta', []),
        );
    }
}