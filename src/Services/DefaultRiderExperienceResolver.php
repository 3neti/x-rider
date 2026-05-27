<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Contracts\RiderStageResolverContract;
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
        protected RiderStageResolverContract $stages,
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

        $stageCollection = $this->stages->resolve($rider, [
            'state' => $state->value,
            'subject' => $subject,
        ] + $context);

        $redirectStage = collect($stageCollection->redirectLike())
            ->last(fn ($stage) => $stage->enabled);

        $messageStage = collect($stageCollection->renderable())
            ->filter(fn ($stage) => $stage->normalizedType() === 'message')
            ->last(fn ($stage) => $stage->enabled);

        $splashStage = collect($stageCollection->renderable())
            ->filter(fn ($stage) => $stage->normalizedType() === 'splash')
            ->last(fn ($stage) => $stage->enabled);

        $message = data_get($rider, 'message')
            ?? data_get($messageStage?->payload ?? [], 'content')
            ?? ($state === RiderOutcomeState::AcceptedPending
                ? data_get($rider, 'pending.content', config('x-rider.defaults.pending_message'))
                : data_get($rider, 'success.content', config('x-rider.defaults.success_message')));

        $successType = RiderContentType::tryFrom((string) (
            data_get($rider, 'type')
            ?? data_get($messageStage?->payload ?? [], 'content_type')
            ?? data_get($rider, 'success.type')
            ?? config('x-rider.defaults.success_type')
        )) ?? RiderContentType::Markdown;

        $redirectUrl = data_get($rider, 'url')
            ?? data_get($redirectStage?->payload ?? [], 'url')
            ?? data_get($rider, 'redirect.url');

        $redirectTimeout = (int) (
            data_get($rider, 'redirect_timeout')
            ?? data_get($redirectStage?->payload ?? [], 'timeout')
            ?? data_get($rider, 'redirect.timeout')
            ?? config('x-rider.defaults.redirect_timeout')
        );

        $fallbackUrl = data_get($rider, 'fallback_url')
            ?? data_get($redirectStage?->payload ?? [], 'fallback_url')
            ?? data_get($rider, 'redirect.fallback_url')
            ?? config('x-rider.redirects.fallback_url');

        $redirectEnabled = $this->redirectEnabled(
            state: $state,
            rider: $rider,
            contextRider: is_array($contextRider) ? $contextRider : [],
            redirectUrl: $redirectUrl,
            redirectStage: $redirectStage,
        );

        return new RiderExperienceData(
            state: $state,
            subject: $subject,
            preClaim: $this->preClaimContent(
                rider: $rider,
                splashStage: $splashStage,
            ),
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
            stages: $stageCollection,
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
        ?\LBHurtado\XRider\Data\RiderStageData $redirectStage = null,
    ): bool {
        if (! $state->riderMayRun()) {
            return false;
        }

        if (data_get($contextRider, 'redirect.enabled') === false) {
            return false;
        }

        if (data_get($contextRider, 'redirect_enabled') === false) {
            return false;
        }

        if ($redirectStage?->enabled === false) {
            return false;
        }

        $enabled = (bool) data_get($contextRider, 'redirect.enabled', false)
            || (bool) data_get($contextRider, 'redirect_enabled', false)
            || filled(data_get($contextRider, 'url'))
            || filled(data_get($contextRider, 'redirect.url'))
            || (
                (bool) data_get($rider, 'redirect.enabled', false)
                && filled($redirectUrl)
            )
            || (
                $redirectStage?->enabled === true
                && filled($redirectUrl)
            );

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

    protected function preClaimContent(
        array $rider,
        ?\LBHurtado\XRider\Data\RiderStageData $splashStage = null,
    ): ?RiderContentData {
        $preClaim = $this->contentFromArray(data_get($rider, 'pre_claim'));

        if ($preClaim instanceof RiderContentData) {
            return $preClaim;
        }

        $content = data_get($splashStage?->payload ?? [], 'content');

        if (! filled($content)) {
            return null;
        }

        $type = RiderContentType::tryFrom((string) data_get(
            $splashStage?->payload ?? [],
            'content_type',
            RiderContentType::Markdown->value
        )) ?? RiderContentType::Markdown;

        return new RiderContentData(
            enabled: $splashStage?->enabled ?? true,
            type: $type,
            content: $content,
            meta: array_filter(array_replace_recursive(
                is_array($splashStage?->meta ?? null) ? $splashStage->meta : [],
                [
                    'source' => 'stage',
                    'stage_key' => $splashStage?->key,
                    'timeout' => data_get($splashStage?->payload ?? [], 'timeout'),
                ],
            ), fn ($value) => filled($value)),
        );
    }
}