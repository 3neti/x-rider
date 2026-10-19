<?php

use Illuminate\Validation\ValidationException;
use LBHurtado\XRider\Services\RedirectDestinationPolicy;

it('accepts an explicit permitted destination without rewriting it', function () {
    config(['x-rider.redirects.allowed_hosts' => ['example.com']]);
    $url = 'https://example.com/path?campaign=one#section';
    expect(app(RedirectDestinationPolicy::class)->validate($url))->toBe($url);
});

it('rejects destinations that cannot fulfill the paid instruction', function (string $url) {
    config(['x-rider.redirects.allowed_hosts' => ['example.com']]);
    app(RedirectDestinationPolicy::class)->validate($url);
})->with(['javascript:alert(1)', '/relative', 'https://other.example/path', 'https://user:pass@example.com/', "https://example.com/\nheader", 'https://example.com/?x_rider_handoff=forged'])->throws(ValidationException::class);

it('requires an explicit policy for arbitrary destination hosts', function () {
    config(['x-rider.redirects.allowed_hosts' => [], 'x-rider.redirects.allow_any_host' => false]);
    app(RedirectDestinationPolicy::class)->validate('https://example.com');
})->throws(ValidationException::class);

it('supports explicitly permitted arbitrary hosts', function () {
    config(['x-rider.redirects.allow_any_host' => true]);
    expect(app(RedirectDestinationPolicy::class)->validate('https://example.com'))->toBe('https://example.com');
});
