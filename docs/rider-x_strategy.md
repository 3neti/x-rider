# x-rider Strategy
## Strategic Evolution Plan for the Claimant Experience Platform

**Version**: 1.0  
**Status**: Strategic Direction  
**Audience**: Core developers, AI agents, platform architects

---

# 1. Purpose

This document defines the long-term strategy for extracting and evolving the current rider implementation into a dedicated platform package:

```text id="71fftv"
3neti/x-rider
```

The goal is to transform the rider from:
- a simple success-page redirect
- a markdown message
- a splash screen

into:

```text id="p7yr6l"
a programmable claimant experience platform
```

while preserving:
- financial correctness
- redemption determinism
- x-change orchestration boundaries

---

# 2. Strategic Insight

The rider is no longer merely:
- UI decoration
- post-claim cosmetics

The rider is becoming:
- a continuation engine
- a merchant engagement surface
- a monetization surface
- a campaign runtime
- a programmable UX platform

This evolution is too large to remain embedded directly inside x-change.

---

# 3. Core Architectural Principle

## x-change owns financial truth.
## x-rider owns claimant experience.

---

## x-change responsibilities

```text id="c3omqj"
claim lifecycle
voucher execution
disbursement
wallet mutation
financial validation
redemption truth
settlement orchestration
```

---

## x-rider responsibilities

```text id="8h7jkh"
splash pages
success experiences
redirect continuation
campaigns
deep links
analytics
ads
merchant engagement
loyalty continuation
```

---

# 4. Strategic Decision

We will NOT:
- embed a full ad engine inside x-change
- tightly couple rider rendering to Vue pages
- let rider logic mutate redemption state
- let merchant UX alter payout correctness

We WILL:
- extract rider into its own package
- formalize rider contracts
- create a programmable runtime later
- allow future extensibility

---

# 5. Recommended Evolution Path

---

# PHASE 1 — NOW
# Create `3neti/x-rider`

## Goal

Extract current rider behavior into a dedicated package without changing user-visible behavior.

---

## Responsibilities

The initial package should own:

```text id="jlwmf0"
Rider DTOs
Success page orchestration
Redirect orchestration
Rider rendering
Analytics seams
Campaign seams
```

---

## Initial Scope

### Supported capabilities

| Capability | Status |
|---|---|
| markdown rider | ✔ |
| splash rendering | ✔ |
| redirect endpoint | ✔ |
| countdown redirect | ✔ |
| fallback redirect | ✔ |
| audit seam | ✔ |

---

## Package Structure

```text id="el3v6y"
packages/x-rider/
├── src/
│   ├── Contracts/
│   ├── Data/
│   ├── Services/
│   ├── Http/
│   ├── Support/
│   └── Providers/
│
├── resources/js/
│   ├── pages/
│   ├── components/
│   └── composables/
│
├── config/
│   └── x-rider.php
│
└── tests/
```

---

## Initial Contracts

```php id="dnld9l"
RiderExperienceResolverContract
SuccessRedirectResolverContract
RiderAnalyticsRecorderContract
```

---

## Initial DTOs

```php id="jlwmvz"
RiderExperienceData
RiderRedirectData
RiderAnalyticsEventData
```

---

## Initial Controllers

```text id="9owr0d"
RiderSuccessPageController
RiderRedirectController
```

---

## Integration with x-change

x-change should invoke x-rider after accepted claim outcomes only.

```text id="xj70n6"
claim accepted
    ↓
x-rider invoked
    ↓
render rider
```

---

# PHASE 2 — NEXT
# Move Existing Rider Behavior Into x-rider

## Goal

Relocate all existing rider implementation from x-change into x-rider.

---

## Move These Concerns

### Success page rendering

Move:
```text id="9odn5r"
claim/Success.vue
```

into:
```text id="nwrvko"
x-rider/pages/Success.vue
```

---

### Redirect orchestration

Move:
```text id="fufxok"
ClaimRedirectController
```

into x-rider.

---

### Rider rendering logic

