# Presentation Modes
## Runtime Presentation Semantics for x-rider

---

# Purpose

This document defines the canonical runtime semantics for Rider presentation modes.

Presentation modes determine:

```text
HOW a Rider stage should be surfaced
```

within a frontend runtime.

x-rider defines the semantic intent of each presentation mode.

Frontend runtimes remain responsible for:

- exact rendering implementation
- animations
- transitions
- styling
- responsiveness
- accessibility
- platform adaptation

This separation allows:

- web
- mobile
- kiosk
- terminal
- embedded runtimes

to interpret the same Rider experience consistently.

---

# Architectural Principle

x-rider owns:

```text
presentation semantics
```

but NOT:

```text
specific frontend rendering implementations
```

Example:

```yaml
presentation: fullscreen
```

means:

```text
This stage intends to occupy primary visual focus.
```

It does NOT mandate:

- exact CSS
- modal framework
- animation engine
- viewport implementation

Those belong to the consuming frontend runtime.

---

# Supported Presentation Modes

---

# 1. inline

```yaml
presentation: inline
```

---

## Meaning

The stage is rendered within the existing page flow.

The stage participates in the current document layout.

It does not block or replace the current screen.

---

## Typical Usage

- notices
- warnings
- disclosures
- embedded sponsor messages
- lightweight onboarding
- preview content
- informational banners
- inline media

---

## Expected Runtime Characteristics

- non-blocking
- layout-aware
- embedded into surrounding UI
- visible alongside existing content

---

## Example

```yaml
- type: splash
  presentation: inline
  content: |
    Please review this before continuing.
```

---

## Common Rendering Forms

Examples:

- card
- banner
- hero panel
- embedded media block
- alert section

---

## Typical Placement

Examples:

- above claim form
- above preview tabs
- inside workflow summary
- embedded within checkout flow

---

# 2. modal

```yaml
presentation: modal
```

---

## Meaning

The stage should temporarily interrupt the current flow using an overlay surface.

The user is expected to acknowledge or dismiss the stage before continuing.

---

## Typical Usage

- confirmations
- disclosures
- warnings
- consent screens
- sponsor interruptions
- onboarding prompts
- OTP notices
- legal acknowledgements

---

## Expected Runtime Characteristics

- overlay-based
- visually blocking
- focus-grabbing
- interruptive
- dismissible or actionable

---

## Example

```yaml
- type: splash
  presentation: modal
  content: |
    Please acknowledge the following disclosure.
```

---

## Common Rendering Forms

Examples:

- dialog
- popup
- sheet
- overlay card
- centered modal

---

## Runtime Recommendations

Frontends SHOULD:

- trap focus when appropriate
- support escape/dismiss behavior
- preserve accessibility semantics
- avoid background interaction

---

# 3. fullscreen

```yaml
presentation: fullscreen
```

---

## Meaning

The stage intends to occupy the primary visual surface.

The runtime should prioritize the Rider experience over surrounding interface chrome.

---

## Typical Usage

- campaigns
- branded experiences
- onboarding journeys
- immersive sponsor content
- kiosk experiences
- transaction interstitials
- celebration screens
- instructional walkthroughs

---

## Expected Runtime Characteristics

- immersive
- visually dominant
- viewport-prioritized
- flow-replacing
- high-attention

---

## Example

```yaml
- type: splash
  presentation: fullscreen
  content: |
    Welcome to the rewards experience.
```

---

## Common Rendering Forms

Examples:

- full-page takeover
- viewport hero
- kiosk screen
- onboarding scene
- sponsor interstitial
- branded full-screen card

---

## Runtime Recommendations

Frontends MAY:

- suppress surrounding chrome
- hide navigation
- dim background context
- enter presentation mode
- autoplay media when allowed

---

# Runtime Fallback Rules

---

# Missing Presentation

If omitted:

```yaml
presentation:
```

the runtime SHOULD assume:

```yaml
presentation: inline
```

unless overridden by frontend policy.

---

# Unknown Presentation Modes

Unknown modes SHOULD:

- fail gracefully
- downgrade safely to inline
- avoid runtime crashes

Example:

```yaml
presentation: hologram
```

may safely degrade to:

```yaml
presentation: inline
```

---

# Relationship to Stage Types

Presentation modes are orthogonal to stage types.

Example:

```yaml
- type: image
  presentation: modal
```

and:

```yaml
- type: image
  presentation: fullscreen
```

are both valid.

The presentation mode controls runtime placement semantics.

The stage type controls content semantics.

---

# Runtime Ownership Model

x-rider owns:

```text
What the presentation mode means
```

Frontend runtimes own:

```text
How the presentation mode is implemented
```

This separation allows multiple frontends to interpret the same Rider runtime differently while preserving semantic consistency.

---

# Current Runtime Usage

At the current phase of implementation:

| Mode | Current Behavior |
|---|---|
| inline | fully supported |
| modal | planned |
| fullscreen | partially simulated |

---

# Planned Runtime Evolution

Future frontend runtimes may support:

- animated transitions
- presentation queues
- stage sequencing
- fullscreen experiences
- sponsor interstitials
- kiosk rendering
- mobile-native rendering
- TV/terminal rendering

without changing Rider stage payloads.

---

# Future Reserved Presentation Modes

The following presentation modes are reserved for future expansion:

```yaml
presentation: overlay
presentation: kiosk
presentation: terminal
presentation: interstitial
presentation: toast
presentation: notification
```

These are not yet standardized.

---

# Why Presentation Modes Matter

Presentation modes establish a critical architectural separation:

```text
Backend defines experience semantics
Frontend defines rendering implementation
```

This allows:

- portable runtime experiences
- multi-platform rendering
- consistent orchestration
- future x-ray extraction
- reusable Rider runtimes

without tightly coupling presentation logic to a single frontend.

---

# Guiding Principle

A useful mental model:

| Concern | Owner |
|---|---|
| "What should this experience feel like?" | x-rider |
| "How exactly should it render?" | frontend runtime |

This principle should guide all future Rider presentation runtime work.