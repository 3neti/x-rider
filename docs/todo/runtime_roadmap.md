# x-rider / x-change Runtime Roadmap
## From Voucher UX to Programmable Financial Runtime

---

# Current State

You have already completed the most important foundational work:

- lifecycle-aware claim flow
- rider normalization
- stage runtime semantics
- package boundaries
- runtime projection contracts
- success/runtime isolation
- preview/runtime documentation

The system is no longer:
- “rendering random rider blobs”

It is now:
- a structured runtime architecture

---

# Phase 6 — Lifecycle Isolation
## Status: ✅ COMPLETE

---

## What We Achieved

### 1. Runtime Phase Semantics

Stages now have deterministic lifecycle placement:

```text
pre_claim
runtime
success
post_claim
redirect
```

This is the first true runtime execution boundary.

---

### 2. Clean Responsibility Split

## ClaimWidget.vue

Owns:
- pre_claim
- runtime

Does NOT execute:
- redirect

---

## Success.vue

Owns:
- success
- post_claim
- redirect

Does NOT render:
- pre_claim

---

### 3. Architectural Consequences

This enables:

- deterministic UX behavior
- safe redirect execution
- future replay/resume
- stateful runtime orchestration
- programmable lifecycle sequencing

Without this isolation, future runtime behavior would become chaotic.

---

### 4. Package Boundary Stabilization

You formalized:

| Package | Responsibility |
|---|---|
| x-change | lifecycle + orchestration |
| x-rider | runtime semantics |
| x-ray | inspection runtime |
| host app | branding + deployment |

This was critical.

---

# Phase 7 — Runtime Actions
## NEXT PHASE

---

## Goal

Turn stages from:
- passive content

into:
- controlled runtime behavior

---

## Key Architectural Shift

Current:

```text
stage
→ render
```

Phase 7:

```text
stage
→ action
→ runtime executor
→ controlled behavior
```

---

## Runtime Actions

Examples:

```yaml
actions:
  - type: redirect
  - type: open_url
  - type: copy_to_clipboard
  - type: delay
  - type: track_event
```

---

## What This Unlocks

### Controlled Runtime Orchestration

Examples:

```text
show splash
wait 3 seconds
redirect to GCash
```

---

### Interactive Runtime Experiences

Examples:

```text
copy account number
open merchant app
show QR
track acknowledgement
```

---

### Runtime Safety Layer

Most important rule:

```text
Runtime actions may NEVER affect:
- redemption correctness
- payout execution
- settlement state
```

This keeps:
- x-change financially deterministic
- x-rider experience-oriented

---

## Main Deliverables

### Frontend

- RiderRuntimeAction types
- runtime executor
- action registry
- action-safe sequencer
- CTA-aware presenters

### Backend

- runtime action DTOs
- action serialization
- validation rules

### Tests

- duplicate execution prevention
- lifecycle isolation verification
- redirect safety

---

## End State

x-rider becomes:
- a runtime engine
- not merely a renderer

---

# Phase 8 — Driver Composition Runtime

---

## Goal

Move from:
- hand-authored stage arrays

to:
- reusable runtime drivers

---

## Key Architectural Shift

Current:

```yaml
stages:
  - type: splash
  - type: redirect
```

Phase 8:

```yaml
driver: sponsor_redirect
driver: onboarding_campaign
driver: gcash_cashout
```

---

## Runtime Flow

```text
Driver
→ DTO
→ RiderExperienceData
→ Runtime
```

---

## What Drivers Become

Drivers become:
- reusable runtime generators
- experience templates
- composable runtime modules

---

## Example Drivers

### Sponsor Driver

```text
show sponsor splash
track sponsor view
redirect to merchant
```

---

### Loyalty Driver

```text
show reward stage
collect acknowledgement
continue to payout
```

---

### EMI-Specific Driver

```text
show GCash-specific UX
deep-link to app
fallback to QR
```

---

## Major Deliverables

### Driver Registry

```text
DriverContract
DriverResolver
DriverFactory
```

---

### Composition Pipelines

```text
voucher
→ rider config
→ driver
→ stage collection
```

---

### Host Extensibility

Banks and integrators can later create:

```text
custom drivers
custom campaigns
custom runtime templates
```

without modifying x-change core.

---

## End State

x-rider becomes:
- a programmable runtime composition engine