Move:
- markdown rendering
- countdown logic
- fallback logic
- redirect resolution

into:
```text id="haxrl9"
RiderRendererService
```

---

## Keep These Inside x-change

x-change continues owning:
- redemption
- claim execution
- payout orchestration
- disbursement truth
- form-flow integration

---

# PHASE 3 — LATER
# Introduce Driver Runtime

## Goal

Transform x-rider into a programmable runtime platform.

---

# Architectural Shift

From:

```text id="iq4k2t"
rider = page
```

To:

```text id="ajvgr0"
rider = runtime pipeline
```

---

# Introduce Driver System

Exactly like form-flow handlers.

---

## New Contract

```php id="wd66ho"
RiderDriverContract
```

---

## Driver Responsibilities

Drivers produce normalized rider DTOs.

They do NOT directly render Vue pages.

---

## Example Drivers

```text id="xldd6w"
markdown
html
splash
image
video
deep_link
affiliate
survey
loyalty
```

---

# Example Driver Configuration

```yaml id="x9xq22"
rider:
  stages:
    - type: splash
      driver: merchant-splash

    - type: markdown
      driver: receipt-message

    - type: redirect
      driver: mobile-deep-link
```

---

# Runtime Pipeline

```text id="z45d0h"
Resolve Rider
    ↓
Resolve Campaign
    ↓
Apply Localization
    ↓
Inject Ads
    ↓
Normalize DTO
    ↓
Render
    ↓
Track Analytics
    ↓
Redirect
```

---

# PHASE 4 — MUCH LATER
# Ads + Campaign Ecosystem

## Goal

Turn x-rider into a monetizable merchant engagement ecosystem.

---

# Future Capabilities

| Capability | Description |
|---|---|
| sponsored riders | paid merchant continuation |
| ad injection | rotating ads |
| affiliate routing | monetized redirects |
| campaign analytics | attribution |
| loyalty continuation | rewards |
| app-install funnels | onboarding |
| surveys | engagement |
| merchant branding | white-label |

---

# Ad Architecture Principle

Ads must NEVER run inside:
- redemption logic
- payout execution
- voucher state mutation

Ads belong ONLY inside:
- rider rendering
- rider runtime pipeline

---

# Future Plugin Ecosystem

Third parties may publish:

```text id="5t2g4d"
x-rider-ads
x-rider-loyalty
x-rider-affiliate
x-rider-video
x-rider-survey
x-rider-analytics
```

---

# 6. Rendering Strategy

## Critical Rule

Drivers do NOT directly render Vue.

Instead:

```text id="p0u0r0"
Driver
    ↓
Normalized DTO
    ↓
Renderer
    ↓
Vue Component
```

---

# Why This Matters

This preserves:
- SSR compatibility
- mobile app compatibility
- API rendering compatibility
- testability
- future React Native/mobile support

---

# 7. Why We Are NOT Starting With Full Runtime

A full runtime immediately would:
- overcomplicate the present problem
- slow delivery
- introduce premature abstractions
- increase debugging difficulty

Instead:
- extract first
- stabilize contracts
- preserve behavior
- evolve incrementally

---

# 8. Immediate Next Step

## Create:

```text id="1v97gj"
3neti/x-rider
```

---

## First Deliverables

```text id="7vt9aj"
RiderExperienceData
RiderRedirectData
RiderSuccessPageController
RiderRedirectController
markdown rendering
countdown redirect
fallback redirect
redirect validation
```

---

# 9. Long-Term Vision

Eventually:

```text id="0eb9we"
x-change
    = financial orchestration platform

x-rider
    = claimant engagement platform
```

The rider platform may eventually outgrow voucher redemption entirely and become reusable for:
- onboarding
- inward remittance
- merchant checkout
- QR journeys
- campaign continuation
- app onboarding
- KYC completion funnels

---

# 10. Final Guiding Principle

> x-change determines whether money movement is valid.
> x-rider determines what the claimant experiences afterward.

---

END OF DOCUMENT
