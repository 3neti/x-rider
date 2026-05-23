# x-change Rider Experience — Comprehensive Implementation Plan
## Definitive Architecture + Execution Plan

**Version**: 1.0  
**Status**: Strategic Implementation Blueprint  
**Audience**: Core developers + AI agents

---

# 1. Objective

Transform the current rider implementation from:

```text id="d7m4o8"
message + redirect
```

into:

```text id="2puk1v"
a programmable, monetizable, analytics-aware
post-claim engagement platform
```

without coupling rider behavior to financial execution semantics.

---

# 2. Architectural Principle

## Redemption establishes financial truth.
## Rider establishes experiential continuity.

The rider layer:
- must never mutate financial state
- must never alter payout correctness
- must never decide redemption validity

The rider layer:
- consumes accepted claim outcomes
- orchestrates claimant-facing continuation
- becomes the primary merchant engagement surface

---

# 3. Current State

Already implemented:

| Capability | Status |
|---|---|
| `/x/claim` entry flow | ✔ |
| YAML-driven form-flow | ✔ |
| Success page | ✔ |
| Redirect endpoint | ✔ |
| markdown rider rendering | ✔ |
| pending disbursement philosophy | ✔ |
| post-redemption pipeline | ✔ |
| redirect audit seam | ✔ |

Current rider behavior:
- splash page
- success message
- redirect URL

---

# 4. Strategic End State

The rider becomes:

```text id="4y7oaq"
RiderExperiencePlatform
```

supporting:
- merchant continuation
- ads
- app-install funnels
- deep links
- loyalty
- cross-sell
- analytics
- attribution
- campaign orchestration

---

# 5. Canonical Rider Lifecycle

```text id="6gk1rv"
PRE-CLAIM
    splash / teaser / onboarding

IN-FLOW
    contextual instructions / engagement

POST-CLAIM
    success rider / pending rider

REDIRECT
    merchant continuation / app deep-link

POST-REDIRECT
    analytics / attribution / monetization
```

---

# 6. Implementation Philosophy

## We WILL:
- extend existing `/x/claim`
- preserve YAML-driven flow
- preserve form-flow ownership
- preserve voucher redemption semantics
- preserve pending disbursement philosophy
- package rider logic into DTO/contracts/services

## We will NOT:
- rebuild redemption
- duplicate form-flow
- embed ad logic inside payout logic
- allow frontend direct redirects

---

# 7. High-Level Architecture

```text id="89d1jg"
Voucher.instructions.rider
            ↓
RiderExperienceResolver
            ↓
RiderExperienceData
            ↓
ClaimSuccessPageController
            ↓
Success.vue
            ↓
ClaimRedirectController
            ↓
merchant/app destination
```

---

# 8. Canonical Rider DTO Layer

---

## 8.1 RiderExperienceData

```php id="b0hxy0"
RiderExperienceData
```

### Responsibilities
- normalized rider payload
- stable frontend contract
- campaign-aware
- analytics-ready

---

## 8.2 Proposed Shape

```php id="s3u03n"
[
    'state' => 'accepted_success',

    'pre_claim' => [...],

    'success' => [
        'enabled' => true,
        'type' => 'markdown',
        'content' => '...',
    ],

    'redirect' => [
        'enabled' => true,
        'url' => '...',
        'timeout' => 5,
        'fallback_url' => '...',
    ],

    'ads' => [
        'enabled' => false,
        'placements' => [],
    ],

    'campaign' => [
        'id' => 'summer-2026',
        'merchant' => '...',
    ],

    'analytics' => [
        'claim_id' => '...',
    ],
]
```

---

# 9. New Package Contracts

---

## 9.1 RiderExperienceResolverContract

```php id="akq79o"
interface RiderExperienceResolverContract
{
    public function resolve(
        Voucher $voucher,
        ClaimOutcomeData $outcome
    ): RiderExperienceData;
}
```

---

## 9.2 SuccessRedirectResolverContract

```php id="8kl5gj"
interface SuccessRedirectResolverContract
{
    public function resolve(
        Voucher $voucher,
        RiderExperienceData $rider
    ): string;
}
```

---

