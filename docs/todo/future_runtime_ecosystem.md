# Future Runtime Ecosystem

## Goal

Evolve x-rider from a runtime presentation layer into a portable runtime ecosystem for:

- campaigns
- sponsor experiences
- merchant engagement
- loyalty flows
- app-install funnels
- affiliate routing
- embedded runtime experiences

while preserving:

```text id="h0i5mt"
financial isolation
```

The Rider runtime must never compromise:

- redemption correctness
- settlement correctness
- payout correctness
- ledger integrity

---

# Core Principle

```text id="w1sv0z"
Presentation runtime is programmable.
Settlement runtime is not.
```

x-rider owns:

- UX/runtime orchestration
- Rider stages
- campaigns
- sponsor runtime
- engagement flows
- runtime analytics

x-change owns:

- redemption
- disbursement
- transfer correctness
- wallet/account state
- settlement execution

---

# Future Runtime Areas

## 1. Structured Splash Drivers

Replace arbitrary depositor HTML with structured drivers:

```yaml id="5bqz54"
driver: dedication_splash
```

Examples:

- dedication_splash
- campaign_splash
- sponsor_splash
- announcement_splash
- receipt_splash
- onboarding_splash

Goal:

```text id="3gw1t1"
data-driven runtime rendering
```

instead of arbitrary markup authoring.

---

## 2. Runtime Driver Registry

Formalize:

```text id="bzr8of"
Driver → DTO → Renderer
```

Potential architecture:

```text id="kzrx8o"
RiderDriverRegistry
RiderRendererRegistry
RiderStageFactory
```

Drivers should remain:

- frontend-agnostic
- transport-safe
- mobile-compatible

---

## 3. Portable Runtime Rendering

Allow Rider experiences to render across:

- Vue/web
- mobile
- kiosk
- embedded browser
- API-only surfaces

Goal:

```text id="7xndgi"
same Rider DTOs
different renderers
```

---

## 4. Runtime Analytics

Future runtime analytics may include:

- stage impressions
- CTA clicks
- dismissals
- redirect starts
- redirect completions
- runtime duration
- abandonment points

Potential future contracts:

```text id="w3yv0r"
RiderAnalyticsRecorderContract
RiderRuntimeTelemetry
```

---

## 5. Campaign Runtime

Allow reusable runtime campaigns:

```yaml id="x8g0w3"
campaign:
  id: summer-promo-2027
```

Possible capabilities:

- merchant campaigns
- sponsor inventory
- A/B runtime testing
- localized campaigns
- runtime targeting

---

## 6. Sponsor / Ad Runtime

Future sponsor runtime must obey:

```text id="6v5dfj"
presentation isolation
```

Ads may influence presentation only.

They must never influence:

- redemption outcome
- payout routing
- settlement correctness
- financial execution

---

## 7. Affiliate Runtime

Potential future runtime actions:

- affiliate_redirect
- app_install
- loyalty_claim
- merchant_follow
- referral_capture

All affiliate logic must remain runtime-only.

---

## 8. Runtime Sandboxing

Long-term runtime hardening may include:

- iframe sandboxing
- CSP enforcement
- isolated runtime containers
- runtime capability restrictions
- runtime permission model

---

## 9. Runtime Capability Model

Potential future runtime permissions:

```yaml id="2qqkj6"
capabilities:
  clipboard: true
  redirect: true
  external_links: false
```

This enables:

- kiosk-safe runtimes
- embedded runtime restrictions
- bank-controlled runtime behavior

---

# Long-Term Runtime Vision

```text id="fjlwmx"
x-rider becomes a programmable engagement runtime layered safely above settlement infrastructure.
```

The runtime may evolve aggressively.

The settlement layer must remain conservative, isolated, and deterministic.