<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderSubjectData;

interface RiderExperienceResolverContract
{
    public function resolve(RiderSubjectData $subject, array $context = []): RiderExperienceData;
}
