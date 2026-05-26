# x-rider Stage Runtime

Version: 1.1  
Status: Stabilized Runtime Contract

---

# 1. Purpose

The x-rider stage runtime converts rider configuration into canonical post-transaction experience data.

It allows rider behavior to be described as ordered stages:

```yaml
rider:
  stages:
    - type: message
      content: "Thank you."

    - type: redirect
      url: "https://merchant.example.com"
      timeout: 5
```

The stage runtime does not replace the existing `RiderExperienceData` contract. Instead, it feeds and enriches it.

The runtime is designed to support:

```text
programmable post-transaction experiences
safe redirects
campaign orchestration
merchant-controlled UX
future ad/campaign systems
```

while preserving backward compatibility with legacy rider configuration.

---

# 2. Runtime Flow

```text
driver YAML / runtime rider context
    ↓
DefaultRiderStageResolver
    ↓
RiderStageCollectionData
    ↓
DefaultRiderExperienceResolver
    ↓
RiderExperienceData
    ↓
Frontend renderer / redirect runtime
```

The runtime is intentionally layered:

```text
stages = programmable orchestration layer
RiderExperienceData = stable application contract
```

Applications should consume `RiderExperienceData`, not raw stage arrays directly.

---

# 3. Canonical Stage Shape

A stage is represented by:

```php
RiderStageData(
    type,
    enabled,
    key,
    presentation,
    payload,
    meta,
)
```

Serialized example:

```json
{
  "type": "message",
  "enabled": true,
  "key": "thank-you-message",
  "presentation": "inline",
  "payload": {
    "content": "Thank you.",
    "content_type": "markdown"
  },
  "meta": {}
}
```

---

# 4. RiderStageData

## Fields

| Field | Type | Description |
|---|---|---|
| `type` | string | Runtime stage type |
| `enabled` | bool | Whether the stage is active |
| `key` | string/null | Stable identifier |
| `presentation` | string | Runtime presentation mode |
| `payload` | array | Driver-specific data |
| `meta` | array | Additional metadata |

---

# 5. RiderStageCollectionData

Stages are grouped into:

```php
RiderStageCollectionData
```

Helper methods include:

```php
firstOfType(string $type)
renderable()
redirectLike()
```

The collection acts as the canonical intermediate runtime representation before normalization into `RiderExperienceData`.

---

# 6. Supported Stage Types

Current supported stage types:

```text
message
redirect
splash
image
link
```

Implemented drivers:

```text
MessageStageDriver
RedirectStageDriver
SplashStageDriver
ImageStageDriver
LinkStageDriver
```

Frontend-rendered stages:

```text
message
splash
image
link
```

Redirect stages are not rendered directly. They normalize into `RiderRedirectData`.

---

# 7. Stage Driver Contract

All stage drivers implement:

```php
RiderStageDriverContract
```

Contract:

```php
public function type(): string;

public function make(array $config = [], array $context = []): RiderStageData;
```

Rules:

```text
Drivers produce data only.
Drivers do not render Vue.
Drivers do not redirect users.
Drivers do not perform side effects.
Drivers should remain deterministic.
```

---

# 8. Stage Resolver Contract

The stage resolver implements:

```php
RiderStageResolverContract
```

Contract:

```php
public function resolve(array $rider = [], array $context = []): RiderStageCollectionData;
```

Responsibilities:

```text
normalize rider configuration
support legacy compatibility
build stage DTOs
maintain runtime ordering
preserve payload semantics
```

---

# Stage Phase Scoping

Stages may optionally declare `phase`.

If omitted, phase is inferred:

| Rule | Phase |
|---|---|
| inline splash/image/link/cta | pre_claim |
| message | success |
| modal/fullscreen | runtime |
| redirect | redirect |

Explicit phase always wins.

---

# 9. Legacy Compatibility

Legacy rider shape remains supported:

```yaml
rider:
  message: "Thank you."
  url: "https://merchant.example.com"
  redirect_timeout: 5
  splash: "Welcome."
  splash_timeout: 2
```

Legacy fields are converted internally into stage-compatible runtime behavior.

This preserves backward compatibility for:

```text
existing YAML drivers
older x-change integrations
host applications
```

---

# 10. Normalization into RiderExperienceData

