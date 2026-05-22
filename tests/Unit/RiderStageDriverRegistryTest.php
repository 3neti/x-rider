<?php

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\Support\RiderStageDriverRegistry;

function fakeMessageStageDriver(): RiderStageDriverContract
{
    return new class implements RiderStageDriverContract {
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
    };
}

it('registers and retrieves a stage driver', function () {
    $registry = new RiderStageDriverRegistry();

    $registry->register(fakeMessageStageDriver());

    expect($registry->has('message'))->toBeTrue()
        ->and($registry->get('message'))->toBeInstanceOf(RiderStageDriverContract::class);
});

it('returns all registered drivers', function () {
    $registry = new RiderStageDriverRegistry();

    $registry->register(fakeMessageStageDriver());

    expect($registry->all())->toHaveKey('message')
        ->and($registry->all())->toHaveCount(1);
});

it('throws when retrieving an unknown driver', function () {
    $registry = new RiderStageDriverRegistry();

    $registry->get('unknown');
})->throws(RuntimeException::class, 'Rider stage driver [unknown] is not registered.');

it('forgets a registered driver', function () {
    $registry = new RiderStageDriverRegistry();

    $registry->register(fakeMessageStageDriver());

    expect($registry->has('message'))->toBeTrue();

    $registry->forget('message');

    expect($registry->has('message'))->toBeFalse();
});

it('flushes all registered drivers', function () {
    $registry = new RiderStageDriverRegistry();

    $registry->register(fakeMessageStageDriver());

    expect($registry->all())->toHaveCount(1);

    $registry->flush();

    expect($registry->all())->toBeEmpty();
});

it('driver can make a rider stage data object', function () {
    $driver = fakeMessageStageDriver();

    $stage = $driver->make([
        'content' => 'Hello runtime.',
        'key' => 'welcome-message',
    ]);

    expect($stage)->toBeInstanceOf(RiderStageData::class)
        ->and($stage->normalizedType())->toBe('message')
        ->and($stage->key)->toBe('welcome-message')
        ->and($stage->payload['content'])->toBe('Hello runtime.');
});