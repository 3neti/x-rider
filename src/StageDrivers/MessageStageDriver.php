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
        $content = data_get($config, 'content')
            ?? data_get($config, 'message')
            ?? data_get($context, 'message');

        $contentType = RiderContentType::tryFrom((string) data_get($config, 'content_type', data_get($config, 'type', RiderContentType::Markdown->value)))
            ?? RiderContentType::Markdown;

        return new RiderStageData(
            type: RiderStageType::Message,
            enabled: (bool) data_get($config, 'enabled', filled($content)),
            key: data_get($config, 'key', 'message'),
            payload: [
                'content' => $content,
                'content_type' => $contentType->value,
            ],
            meta: (array) data_get($config, 'meta', []),
        );
    }
}