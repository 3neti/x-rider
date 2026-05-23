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
    presentation?: string | null;
    payload?: Record<string, unknown>;
    meta?: Record<string, unknown>;
}
```

---

# Field Definitions

| Field | Purpose |
|---|---|
| type | semantic stage identifier |
| enabled | runtime activation |
| key | stable identifier |
| presentation | rendering intent |
| payload | stage-specific content |
| meta | runtime metadata |

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

---

# 1. splash

## Purpose

Rich textual or introductory experience surface.

---

## Canonical Payload

```yaml
payload:
  content: Welcome.
  content_type: markdown
  timeout: 3
```

---

## Payload Fields

| Field | Type | Purpose |
|---|---|---|
| content | string | stage body |
| content_type | string | rendering hint |
| timeout | int | optional display duration |

---

## Example

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

# 2. message

## Purpose

Simple informational text.

---

## Canonical Payload

```yaml
payload:
  content: Thank you.
  content_type: text
```

---

## Example

```yaml
- type: message
  payload:
    content: Thank you for claiming.
```

---

# 3. image

## Purpose

Display image/media content.

---

## Canonical Payload

```yaml
payload:
  src: https://example.com/banner.jpg
  alt: Promotional banner
```

---

## Payload Fields

| Field | Type | Purpose |
|---|---|---|
| src | string | image source |
| alt | string | accessibility text |

---

## Example

```yaml
- type: image
  presentation: inline
  payload:
    src: https://placehold.co/1200x400
    alt: Campaign banner
```

---

# 4. link

## Purpose

Interactive external or internal navigation.

---

## Canonical Payload

```yaml
payload:
  label: Learn more
  url: https://example.com
```

---

## Payload Fields

| Field | Type | Purpose |
|---|---|---|
| label | string | display text |
| url | string | target destination |

---

## Example

```yaml
- type: link
  payload:
    label: Learn more
    url: https://example.com
```

---

# 5. redirect

## Purpose

Runtime redirect orchestration.

---

## Canonical Payload

```yaml
payload:
  url: https://merchant.example.com
  timeout: 5
  fallback_url: /x/claim
```

---

## Payload Fields

| Field | Type | Purpose |
|---|---|---|
| url | string | redirect destination |
| timeout | int | delay before redirect |
| fallback_url | string | fallback runtime route |

---

## Example

```yaml
- type: redirect
  payload:
    url: https://merchant.example.com
    timeout: 5
```

---

# Runtime Normalization Rules

---

# Raw YAML MAY Be Short Form

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

# Legacy Fields

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

# Frontend Runtime Guarantee

Frontend runtimes SHOULD assume:

```text
payload contains the canonical normalized structure
```

and SHOULD avoid depending on raw YAML shortcuts.

---

# Unknown Payload Fields

Unknown payload fields SHOULD:

- remain preserved
- pass through normalization
- avoid destructive stripping

This allows forward compatibility.

---

# Example

```yaml
payload:
  sponsor_id: abc123
  analytics:
    campaign: summer
```

SHOULD survive normalization unchanged.

---

# Meta vs Payload

---

# payload

Represents:

```text
stage business/runtime content
```

Examples:

- image source
- text body
- redirect target

---

# meta

Represents:

```text
runtime metadata
```

Examples:

- normalization source
- timestamps
- runtime annotations
- analytics hints

---

# Example

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

# Runtime Rendering Responsibility

x-rider defines:

```text
payload semantics
```

Frontend runtimes define:

```text
how payloads render visually
```

This separation is intentional.

---

# Runtime Fallback Rules

---

# Unknown Stage Types

Unknown types SHOULD:

- fail gracefully
- avoid crashing runtime
- optionally render generic payload views

---

# Missing Payloads

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

# Future Stage Types

Planned future stage types may include:

```yaml
- type: video
- type: qr
- type: countdown
- type: poll
- type: carousel
- type: sponsor
- type: action
```

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

# Guiding Principle

A useful heuristic:

| Concern | Owner |
|---|---|
| "What data does this stage contain?" | x-rider |
| "How should the UI display it?" | frontend runtime |

This principle should guide future Rider stage evolution.