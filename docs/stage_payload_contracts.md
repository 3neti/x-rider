# Stage Payload Contracts
## Canonical Rider Stage Payload Specifications

---

# Purpose

This document defines the canonical payload contracts for Rider stages.

It establishes:

- supported stage types
- canonical payload structure
- normalization expectations
- frontend/runtime guarantees
- fallback behavior
- forward compatibility rules

This document is the authoritative reference for:

```text
what a Rider stage payload should look like
```

throughout the x-rider ecosystem.

---

# Architectural Principle

A Rider stage consists of:

```yaml
type
presentation
payload
```

where:

| Component | Responsibility |
|---|---|
| type | semantic meaning |
| presentation | runtime placement semantics |
| payload | stage-specific data |

---

# Canonical Stage Structure

Normalized stages SHOULD conform to:

```yaml
- type: splash
  key: welcome-stage
  enabled: true
  presentation: inline

  payload:
    content: Welcome.
```

---

# Canonical Runtime Shape

Normalized runtime structure:

```ts
interface RiderStage {
    type: string;
    enabled: boolean;

    key?: string | null;

    phase?: string | null;

    presentation?: string | null;

    payload?: Record<string, unknown>;

    actions?: RiderRuntimeAction[];

    meta?: Record<string, unknown>;

    content?: string | null;
    content_type?: string | null;
}
```

---

## Runtime Action Shape

```ts
interface RiderRuntimeAction {
    key?: string;

    type:
        | 'redirect'
        | 'open_url'
        | 'copy_to_clipboard'
        | 'track_event'
        | 'delay'
        | 'show_stage'
        | 'close';

    timing?:
        | 'on_mount'
        | 'on_click'
        | 'after_delay'
        | 'on_complete';

    enabled?: boolean;

    requires_user_gesture?: boolean;

    external?: boolean;

    payload?: Record<string, unknown>;
}
```

---

# Field Definitions

| Field | Purpose |
|---|---|
| type | semantic stage identifier |
| enabled | runtime activation |
| key | stable runtime identity |
| phase | lifecycle placement |
| presentation | rendering/runtime mode |
| payload | stage-specific content |
| actions | runtime behavior list |
| meta | runtime metadata |
| content | normalized textual shortcut |
| content_type | normalized rendering hint |

---

## Lifecycle Phases

Supported phases currently include:

| Phase | Purpose |
|---|---|
| `pre_claim` | preview/onboarding runtime |
| `runtime` | active runtime before form-flow |
| `success` | redemption success runtime |
| `post_claim` | post-redemption experience |
| `redirect` | redirect runtime orchestration |

Lifecycle semantics are intentionally isolated.

---

## Presentation Modes

Supported presentation modes currently include:

| Mode | Purpose |
|---|---|
| `inline` | render inside page flow |
| `modal` | blocking dismissible overlay |
| `fullscreen` | immersive runtime surface |

Blocking presentations are sequenced one at a time by the runtime sequencer.

---

# Payload Design Philosophy

Payloads are intentionally:

- frontend-agnostic
- transport-safe
- serializable
- runtime-oriented
- extensible
- loosely coupled

Payloads SHOULD NOT contain:

- framework-specific objects
- executable JavaScript
- renderer internals
- DOM references
- Vue/React components

---

# Supported Stage Types

## 1. splash

### Purpose

Rich textual or introductory experience surface.

### Canonical Payload

```yaml
payload:
  content: Welcome.
  content_type: markdown
  timeout: 3
```

### Payload Fields

| Field | Type | Purpose |
|---|---|---|
| content | string | stage body |
| content_type | string | rendering hint |
| timeout | int | optional display duration |

### Example

```yaml
- type: splash
  presentation: fullscreen

  payload:
    content: |
      Welcome to the rewards experience.
    content_type: markdown
    timeout: 5
```

---

## 2. message

### Purpose

Simple informational text.

### Canonical Payload

```yaml
payload:
  content: Thank you.
  content_type: text
```

### Example

```yaml
- type: message

  payload:
    content: Thank you for claiming.
```

---

## 3. image

### Purpose

Display image/media content.

### Canonical Payload

```yaml
payload:
  src: https://example.com/banner.jpg
  alt: Promotional banner
```

### Payload Fields

| Field | Type | Purpose |
|---|---|---|
| src | string | image source |
| alt | string | accessibility text |

### Example

```yaml
- type: image
  presentation: inline

  payload:
    src: https://placehold.co/1200x400
    alt: Campaign banner
```

---

## 4. link

### Purpose

Interactive external or internal navigation.

### Canonical Payload

```yaml
payload:
  label: Learn more
  url: https://example.com
```

### Payload Fields

| Field | Type | Purpose |
|---|---|---|
| label | string | display text |
| url | string | target destination |

### Example

```yaml
- type: link

  payload:
    label: Learn more
    url: https://example.com
```

---

## 5. redirect

### Purpose

Runtime redirect orchestration.

Redirect stages are runtime-driven.

Claim preview surfaces must never execute redirect runtime.

### Canonical Payload

```yaml
payload:
  url: https://merchant.example.com
  timeout: 5
  fallback_url: /x/claim
  external: true
```

### Payload Fields

| Field | Type | Purpose |
|---|---|---|
| url | string | redirect destination |
| timeout | int | delay before redirect |
| fallback_url | string | fallback runtime route |
| external | bool | external runtime hint |

### Example

```yaml
- type: redirect
  phase: redirect

  payload:
    url: https://merchant.example.com
    timeout: 5
    external: true
```

### Runtime Behavior

Redirect stages are internally normalized into runtime actions.

Example runtime behavior:

```text
delay → redirect
```

