<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderExperienceData;

interface SuccessRedirectResolverContract
{
    public function resolve(RiderExperienceData $experience): string;
}
