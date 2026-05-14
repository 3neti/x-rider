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
php artisan vendor:publish --tag=x-rider-ui
```

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
