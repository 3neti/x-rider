# x-rider Implementation Plan

## Phase 1 — Package Skeleton

Create `3neti/x-rider` with:

```text
RiderExperienceData
RiderRedirectData
RiderContentData
RiderCampaignData
RiderAnalyticsEventData
RiderSubjectData
```

Contracts:

```text
RiderExperienceResolverContract
SuccessRedirectResolverContract
RiderAnalyticsRecorderContract
RiderCampaignResolverContract
RiderRendererContract
RiderDriverContract
```

Services:

```text
DefaultRiderExperienceResolver
DefaultSuccessRedirectResolver
DefaultRiderCampaignResolver
LogRiderAnalyticsRecorder
RiderRenderer
```

## Phase 2 — x-change Integration

Keep `/x/claim` as the financial flow.

After accepted claim outcome:

```text
x-change ClaimSuccessPageController
    → x-rider RiderExperienceResolver
    → render rider payload
```

Redirects must go through:

```text
/x/claim/{code}/redirect
    → x-rider SuccessRedirectResolverContract
```

Never redirect directly to `rider.url` from Vue.

## Phase 3 — Move Current Rider Behavior

Move from x-change into x-rider:

```text
Success.vue rider rendering
countdown redirect
fallback redirect
markdown/text handling
redirect safety
analytics seam
```

x-change keeps:

```text
claim execution
redemption
withdrawal
disbursement
form-flow integration
voucher state
```

## Phase 4 — Driver Runtime

Later introduce driver-driven rider stages:

```text
splash
markdown
image
video
redirect
deep_link
survey
loyalty
affiliate
```

Drivers should output DTOs, not Vue.

```text
Driver → DTO → Renderer → Vue/API/Mobile
```

## Phase 5 — Monetization Ecosystem

Much later add:

```text
ads
campaigns
affiliate routing
app-install funnels
merchant loyalty
analytics dashboards
```

Rule:

```text
Ads and campaigns live only in x-rider.
They must never affect redemption or payout correctness.
```