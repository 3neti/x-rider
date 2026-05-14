<?php

namespace LBHurtado\XRider\Actions;

use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderSubjectData;

class ResolveRiderExperience
{
    public function __construct(
        protected RiderExperienceResolverContract $resolver,
    ) {}

    public function handle(RiderSubjectData $subject, array $context = []): RiderExperienceData
    {
        return $this->resolver->resolve($subject, $context);
    }
}
