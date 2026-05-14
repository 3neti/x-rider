<?php

use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Services\DefaultRiderCampaignResolver;
use LBHurtado\XRider\Services\DefaultRiderExperienceResolver;

it('normalizes legacy message and url rider shape', function () {
    $resolver = new DefaultRiderExperienceResolver(new DefaultRiderCampaignResolver());

    $experience = $resolver->resolve(new RiderSubjectData(reference: 'claim-ABC', code: 'ABC'), [
        'rider' => [
            'message' => 'Thank you for claiming.',
            'url' => 'https://merchant.example/continue',
            'redirect_timeout' => 7,
        ],
    ]);

    expect($experience->success?->content)->toBe('Thank you for claiming.')
        ->and($experience->redirect?->url)->toBe('https://merchant.example/continue')
        ->and($experience->redirect?->timeout)->toBe(7);
});