Redirect countdown rendering belongs to the frontend runtime sequencer.

---

# Runtime Action Support

Stages may declare runtime actions.

Example:

```yaml
- type: cta
  key: reward-cta
  phase: pre_claim
  presentation: inline

  payload:
    label: Open Reward
    url: https://example.com/reward

  actions:
    - type: open_url
      timing: on_click
      requires_user_gesture: true

      payload:
        url: https://example.com/reward
        target: _blank
```

---

## Runtime Action Principle

Stages describe:

```text
what the user sees
```

Runtime actions describe:

```text
what the runtime does
```

This distinction is foundational.

---

## Supported Runtime Actions

| Action | Purpose |
|---|---|
| redirect | navigate current window |
| open_url | open external/internal URL |
| copy_to_clipboard | copy text |
| track_event | emit analytics/runtime event |
| delay | pause runtime sequence |
| show_stage | reveal hidden stage |
| close | dismiss runtime surface |

---

## Supported Timings

| Timing | Meaning |
|---|---|
| on_mount | execute when stage enters runtime |
| on_click | execute from user interaction |
| after_delay | execute after runtime delay |
| on_complete | execute after stage completion |

---

# Runtime Normalization Rules

## Raw YAML MAY Be Short Form

Example:

```yaml
- type: splash
  content: Welcome.
```

Normalization SHOULD produce:

```yaml
payload:
  content: Welcome.
```

---

## Legacy Fields

The runtime MAY normalize legacy fields into payloads.

Example:

```yaml
- type: image
  src: https://example.com/image.jpg
```

becomes:

```yaml
payload:
  src: https://example.com/image.jpg
```

---

## Frontend Runtime Guarantee

Frontend runtimes SHOULD assume:

```text
payload contains the canonical normalized structure
```

and SHOULD avoid depending on raw YAML shortcuts.

---

## Unknown Payload Fields

Unknown payload fields SHOULD:

- remain preserved
- pass through normalization
- avoid destructive stripping

This allows forward compatibility.

### Example

```yaml
payload:
  sponsor_id: abc123

  analytics:
    campaign: summer
```

SHOULD survive normalization unchanged.

---

# Runtime Action Normalization

Runtime actions normalize invalid values safely.

---

## Invalid Action Types

Unknown action types normalize into:

```text
track_event
```

---

## Invalid Timings

Unknown timings normalize into:

```text
on_click
```

---

## Invalid Delays

Negative delays normalize into:

```text
0
```

---

## Disabled Actions

Actions with:

```yaml
enabled: false
```

must never execute.

---

# Meta vs Payload

## payload

Represents:

```text
stage business/runtime content
```

Examples:

- image source
- text body
- redirect target

---

## meta

Represents:

```text
runtime metadata
```

Examples:

- normalization source
- timestamps
- runtime annotations
- analytics hints

### Example

```yaml
payload:
  label: Learn more

meta:
  normalized_from: legacy-link
```

---

# Reserved Payload Keys

The following payload keys are reserved by x-rider:

| Key | Meaning |
|---|---|
| content | textual body |
| content_type | rendering hint |
| src | media source |
| alt | accessibility text |
| label | display label |
| url | target URL |
| timeout | runtime timing |
| fallback_url | fallback redirect |

Future runtimes SHOULD avoid redefining these semantics.

---

# Content Types

Supported content types currently include:

| Type | Meaning |
|---|---|
| text | plain text |
| markdown | markdown rendering |
| html | trusted HTML |

---

# Runtime Responsibility Boundary

x-rider defines:

```text
payload semantics
runtime semantics
lifecycle semantics
runtime action semantics
```

Frontend runtimes define:

```text
visual rendering
animations
accessibility
interaction UX
presentation implementation
```

x-change defines:

```text
lifecycle orchestration
phase projection
runtime payload transport
```

This separation is intentional.

---

# Runtime Fallback Rules

## Unknown Stage Types

Unknown types SHOULD:

- fail gracefully
- avoid crashing runtime
- optionally render generic payload views

---

## Missing Payloads

Stages without payloads SHOULD:

- render safely
- degrade gracefully
- avoid hard runtime failures

---

# Security Considerations

Payloads MAY contain:

- markdown
- HTML
- URLs
- external assets

Frontend runtimes SHOULD:

- sanitize HTML
- validate URLs
- protect against XSS
- avoid arbitrary script execution

---

# Future Runtime Evolution

Planned future runtime features may include:

```yaml
- type: video
- type: qr
- type: countdown
- type: poll
- type: carousel
- type: sponsor
```

Future runtime capabilities may include:

- runtime analytics transport
- sponsor runtime orchestration
- runtime persistence
- cross-device runtime continuity
- runtime replay
- mobile-native runtimes
- kiosk runtimes
- multi-client runtime protocols

The payload contract system is intentionally extensible.

---

# Canonical Ownership

x-rider owns:

- stage semantics
- payload normalization
- runtime payload contracts

Frontend runtimes own:

- rendering implementation
- animations
- accessibility
- interaction behavior

---

# Lifecycle Isolation Guarantee

Lifecycle phases are intentionally isolated.

| Surface | Allowed Phases |
|---|---|
| Claim Preview | `pre_claim`, `runtime` |
| Success Runtime | `success`, `post_claim`, `redirect` |

This prevents:

- redirect leakage into preview runtime
- pre-claim replay after redemption
- runtime execution at incorrect lifecycle stages

These guarantees are formally tested.

---

# Guiding Principle

A useful heuristic:

| Concern | Owner |
|---|---|
| "What data does this stage contain?" | x-rider |
| "How should the UI display it?" | frontend runtime |

This principle should guide future Rider stage evolution.