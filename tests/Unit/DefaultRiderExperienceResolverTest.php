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

    expect($experience->stages)->not->toBeNull()
        ->and($experience->stages?->stages)->toHaveCount(2)
        ->and($experience->stages?->firstOfType('message')?->payload['content'])->toBe('Stage message.')
        ->and($experience->stages?->firstOfType('redirect')?->payload['url'])->toBe('https://merchant.example.com/thank-you')
        ->and($experience->success?->content)->toBe('Stage message.')
        ->and($experience->redirect?->enabled)->toBeTrue();
});