## 9.3 RiderAnalyticsRecorderContract

```php id="9mwt3w"
interface RiderAnalyticsRecorderContract
{
    public function record(
        RiderAnalyticsEventData $event
    ): void;
}
```

---

## 9.4 RiderCampaignResolverContract

```php id="bqhy0j"
interface RiderCampaignResolverContract
{
    public function resolveCampaign(
        Voucher $voucher
    ): RiderCampaignData;
}
```

---

# 10. New Service Layer

---

## 10.1 DefaultRiderExperienceResolver

### Responsibilities
- inspect voucher rider config
- determine rider state
- build normalized DTO
- merge campaign overlays
- merge ad placements

---

## 10.2 DefaultSuccessRedirectResolver

### Responsibilities
- validate redirect URL
- validate scheme
- validate host
- resolve deep-link fallback
- enforce allowlists

---

## 10.3 DefaultRiderAnalyticsRecorder

### Responsibilities
- log redirect events
- log countdown impressions
- log clicks
- log campaign attribution

---

## 10.4 DefaultAdInsertionService

### Responsibilities
- inject ads into rider payload
- resolve placements
- support targeting rules

---

# 11. Claim Outcome Model

The rider must become outcome-aware.

---

## 11.1 ClaimOutcomeData

```php id="14y5mp"
accepted_success
accepted_pending
rejected_failure
```

---

## 11.2 Source

Produced after:
- redemption execution
- post-redemption pipeline

---

## 11.3 Rule

Only:
- accepted_success
- accepted_pending

may invoke rider continuation.

---

# 12. Success Page Refactor

---

## 12.1 Current

```text id="o6s17t"
Success.vue
    → directly reads rider.url
```

---

## 12.2 Required

```text id="gl0x5f"
Success.vue
    → redirectEndpoint
    → /x/claim/{code}/redirect
```

Frontend must never directly navigate to:
- `rider.url`
- external merchant URLs

---

# 13. Redirect Controller Refactor

---

## 13.1 Current Responsibilities

- redirect
- audit log

---

## 13.2 Expanded Responsibilities

### Security
- validate scheme
- validate domain
- validate allowlists

### Analytics
- record click
- record campaign
- record source

### Mobile
- support deep links
- support app fallback URLs

### Monetization
- future ad-click attribution
- affiliate tracking

---

# 14. Rider Content Pipeline

Introduce:

```text id="3is7mr"
RiderContentPipeline
```

---

## Stages

```text id="uxv4qk"
ResolveBaseContent
    ↓
ResolveCampaign
    ↓
InjectAds
    ↓
ApplyLocalization
    ↓
NormalizeContent
```

---

# 15. Ad Architecture

---

## 15.1 Critical Rule

Ads must NEVER run:
- inside redemption logic
- inside payout execution
- inside voucher state mutation

Ads belong ONLY inside rider rendering.

---

## 15.2 Ad Placement Types

| Placement | Description |
|---|---|
| splash_banner | before claim |
| success_inline | inside success page |
| redirect_interstitial | before redirect |
| footer_cta | bottom CTA |
| app_install | install prompt |

---

## 15.3 Ad DTO

```php id="vk8byr"
RiderAdPlacementData
```

---

# 16. Campaign Architecture

Future campaigns may vary by:
- merchant
- issuer
- amount
- geography
- language
- settlement rail
- device
- time window

---

## Example

```text id="o9nq65"
large claims
    → premium rider
```

---

# 17. YAML Driver Integration

The YAML driver remains authoritative for:
- pre-claim splash
- form-flow structure
- callback behavior

The rider architecture must extend, not replace, the YAML driver.

---

## Current YAML Areas

```yaml id="cjlwmq"
splash_enabled
rider.message
rider.url
redirect_timeout
```

---

## Future YAML Extensions

```yaml id="8u7xb7"
rider:
  success:
    type: markdown
    content: ...
  redirect:
    url: ...
  ads:
    enabled: true
  campaign:
    id: summer-2026
```

---

# 18. Vue Layer Refactor

---

## 18.1 New Props

```ts id="6uh4z2"
rider
claimOutcome
redirectEndpoint
campaign
ads
analytics
```

---