---

# Phase 9 — x-ray Extraction + Inspection Runtime

---

## Goal

Create the “understanding layer” for Pay Codes.

---

## Key Architectural Shift

Current:

```text
claim flow
```

Phase 9:

```text
claim flow
+ inspection flow
+ disclosure runtime
```

---

## What x-ray Becomes

x-ray becomes:

```text
portable inspection runtime
```

for:
- Pay Codes
- requirements
- validations
- claim consequences
- redirect behavior
- settlement intent

---

## Example Questions x-ray Answers

```text
What does this Pay Code do?
What is required to claim it?
What happens after claim?
Is KYC required?
Will it redirect?
Can it be partially withdrawn?
```

---

## Major Deliverables

### Public Inspection Runtime

Safe preview contracts.

---

### Disclosure Policies

Different levels:

```text
guest
operator
admin
issuer
```

---

### Trust Projection

Examples:

```text
verified issuer
bank-backed
KYC-required
cash-backed
```

---

### Runtime Visualization

Potentially:

```text
timeline
stages
requirements
redirect destinations
```

---

## Why This Matters

This is what transforms:
- a voucher code

into:
- a trustworthy programmable instrument

---

## End State

x-ray becomes:
- the “browser” for Pay Codes

---

# Phase 10 — Analytics + Monetization Runtime

---

## Goal

Allow runtime experiences to become:
- measurable
- attributable
- monetizable

WITHOUT affecting financial correctness.

---

## Key Architectural Rule

```text
Ads/campaigns must never affect payout correctness.
```

This is sacred.

---

## What Gets Added

### Analytics Event Bus

Examples:

```text
stage viewed
redirect clicked
runtime completed
cta acknowledged
campaign converted
```

---

### Runtime Attribution

Examples:

```text
affiliate source
merchant campaign
bank promo
reward redemption
```

---

### Loyalty Runtime

Examples:

```text
cashback stage
merchant points
repeat-use incentives
```

---

### Sponsor Runtime

Examples:

```text
branded success flow
partner splash
co-branded payout journey
```

---

## Major Deliverables

- analytics recorder contract
- runtime event DTOs
- attribution tracking
- sponsor runtime
- loyalty runtime
- campaign orchestration

---

## End State

x-rider becomes:
- an economic experience layer

around:
- financial workflows

---

# Phase 11 — Multi-Client Runtime Protocol

---

## Goal

Make the runtime portable beyond Vue/web.

---

## Key Architectural Shift

Current:

```text
DTO
→ Vue renderer
```

Phase 11:

```text
DTO
→ web renderer
→ mobile renderer
→ kiosk renderer
→ bank app renderer
→ API consumer
```

---

## What Changes

The runtime becomes:
- transport-agnostic
- renderer-agnostic

---

## Target Clients

### Web

Current implementation.

---

### Mobile

Native:
- iOS
- Android
- Flutter
- React Native

---

### Embedded Bank Apps

Example:

```text
EastWest app
DBP app
GCash embedded runtime
```

---

### Kiosk / Terminal

Example:

```text
cash-out kiosk
government payout terminal
merchant terminal
```

---

## Major Deliverables

### Renderer Contracts

```text
RendererInterface
ClientCapabilities
HydrationProtocol
```

---

### Capability Negotiation

Example:

```text
supports_deep_link
supports_clipboard
supports_modal
supports_camera
```

---

### Headless Runtime APIs

Allow clients to:
- consume runtime DTOs
- render natively

---

## End State

x-rider becomes:
- a runtime protocol
- not merely a frontend subsystem

---

# Final Architectural Trajectory

The evolution is roughly:

---

## Initial State

```text
voucher redemption flow
```

---

## Current State

```text
structured lifecycle runtime
```

---

## After Phase 7

```text
runtime action engine
```

---

## After Phase 8

```text
programmable runtime composition platform
```

---

## After Phase 9

```text
portable inspection + trust runtime
```

---

## After Phase 10

```text
economic runtime ecosystem
```

---

## After Phase 11

```text
multi-client runtime protocol
```

---

# The Bigger Picture

At the end of this roadmap:

x-change becomes:
- the financial orchestration layer

x-rider becomes:
- the programmable experience runtime

x-ray becomes:
- the inspection and trust runtime

Together they form:

```text
A programmable financial experience platform.
```