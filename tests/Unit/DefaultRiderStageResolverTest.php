<?php

use LBHurtado\XRider\Contracts\RiderStageResolverContract;
use LBHurtado\XRider\Services\DefaultRiderStageResolver;
use LBHurtado\XRider\StageDrivers\MessageStageDriver;
use LBHurtado\XRider\StageDrivers\RedirectStageDriver;
use LBHurtado\XRider\StageDrivers\SplashStageDriver;
use LBHurtado\XRider\Support\RiderStageDriverRegistry;

function xRiderStageResolver(): DefaultRiderStageResolver
{
    $registry = new RiderStageDriverRegistry();

    $registry
        ->register(new MessageStageDriver())
        ->register(new RedirectStageDriver())
        ->register(new SplashStageDriver());

    return new DefaultRiderStageResolver($registry);
}

it('resolves explicit stages from rider config', function () {
    $collection = xRiderStageResolver()->resolve([
        'stages' => [
            [
                'type' => 'message',
                'content' => 'Hello explicit.',
            ],
            [
                'type' => 'redirect',
                'url' => 'https://merchant.example.com',
                'timeout' => 7,
            ],
        ],
    ]);

    expect($collection->stages)->toHaveCount(2)
        ->and($collection->stages[0]->normalizedType())->toBe('message')
        ->and($collection->stages[0]->payload['content'])->toBe('Hello explicit.')
        ->and($collection->stages[1]->normalizedType())->toBe('redirect')
        ->and($collection->stages[1]->payload['timeout'])->toBe(7)
        ->and($collection->meta['source'])->toBe('explicit');
});

it('resolves legacy rider message url and splash into stages', function () {
    $collection = xRiderStageResolver()->resolve([
        'message' => 'Legacy message.',
        'url' => 'https://merchant.example.com',
        'redirect_timeout' => 5,
        'splash' => 'Legacy splash.',
        'splash_timeout' => 2,
    ]);

    expect($collection->stages)->toHaveCount(3)
        ->and($collection->firstOfType('message')?->payload['content'])->toBe('Legacy message.')
        ->and($collection->firstOfType('redirect')?->payload['url'])->toBe('https://merchant.example.com')
        ->and($collection->firstOfType('redirect')?->payload['timeout'])->toBe(5)
        ->and($collection->firstOfType('splash')?->payload['content'])->toBe('Legacy splash.')
        ->and($collection->meta['source'])->toBe('legacy');
});

it('ignores unknown explicit stage types safely', function () {
    $collection = xRiderStageResolver()->resolve([
        'stages' => [
            [
                'type' => 'unknown',
                'content' => 'Ignore me.',
            ],
            [
                'type' => 'message',
                'content' => 'Keep me.',
            ],
        ],
    ]);

    expect($collection->stages)->toHaveCount(1)
        ->and($collection->stages[0]->normalizedType())->toBe('message')
        ->and($collection->stages[0]->payload['content'])->toBe('Keep me.');
});

it('resolves through the container binding', function () {
    $resolver = app(RiderStageResolverContract::class);

    $collection = $resolver->resolve([
        'stages' => [
            [
                'type' => 'message',
                'content' => 'Container works.',
            ],
        ],
    ]);

    expect($collection->stages)->toHaveCount(1)
        ->and($collection->stages[0]->payload['content'])->toBe('Container works.');
});

it('resolves legacy rider splash as fullscreen pre claim stage with metadata', function () {
    $resolver = app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class);

    $collection = $resolver->resolve([
        'splash' => '<strong>Hello</strong>',
        'splash_timeout' => 3,
        'splash_meta' => [
            'sanitized' => true,
            'html_profile' => 'rider_splash',
        ],
    ]);

    $stage = $collection->firstOfType('splash');

    expect($stage)->not->toBeNull()
        ->and($stage?->key)->toBe('legacy-splash')
        ->and($stage?->phase)->toBe('pre_claim')
        ->and($stage?->presentation)->toBe('fullscreen')
        ->and($stage?->payload['content'])->toBe('<strong>Hello</strong>')
        ->and($stage?->payload['content_type'])->toBe('html')
        ->and($stage?->meta)->toMatchArray([
            'sanitized' => true,
            'html_profile' => 'rider_splash',
        ]);
});