Stages normalize into canonical experience fields.

---

## 10.1 Message Stage

Stage:

```yaml
- type: message
  content: "Thank you."
  content_type: markdown
```

Normalizes into:

```php
RiderExperienceData::success
```

Result:

```php
$experience->success
```

---

## 10.2 Redirect Stage

Stage:

```yaml
- type: redirect
  url: "https://merchant.example.com"
  timeout: 5
  fallback_url: /x/claim
```

Normalizes into:

```php
RiderExperienceData::redirect
```

Result:

```php
$experience->redirect
```

---

## 10.3 Splash Stage

Stage:

```yaml
- type: splash
  presentation: inline
  content: "Welcome."
  timeout: 2
```

Normalizes into a rider stage payload:

```php
$experience->stages
```

and may also hydrate the embedded pre-claim experience surface:

```php
$experience->preClaim
```

Current runtime behavior:

```text
presentation: inline
```

renders inside the voucher preview page as the embedded pre-claim surface.

Future runtime behavior:

```text
presentation: modal
presentation: fullscreen
```

are recognized by the runtime but deferred for later orchestration slices.

Result:

```php
$experience->preClaim
$experience->stages
```

Example resolved payload:

```php
$experience->preClaim?->content
// "Welcome."

$experience->preClaim?->meta
// [
//     'stage_key' => '...',
//     'presentation' => 'inline',
// ]
```

The canonical stage data remains available through:

```php
$experience->stages
```

which allows future modal/fullscreen runtime orchestration without changing the normalized rider contract.

---

## 10.4 Image Stage

Stage:

```yaml
- type: image
  presentation: inline
  src: https://placehold.co/1200x400
  alt: "Hero banner"
```

Normalizes into:

```php
$experience->stages
```

Payload example:

```php
[
    'src' => 'https://placehold.co/1200x400',
    'alt' => 'Hero banner',
]
```

---

## 10.5 Link Stage

Stage:

```yaml
- type: link
  label: "Learn more"
  url: "https://example.com"
```

Normalizes into:

```php
$experience->stages
```

Payload example:

```php
[
    'label' => 'Learn more',
    'url' => 'https://example.com',
]
```

---

# 11. Presentation Modes

Presentation mode defines how the frontend should surface the stage.

Supported modes:

| Mode | Meaning |
|---|---|
| `inline` | Embedded directly into the current screen |
| `modal` | Interruptive overlay |
| `fullscreen` | Full immersive takeover |

Current runtime support:

| Mode | Status |
|---|---|
| inline | Implemented |
| modal | Reserved |
| fullscreen | Reserved |

The runtime recognizes all presentation modes now to preserve forward compatibility.

Frontend runtimes SHOULD gracefully ignore unsupported modes.

---

# 12. Precedence Rules

The runtime follows strict precedence rules.

General model:

```text
runtime/context legacy fields
    >
explicit/latest matching stage
    >
driver defaults
    >
config fallback
```

---

## 12.1 Message Precedence

```text
rider.message
    >
latest enabled message stage
    >
rider.success.content
    >
x-rider.defaults.success_message
```

---

## 12.2 Redirect URL Precedence

```text
rider.url
    >
latest enabled redirect stage payload.url
    >
rider.redirect.url
```

---

## 12.3 Redirect Timeout Precedence

```text
rider.redirect_timeout
    >
latest enabled redirect stage payload.timeout
    >
rider.redirect.timeout
    >
x-rider.defaults.redirect_timeout
```

---

## 12.4 Splash / Pre-Claim Precedence

```text
rider.pre_claim
    >
latest enabled splash stage
    >
null
```

---

# 13. Package Default Rule

Package default configuration must remain safe and neutral.

Recommended baseline:

```yaml
rider:
  stages: []
```

The package default should not inject:

```text
ads
campaigns
merchant redirects
splash screens
marketing content
forced redirects
```

Demonstration content belongs in:

```text
fixtures
sandbox drivers
tests
demo configurations
```

not package defaults.

---

## 13.1 Demo Driver

The package may include a separate demo driver:

```text
resources/rider-drivers/demo.yaml
```

The demo driver is intended for:

```text
manual testing
sandbox verification
developer onboarding
visual confirmation of stage behavior
```

