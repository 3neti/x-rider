<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Data\RiderContentData;
use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderRedirectData;
use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Enums\RiderContentType;
use LBHurtado\XRider\Enums\RiderOutcomeState;

class DefaultRiderExperienceResolver implements RiderExperienceResolverContract
{
    public function __construct(
        protected RiderCampaignResolverContract $campaigns,
    ) {}

    public function resolve(RiderSubjectData $subject, array $context = []): RiderExperienceData
    {
        $state = RiderOutcomeState::tryFrom((string) data_get($context, 'state', config('x-rider.defaults.outcome_state')))
            ?? RiderOutcomeState::AcceptedSuccess;

        $rider = data_get($context, 'rider', []);
        $message = data_get($rider, 'message')
            ?? data_get($rider, 'success.content')
            ?? ($state === RiderOutcomeState::AcceptedPending
                ? config('x-rider.defaults.pending_message')
                : config('x-rider.defaults.success_message'));

        $successType = RiderContentType::tryFrom((string) (data_get($rider, 'type') ?? data_get($rider, 'success.type') ?? config('x-rider.defaults.success_type')))
            ?? RiderContentType::Markdown;

        $redirectUrl = data_get($rider, 'url') ?? data_get($rider, 'redirect.url');
        $redirectTimeout = (int) (data_get($rider, 'redirect_timeout') ?? data_get($rider, 'redirect.timeout') ?? config('x-rider.defaults.redirect_timeout'));
        $fallbackUrl = data_get($rider, 'fallback_url') ?? data_get($rider, 'redirect.fallback_url') ?? config('x-rider.redirects.fallback_url');

        return new RiderExperienceData(
            state: $state,
            subject: $subject,
            preClaim: $this->contentFromArray(data_get($rider, 'pre_claim')),
            success: new RiderContentData(
                enabled: $state->riderMayRun() && filled($message),
                type: $successType,
                content: $message,
                meta: data_get($rider, 'success.meta', []),
            ),
            redirect: new RiderRedirectData(
                enabled: $state->riderMayRun() && filled($redirectUrl),
                url: $redirectUrl,
                timeout: max(0, $redirectTimeout),
                fallbackUrl: $fallbackUrl,
                meta: data_get($rider, 'redirect.meta', []),
            ),
            campaign: $this->campaigns->resolve($subject, $context),
            ads: data_get($context, 'ads', data_get($rider, 'ads', [])),
            analytics: data_get($context, 'analytics', []),
            meta: data_get($context, 'meta', []),
        );
    }

    protected function contentFromArray(mixed $value): ?RiderContentData
    {
        if (! is_array($value)) {
            return null;
        }

        return new RiderContentData(
            enabled: (bool) data_get($value, 'enabled', true),
            type: RiderContentType::tryFrom((string) data_get($value, 'type', RiderContentType::Markdown->value)) ?? RiderContentType::Markdown,
            content: data_get($value, 'content'),
            meta: data_get($value, 'meta', []),
        );
    }
}
