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