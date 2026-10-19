<?php

declare(strict_types=1);

namespace LBHurtado\XRider\Services;

use Illuminate\Validation\ValidationException;

final class RedirectDestinationPolicy
{
    public function validate(string $url): string
    {
        $parts = parse_url($url);
        parse_str(is_array($parts) ? ($parts['query'] ?? '') : '', $query);
        $schemes = array_map('strtolower', (array) config('x-rider.redirects.allowed_schemes', ['http', 'https']));
        $hosts = array_map('strtolower', (array) config('x-rider.redirects.allowed_hosts', []));
        if (strlen($url) > 4096 || array_key_exists('x_rider_handoff', $query)
            || ! filter_var($url, FILTER_VALIDATE_URL)
            || preg_match('/[\x00-\x20\x7f]/', $url)
            || ! is_array($parts)
            || isset($parts['user']) || isset($parts['pass'])
            || ! in_array(strtolower($parts['scheme'] ?? ''), $schemes, true)
            || (! config('x-rider.redirects.allow_any_host', false)
                && ! in_array(strtolower($parts['host'] ?? ''), $hosts, true))) {
            throw ValidationException::withMessages(['rider.url' => 'The landing page URL is not permitted by the redirect policy.']);
        }

        return $url;
    }
}
