# x-rider Stage Runtime

Version: 1.0  
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
```

Frontend-rendered stages:

```text
message
splash
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
```

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
  content: "Welcome."
  timeout: 2
```

Normalizes into:

```php
RiderExperienceData::preClaim
```

Result:

```php
$experience->preClaim
```

---

# 11. Precedence Rules

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

## 11.1 Message Precedence

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

## 11.2 Redirect URL Precedence

```text
rider.url
    >
latest enabled redirect stage payload.url
    >
rider.redirect.url
```

---

## 11.3 Redirect Timeout Precedence

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

## 11.4 Splash / Pre-Claim Precedence

```text
rider.pre_claim
    >
latest enabled splash stage
    >
null
```

---

# 12. Package Default Rule

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

# 13. Frontend Rendering Contract

The x-change success page currently uses:

```vue
<RiderStageRenderer :stages="riderStages" />
<RiderCountdown :redirect="riderRedirect" />
```

Current rendered stages:

```text
message
splash
link
```

Redirect stages are intentionally excluded from rendering.

Redirect orchestration remains centralized.

---

# 14. Safe Redirect Rule

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

# 15. Analytics Rule

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

# 16. Extension Rules

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

# 17. Deferred Runtime Types

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

# 18. Architectural Principles

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

# 19. Current Status

Completed:

```text
✅ stage DTOs
✅ stage collection
✅ stage driver contract
✅ stage driver registry
✅ message stage driver
✅ redirect stage driver
✅ splash stage driver
✅ stage resolver
✅ stages attached to RiderExperienceData
✅ stage renderer scaffold
✅ message normalization
✅ redirect normalization
✅ splash/pre-claim normalization
✅ safe redirect runtime
✅ legacy compatibility
```

Deferred:

```text
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

# 20. Summary

The x-rider stage runtime now provides:

```text
programmable post-transaction orchestration
stable RiderExperienceData contracts
safe redirect handling
backward compatibility
extensible runtime architecture
```

while keeping x-change decoupled from individual stage implementations.