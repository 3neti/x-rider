<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\SuccessRedirectResolverContract;
use LBHurtado\XRider\Data\RiderExperienceData;

class DefaultSuccessRedirectResolver implements SuccessRedirectResolverContract
{
    public function resolve(RiderExperienceData $experience): string
    {
        $fallback = $experience->redirect?->fallbackUrl
            ?: config('x-rider.redirects.fallback_url', '/');

        if (! $experience->riderMayRun() || ! $experience->redirect?->hasUrl()) {
            return $fallback;
        }

        $url = (string) $experience->redirect->url;

        return $this->isAllowed($url) ? $url : $fallback;
    }

    protected function isAllowed(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts) || blank($parts['scheme'] ?? null)) {
            return false;
        }

        $scheme = strtolower((string) $parts['scheme']);
        $allowedSchemes = array_map('strtolower', config('x-rider.redirects.allowed_schemes', ['http', 'https']));

        if (! in_array($scheme, $allowedSchemes, true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if (blank($host)) {
            return false;
        }

        if ((bool) config('x-rider.redirects.allow_any_host', true)) {
            return true;
        }

        $allowedHosts = array_map('strtolower', config('x-rider.redirects.allowed_hosts', []));

        return in_array($host, $allowedHosts, true);
    }
}
