<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\MessageStageDriver;

it('creates a message stage from content', function () {
    $stage = (new MessageStageDriver())->make([
        'content' => 'Hello rider.',
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Message->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->key)->toBe('message')
        ->and($stage->payload['content'])->toBe('Hello rider.')
        ->and($stage->payload['content_type'])->toBe('markdown');
});

it('creates a disabled message stage when no content exists', function () {
    $stage = (new MessageStageDriver())->make();

    expect($stage->enabled)->toBeFalse()
        ->and($stage->payload['content'])->toBeNull();
});

it('supports legacy message key', function () {
    $stage = (new MessageStageDriver())->make([
        'message' => 'Legacy message.',
    ]);

    expect($stage->enabled)->toBeTrue()
        ->and($stage->payload['content'])->toBe('Legacy message.');
});