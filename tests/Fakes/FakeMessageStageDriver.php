<?php

namespace LBHurtado\XRider\Tests\Fakes;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

class FakeMessageStageDriver implements RiderStageDriverContract
{
    public function type(): string
    {
        return RiderStageType::Message->value;
    }

    public function make(array $config = [], array $context = []): RiderStageData
    {
        return new RiderStageData(
            type: RiderStageType::Message,
            enabled: (bool) data_get($config, 'enabled', true),
            key: data_get($config, 'key', 'fake-message'),
            payload: [
                'content' => data_get($config, 'content', 'Hello from fake driver.'),
            ],
            meta: data_get($config, 'meta', []),
        );
    }
}