<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Data\RiderCampaignData;
use LBHurtado\XRider\Data\RiderSubjectData;

class DefaultRiderCampaignResolver implements RiderCampaignResolverContract
{
    public function resolve(RiderSubjectData $subject, array $context = []): RiderCampaignData
    {
        $campaign = data_get($context, 'campaign', []);

        return new RiderCampaignData(
            id: data_get($campaign, 'id'),
            merchant: data_get($campaign, 'merchant'),
            tags: data_get($campaign, 'tags', []),
            meta: data_get($campaign, 'meta', []),
        );
    }
}
