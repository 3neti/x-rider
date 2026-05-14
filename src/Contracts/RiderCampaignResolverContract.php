<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderCampaignData;
use LBHurtado\XRider\Data\RiderSubjectData;

interface RiderCampaignResolverContract
{
    public function resolve(RiderSubjectData $subject, array $context = []): RiderCampaignData;
}
