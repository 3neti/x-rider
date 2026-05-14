<?php

namespace LBHurtado\XRider\Actions;

use LBHurtado\XRider\Contracts\SuccessRedirectResolverContract;
use LBHurtado\XRider\Data\RiderExperienceData;

class ResolveRiderRedirect
{
    public function __construct(
        protected SuccessRedirectResolverContract $resolver,
    ) {}

    public function handle(RiderExperienceData $experience): string
    {
        return $this->resolver->resolve($experience);
    }
}
