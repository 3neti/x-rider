<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\CtaStageDriver;

it('creates a cta stage from payload config', function () {
    $stage = (new CtaStageDriver())->make([
        'key' => 'demo-cta',
        'payload' => [
            'label' => 'Open Reward',
            'action' => 'open_url',
            'url' => 'https://example.com/reward',
        ],
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Cta->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->key)->toBe('demo-cta')
        ->and($stage->payload['label'])->toBe('Open Reward')
        ->and($stage->payload['action'])->toBe('open_url')
        ->and($stage->payload['url'])->toBe('https://example.com/reward')
        ->and($stage->payload['presentation'])->toBe('inline');
});

it('creates a cta stage from top level config', function () {
    $stage = (new CtaStageDriver())->make([
        'label' => 'Continue',
        'action' => 'open_url',
        'url' => 'https://example.com',
        'presentation' => 'modal',
    ]);

    expect($stage->enabled)->toBeTrue()
        ->and($stage->payload['label'])->toBe('Continue')
        ->and($stage->payload['presentation'])->toBe('modal');
});