## 18.2 New Components

```text id="j8g0pp"
RiderRenderer.vue
RiderAdPlacement.vue
RiderCountdown.vue
RiderCampaignBanner.vue
```

---

# 19. Package Route Strategy

Keep canonical routes:

```text id="4gxw3x"
GET  /x/claim/{code}/success
GET  /x/claim/{code}/redirect
```

Do not introduce parallel rider routes.

---

# 20. Analytics Events

---

## Required Events

```text id="zxw6s2"
rider.success.viewed
rider.redirect.started
rider.redirect.completed
rider.ad.viewed
rider.ad.clicked
rider.deep_link.failed
```

---

# 21. Database / Persistence Strategy

Initially:
- log-only
- event-based

Later:
- dedicated analytics tables
- attribution warehouse
- campaign metrics

---

# 22. Testing Strategy

---

## 22.1 Unit Tests

### Rider DTO
- normalization
- defaults
- campaign merge

### Redirect Resolver
- allowed scheme
- blocked scheme
- deep-link fallback

### Ad Insertion
- placement selection
- targeting logic

---

## 22.2 Feature Tests

### Success Page
- accepted_success rendering
- accepted_pending rendering
- no rider for rejected_failure

### Redirect
- valid redirect
- invalid redirect
- analytics fired

---

## 22.3 Browser Tests

### Flow
- countdown redirect
- markdown rendering
- deep-link behavior
- ad rendering
- mobile fallback

---

## 22.4 Demo Driver for Manual Testing

x-rider keeps the package default driver neutral.

For manual verification of rider behavior, use the demo driver:

```text
resources/rider-drivers/demo.yaml
```

The demo driver may include visible examples such as:

```text
pre-claim splash
success message
redirect example
link/image stages
```

Use it only in sandbox or development:

```env
X_RIDER_DRIVER=demo
```

Production should use:

```env
X_RIDER_DRIVER=default
```

or a host-specific production-safe driver.

---

# 23. Sprint Plan

---

# Sprint 1 — Rider Contracts + Safe Redirect

Deliver:
- RiderExperienceData
- resolver contracts
- redirect resolver
- Success.vue redirect endpoint usage
- redirect validation tests

---

# Sprint 2 — Outcome-Aware Rider

Deliver:
- ClaimOutcomeData
- pending rider behavior
- accepted_success vs pending UX
- rider normalization

---

# Sprint 3 — Rider Content Pipeline

Deliver:
- RiderContentPipeline
- renderer abstraction
- markdown/html/image/video support

---

# Sprint 4 — Analytics Layer

Deliver:
- RiderAnalyticsRecorder
- analytics events
- attribution logging

---

# Sprint 5 — Ad Infrastructure

Deliver:
- RiderAdPlacementData
- ad insertion service
- placement registry
- sponsor support

---

# Sprint 6 — Campaign Engine

Deliver:
- campaign resolver
- targeting
- dynamic rider selection

---

# Sprint 7 — Deep Links + Mobile Continuation

Deliver:
- app deep-link support
- mobile fallback URLs
- app install continuation

---

# Sprint 8 — Merchant Engagement Platform

Deliver:
- merchant-configurable rider campaigns
- loyalty integrations
- affiliate routing
- cross-sell surfaces

---

# 24. Migration Strategy

---

## Phase A
Keep existing:
- rider.message
- rider.url

Wrap them into:
- RiderExperienceData

---

## Phase B
Introduce:
- outcome-aware rider states
- redirect contracts

---

## Phase C
Introduce:
- campaigns
- analytics
- ads

without breaking existing vouchers.

---

# 25. Final Architecture

```text id="w9e3v8"
/x/claim
    ↓
form-flow
    ↓
claim submit
    ↓
redemption execution
    ↓
post-redemption pipeline
    ↓
ClaimOutcomeData
    ↓
RiderExperienceResolver
    ↓
Success.vue
    ↓
ClaimRedirectController
    ↓
merchant continuation
    ↓
analytics + monetization
```

---

# 26. Final Guiding Principle

> The rider is not a redirect.
> The rider is the programmable claimant experience layer of x-change.

---

END OF DOCUMENT
