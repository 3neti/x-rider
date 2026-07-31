# x-rider

`3neti/x-rider` is the claimant experience package for post-claim rider messages, safe redirects, splash/continuation surfaces, analytics seams, and future campaign drivers.

## Principle

- `x-change` owns financial truth.
- `x-rider` owns claimant experience.

## Initial scope

- Rider DTOs
- Rider experience resolver
- Safe redirect resolver
- Success page shell
- Redirect controller
- Analytics recorder seam
- Future driver contract

## Install

```bash
composer require 3neti/x-rider
php artisan vendor:publish --tag=x-rider-config
php artisan vendor:publish --tag=x-rider-drivers
php artisan vendor:publish --tag=x-rider-ui
```

Laravel discovers `LBHurtado\XRider\XRiderServiceProvider` automatically. The
package owns its configuration, default driver manifests, optional routes, and Vue
runtime source. Publishing creates host overrides; it is not required for the PHP
runtime to resolve the package defaults.

## Compatibility

- PHP 8.3 or 8.4
- Laravel 12 or 13
- Node.js 20 or 22 for frontend verification

## Safety boundary

- HTML splash content is sanitized before presentation.
- Redirects are denied unless their scheme and host satisfy the configured policy.
- Runtime actions are normalized before execution and unknown actions fail safely.
- `x-rider` presents the claimant experience only. It does not authorize, settle,
  redeem, or mutate a Pay Code.

## Current integration model

`x-change` should invoke `x-rider` only after an accepted claim outcome.

```text
claim accepted
    ↓
RiderExperienceResolver
    ↓
Success.vue
    ↓
RiderRedirectController
```

## Quality gates

```bash
composer validate --strict
composer pint
composer test
npm ci
npm run test:frontend
npm audit
composer audit
```

Hosted verification covers both supported PHP/Laravel lanes and the package-owned
Vue runtime.
