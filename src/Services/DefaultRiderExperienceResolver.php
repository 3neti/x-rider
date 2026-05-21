<?php

namespace LBHurtado\XRider\Services;

use Illuminate\Support\Facades\Log;
use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Data\RiderContentData;
use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderRedirectData;
use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Enums\RiderContentType;
use LBHurtado\XRider\Enums\RiderOutcomeState;
use LBHurtado\XRider\Support\RiderDriverLoader;

class DefaultRiderExperienceResolver implements RiderExperienceResolverContract
{
    public function __construct(
        protected RiderCampaignResolverContract $campaigns,
        protected RiderDriverLoader $drivers,
    ) {}

    public function resolve(RiderSubjectData $subject, array $context = []): RiderExperienceData
    {
        $state = RiderOutcomeState::tryFrom(
            (string) data_get($context, 'state', config('x-rider.defaults.outcome_state'))
        ) ?? RiderOutcomeState::AcceptedSuccess;

        $driver = $this->drivers->load(
            data_get($context, 'driver')
        );

        $driverRider = data_get($driver, 'rider', []);
        $contextRider = data_get($context, 'rider', []);

        $rider = array_replace_recursive(
            is_array($driverRider) ? $driverRider : [],
            is_array($contextRider) ? $contextRider : [],
        );

        $message = data_get($rider, 'message')
            ?? ($state === RiderOutcomeState::AcceptedPending
                ? data_get($rider, 'pending.content', config('x-rider.defaults.pending_message'))
                : data_get($rider, 'success.content', config('x-rider.defaults.success_message')));

        $successType = RiderContentType::tryFrom((string) (
            data_get($rider, 'type')
            ?? data_get($rider, 'success.type')
            ?? config('x-rider.defaults.success_type')
        )) ?? RiderContentType::Markdown;

        $redirectUrl = data_get($rider, 'url') ?? data_get($rider, 'redirect.url');

        $redirectTimeout = (int) (
            data_get($rider, 'redirect_timeout')
            ?? data_get($rider, 'redirect.timeout')
            ?? config('x-rider.defaults.redirect_timeout')
        );

        $fallbackUrl = data_get($rider, 'fallback_url')
            ?? data_get($rider, 'redirect.fallback_url')
            ?? config('x-rider.redirects.fallback_url');

        $redirectEnabled = $this->redirectEnabled(
            state: $state,
            rider: $rider,
            contextRider: is_array($contextRider) ? $contextRider : [],
            redirectUrl: $redirectUrl,
        );

        Log::debug('[x-rider] resolved rider experience', [
            'subject_type' => $subject->type,
            'subject_id' => $subject->id,
            'subject_code' => $subject->code,
            'state' => $state->value,
            'message_present' => filled($message),
            'message_preview' => is_string($message) ? str($message)->limit(80)->toString() : null,
            'redirect_url_present' => filled($redirectUrl),
            'redirect_url' => $redirectUrl,
            'redirect_enabled' => $redirectEnabled,
            'redirect_timeout' => max(0, $redirectTimeout),
            'fallback_url' => $fallbackUrl,
            'context_rider_keys' => is_array($contextRider) ? array_keys($contextRider) : [],
            'merged_redirect_enabled' => data_get($rider, 'redirect.enabled'),
            'context_redirect_enabled' => is_array($contextRider) ? data_get($contextRider, 'redirect.enabled') : null,
            'context_redirect_enabled_legacy' => is_array($contextRider) ? data_get($contextRider, 'redirect_enabled') : null,
        ]);

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
                enabled: $redirectEnabled,
                url: $redirectUrl,
                timeout: max(0, $redirectTimeout),
                fallbackUrl: $fallbackUrl,
                meta: data_get($rider, 'redirect.meta', []),
            ),
            campaign: $this->campaigns->resolve($subject, $context),
            ads: data_get($context, 'ads', data_get($rider, 'ads', [])),
            analytics: array_replace_recursive(
                (array) data_get($rider, 'analytics', []),
                (array) data_get($context, 'analytics', []),
            ),
            meta: array_replace_recursive(
                [
                    'driver' => data_get($driver, 'name', config('x-rider.driver', 'default')),
                    'driver_version' => data_get($driver, 'version'),
                ],
                (array) data_get($context, 'meta', []),
            ),
        );
    }

    protected function redirectEnabled(
        RiderOutcomeState $state,
        array $rider,
        array $contextRider,
        mixed $redirectUrl,
    ): bool {
        $decision = [
            'state' => $state->value,
            'rider_may_run' => $state->riderMayRun(),
            'redirect_url' => $redirectUrl,
            'redirect_url_present' => filled($redirectUrl),
            'context_url_present' => filled(data_get($contextRider, 'url')),
            'context_nested_url_present' => filled(data_get($contextRider, 'redirect.url')),
            'context_redirect_enabled' => data_get($contextRider, 'redirect.enabled'),
            'context_redirect_enabled_legacy' => data_get($contextRider, 'redirect_enabled'),
            'merged_redirect_enabled' => data_get($rider, 'redirect.enabled'),
        ];

        if (! $state->riderMayRun()) {
            Log::debug('[x-rider] redirect disabled: rider may not run', $decision);

            return false;
        }

        if (data_get($contextRider, 'redirect.enabled') === false) {
            Log::debug('[x-rider] redirect disabled: context redirect.enabled=false', $decision);

            return false;
        }

        if (data_get($contextRider, 'redirect_enabled') === false) {
            Log::debug('[x-rider] redirect disabled: context redirect_enabled=false', $decision);

            return false;
        }

        $enabled = (bool) data_get($contextRider, 'redirect.enabled', false)
            || (bool) data_get($contextRider, 'redirect_enabled', false)
            || filled(data_get($contextRider, 'url'))
            || filled(data_get($contextRider, 'redirect.url'))
            || (
                (bool) data_get($rider, 'redirect.enabled', false)
                && filled($redirectUrl)
            );

        Log::debug('[x-rider] redirect enabled decision', $decision + [
                'enabled' => $enabled,
            ]);

        return $enabled;
    }

    protected function contentFromArray(mixed $value): ?RiderContentData
    {
        if (! is_array($value)) {
            return null;
        }

        return new RiderContentData(
            enabled: (bool) data_get($value, 'enabled', true),
            type: RiderContentType::tryFrom((string) data_get($value, 'type', RiderContentType::Markdown->value))
            ?? RiderContentType::Markdown,
            content: data_get($value, 'content'),
            meta: data_get($value, 'meta', []),
        );
    }
}