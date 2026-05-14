<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\SuccessRedirectResolverContract;
use LBHurtado\XRider\Data\RiderExperienceData;

class DefaultSuccessRedirectResolver implements SuccessRedirectResolverContract
{
    public function resolve(RiderExperienceData $experience): string
    {
        $redirect = $experience->redirect;

        $fallback = $redirect?->fallbackUrl ?: (string) config('x-rider.redirects.fallback_url', '/');

        if (! $redirect?->hasUrl()) {
            return $fallback;
        }

        $url = $redirect->url;

        if (! $this->hasAllowedScheme($url)) {
            return $fallback;
        }

        if (! $this->hasAllowedHost($url)) {
            return $fallback;
        }

        return $url;
    }

    protected function hasAllowedScheme(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! is_string($scheme) || $scheme === '') {
            return false;
        }

        return in_array(
            strtolower($scheme),
            array_map('strtolower', (array) config('x-rider.redirects.allowed_schemes', ['http', 'https'])),
            true
        );
    }

    protected function hasAllowedHost(string $url): bool
    {
        if ((bool) config('x-rider.redirects.allow_any_host', false)) {
            return true;
        }

        $allowedHosts = array_filter((array) config('x-rider.redirects.allowed_hosts', []));

        if ($allowedHosts === []) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        return in_array(strtolower($host), array_map('strtolower', $allowedHosts), true);
    }
}