Example demo behavior may include:

```text
pre-claim splash
success message
disabled redirect example
link/image samples
```

The demo driver must not be used as the production default.

To test the demo driver in a host app, publish the rider drivers:

```bash
php artisan vendor:publish --tag=x-rider-drivers --force
```

Then configure the host to use the demo driver temporarily:

```php
config(['x-rider.driver' => 'demo']);
```

or pass runtime context explicitly:

```php
$riders->resolve($subject, [
    'driver' => 'demo',
]);
```

For local testing only, the host may also set the default driver in configuration:

```php
'driver' => env('X_RIDER_DRIVER', 'default'),
```

and then use:

```env
X_RIDER_DRIVER=demo
```

Production deployments should use:

```env
X_RIDER_DRIVER=default
```

or an explicit production-safe custom driver.

Rule:

```text
default.yaml = neutral baseline
demo.yaml = visible testing behavior
custom driver = host/application-specific behavior
```

---

# 14. Frontend Rendering Contract

The x-change preview runtime currently uses:

```vue
<RiderRenderer :content="preClaimContent" />
<RiderStagePresenter :stage="stage" />
```

Current rendered stages:

```text
message
splash
image
link
```

Redirect stages are intentionally excluded from rendering.

Redirect orchestration remains centralized.

---

# 15. Safe Redirect Rule

External redirects are never opened directly from stage renderers.

All redirects pass through:

```text
ClaimRedirectController
DefaultSuccessRedirectResolver
```

Safety checks include:

```text
allowed host validation
javascript: URL blocking
fallback routing
redirect enablement checks
```

Allowed hosts:

```php
x-rider.redirects.allowed_hosts
```

Development-only escape hatch:

```env
X_RIDER_ALLOW_ANY_REDIRECT_HOST=true
```

This should never be enabled in production.

---

# 16. Analytics Rule

Stage drivers themselves do not emit analytics.

Analytics should occur only at orchestration boundaries such as:

```text
redirect start
redirect completion
campaign impression
claim completion
```

This keeps drivers pure and deterministic.

---

# 17. Extension Rules

Future stage drivers should follow these rules:

```text
1. Register a driver implementing RiderStageDriverContract.
2. Convert config/context into RiderStageData.
3. Avoid side effects.
4. Add unit tests.
5. Add frontend renderers only if visual.
6. Keep redirect/security behavior centralized.
7. Preserve backward compatibility.
```

---

# 18. Deferred Runtime Types

Deferred runtime types include:

```text
ads
campaigns
affiliate
survey
video
deep_link
A/B testing
personalization
loyalty
SSR
```

These should extend the stage runtime rather than bypass it.

---

# 19. Architectural Principles

The stage runtime follows these principles:

```text
stable app contracts
progressive normalization
safe defaults
backward compatibility
deterministic runtime behavior
frontend/backend separation
```

The runtime intentionally separates:

```text
stage orchestration
frontend rendering
redirect execution
analytics recording
```

into distinct layers.

---

# 20. Current Status

Completed:

```text
✅ stage DTOs
✅ stage collection
✅ stage driver contract
✅ stage driver registry
✅ message stage driver
✅ redirect stage driver
✅ splash stage driver
✅ image stage driver
✅ link stage driver
✅ stage resolver
✅ stages attached to RiderExperienceData
✅ stage renderer scaffold
✅ image stage renderer
✅ link stage renderer
✅ message normalization
✅ redirect normalization
✅ splash/pre-claim normalization
✅ presentation mode semantics
✅ safe redirect runtime
✅ legacy compatibility
```

Deferred:

```text
⬜ modal orchestration runtime
⬜ fullscreen orchestration runtime
⬜ campaign runtime
⬜ ads orchestration
⬜ affiliate flows
⬜ surveys
⬜ personalization
⬜ multi-step stage timelines
⬜ SSR optimization
⬜ async analytics pipeline
```

---

# 21. Summary

The x-rider stage runtime now provides:

```text
programmable post-transaction orchestration
stable RiderExperienceData contracts
safe redirect handling
visual stage orchestration
presentation semantics
backward compatibility
extensible runtime architecture
```

while keeping x-change decoupled from individual stage implementations.