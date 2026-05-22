<?php

use Illuminate\Filesystem\Filesystem;
use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Data\RiderCampaignData;
use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Enums\RiderOutcomeState;
use LBHurtado\XRider\Services\DefaultRiderExperienceResolver;
use LBHurtado\XRider\Support\RiderDriverLoader;

function xRiderTestSubject(): RiderSubjectData
{
    return new RiderSubjectData(
        type: 'voucher',
        id: 'ABC123',
        code: 'ABC123',
        meta: [],
    );
}

function xRiderCampaignStub(): RiderCampaignResolverContract
{
    return new class implements RiderCampaignResolverContract {
        public function resolve(RiderSubjectData $subject, array $context = []): RiderCampaignData
        {
            return new RiderCampaignData(
                id: null,
                merchant: null,
                meta: [],
            );
        }
    };
}

it('resolves the default rider experience from yaml', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject());

    expect($experience->normalizedState())->toBe(RiderOutcomeState::AcceptedSuccess->value)
        ->and($experience->success?->content)->toContain('Thank you')
        ->and($experience->redirect?->timeout)->toBe(5)
        ->and($experience->meta['driver'])->toBe('default');
});

it('uses pending content for accepted pending state', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'state' => RiderOutcomeState::AcceptedPending->value,
    ]);

    expect($experience->success?->content)->toContain('being processed');
});

it('lets context rider override yaml rider content', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'success' => [
                'content' => 'Context wins.',
            ],
        ],
    ]);

    expect($experience->success?->content)->toBe('Context wins.');
});

it('enables redirect when legacy rider url is provided by context', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'message' => 'Thank you very much.',
            'url' => 'https://merchant.example.com/thank-you',
            'redirect_timeout' => 3,
        ],
    ]);

    expect($experience->redirect?->enabled)->toBeTrue()
        ->and($experience->redirect?->url)->toBe('https://merchant.example.com/thank-you')
        ->and($experience->redirect?->timeout)->toBe(3);
});

it('attaches resolved stages to rider experience', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'message' => 'Stage message.',
            'url' => 'https://merchant.example.com/thank-you',
            'redirect_timeout' => 4,
        ],
    ]);

    $legacyMessage = collect($experience->stages?->stages ?? [])
        ->first(fn ($stage) => $stage->key === 'legacy-message');

    $legacyRedirect = collect($experience->stages?->stages ?? [])
        ->first(fn ($stage) => $stage->key === 'legacy-redirect');

    expect($experience->stages)->not->toBeNull()
        ->and($legacyMessage?->payload['content'])->toBe('Stage message.')
        ->and($legacyRedirect?->payload['url'])->toBe('https://merchant.example.com/thank-you')
        ->and($experience->success?->content)->toBe('Stage message.')
        ->and($experience->redirect?->enabled)->toBeTrue();
});

it('normalizes explicit redirect stage into rider redirect data', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'stages' => [
                [
                    'type' => 'message',
                    'key' => 'test-message',
                    'content' => 'Stage message.',
                ],
                [
                    'type' => 'redirect',
                    'key' => 'test-redirect',
                    'url' => 'https://merchant.example.com/stage-redirect',
                    'timeout' => 9,
                    'fallback_url' => '/stage-fallback',
                ],
            ],
        ],
    ]);

    expect($experience->redirect?->enabled)->toBeTrue()
        ->and($experience->redirect?->url)->toBe('https://merchant.example.com/stage-redirect')
        ->and($experience->redirect?->timeout)->toBe(9)
        ->and($experience->redirect?->fallbackUrl)->toBe('/stage-fallback');
});

it('does not enable redirect when explicit redirect stage is disabled', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'stages' => [
                [
                    'type' => 'redirect',
                    'enabled' => false,
                    'url' => 'https://merchant.example.com/stage-redirect',
                    'timeout' => 9,
                ],
            ],
        ],
    ]);

    expect($experience->redirect?->enabled)->toBeFalse();
});

it('normalizes explicit message stage into rider success content', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'stages' => [
                [
                    'type' => 'message',
                    'key' => 'test-message',
                    'content' => 'Stage-driven thank you.',
                    'content_type' => 'text',
                ],
            ],
        ],
    ]);

    expect($experience->success?->content)->toBe('Stage-driven thank you.')
        ->and($experience->success?->normalizedType())->toBe('text');
});

it('keeps legacy rider message precedence over message stages', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $resolver = new DefaultRiderExperienceResolver(
        campaigns: xRiderCampaignStub(),
        drivers: new RiderDriverLoader(new Filesystem),
        stages: app(\LBHurtado\XRider\Contracts\RiderStageResolverContract::class),
    );

    $experience = $resolver->resolve(xRiderTestSubject(), [
        'rider' => [
            'message' => 'Legacy message wins.',
            'stages' => [
                [
                    'type' => 'message',
                    'key' => 'test-message',
                    'content' => 'Stage message loses.',
                ],
            ],
        ],
    ]);

    expect($experience->success?->content)->toBe('Legacy message wins.');
});