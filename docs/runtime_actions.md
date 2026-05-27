# x-rider Runtime Actions

Runtime actions allow rider stages to perform safe frontend behavior.

They may affect page experience, but must never affect:

- claim correctness
- voucher redemption
- payout execution
- settlement
- ledger state

---

# Ownership Rule

`x-rider` owns runtime action semantics.

`x-change` may transport runtime payloads, but must not interpret:

- runtime sequencing
- redirect semantics
- CTA behavior
- modal orchestration
- fullscreen presentation
- countdown execution

This preserves lifecycle isolation.

---

# Runtime Lifecycle

The current runtime boundary is:

```text
pre_claim → form-flow → success → redirect
```

## ClaimWidget

Responsible for:

- pre_claim stages
- runtime stages before form-flow

Must not execute:

- redirect stages

---

## Success.vue

Responsible for:

- success stages
- post_claim stages
- redirect stages

Must not render:

- pre_claim stages

---

# Stage Shape

```json
{
  "type": "cta",
  "key": "demo-cta",
  "phase": "pre_claim",
  "presentation": "inline",
  "payload": {
    "label": "Open Reward",
    "url": "https://example.com/reward"
  },
  "actions": [
    {
      "key": "demo-cta-open",
      "type": "open_url",
      "timing": "on_click",
      "requires_user_gesture": true,
      "payload": {
        "url": "https://example.com/reward",
        "target": "_blank"
      }
    }
  ]
}
```

---

# Supported Action Types

| Type | Purpose |
|---|---|
| `redirect` | Navigate current browser window |
| `open_url` | Open external/internal URL |
| `copy_to_clipboard` | Copy text to clipboard |
| `track_event` | Emit analytics/runtime event |
| `delay` | Pause runtime sequencing |
| `show_stage` | Reveal hidden stage |
| `close` | Close active presentation |

---

# Supported Timings

| Timing | Meaning |
|---|---|
| `on_mount` | Execute when stage enters runtime |
| `after_delay` | Execute after delay-oriented sequencing |
| `on_complete` | Execute after stage completion |
| `on_click` | Execute from user interaction |

---

# Runtime Executor

Frontend runtime execution is handled by:

```text
useRiderRuntimeActions.ts
```

Current guarantees:

- unsafe URLs are blocked
- disabled actions do not execute
- malformed timings normalize safely
- malformed action types normalize safely
- user gesture requirements are enforced
- runtime errors are isolated from redemption flow

---

# Presentation Modes

Stages may render as:

| Presentation | Behavior |
|---|---|
| `inline` | Render inside page flow |
| `modal` | Dismissible overlay |
| `fullscreen` | Immersive blocking presentation |

Blocking presentations are sequenced one at a time.

Example:

```text
modal → dismiss → fullscreen → dismiss
```

Inline stages render independently.

---

# Redirect Runtime

Legacy redirect stages are normalized into runtime actions.

Example:

```json
{
  "type": "redirect",
  "key": "demo-redirect",
  "phase": "redirect",
  "payload": {
    "url": "https://example.com/success",
    "timeout": 8,
    "external": true
  }
}
```

Runtime converts this into:

```text
delay 8000ms
redirect
```

Frontend presentation displays:

```text
Redirecting you now...
Redirecting in N seconds.
```

---

# CTA Runtime

Preferred CTA structure:

```yaml
- type: cta
  key: demo-cta
  phase: pre_claim
  presentation: inline
  payload:
    label: Open Reward
    url: https://example.com/reward
  actions:
    - key: demo-cta-open
      type: open_url
      timing: on_click
      requires_user_gesture: true
      payload:
        url: https://example.com/reward
        target: _blank
```

This preserves UX while routing behavior through the runtime executor.

---

# Backend DTO Boundary

Runtime actions are normalized by:

```php
LBHurtado\XRider\Data\RiderRuntimeActionData
```

Stages carry actions through:

```php
LBHurtado\XRider\Data\RiderStageData::$actions
```

Collections preserve actions through:

```php
LBHurtado\XRider\Data\RiderStageCollectionData
```

---

# Runtime Normalization Rules

Malformed runtime actions normalize safely.

## Invalid type

```json
{
  "type": "dangerous_action"
}
```

Normalizes to:

```text
track_event
```

---

## Invalid timing

```json
{
  "timing": "whenever"
}
```

Normalizes to:

```text
on_click
```

---

## Negative delays

```json
{
  "delay_ms": -500
}
```

Normalizes to:

```text
0
```

---

# Current Guarantees

- Runtime actions are typed on the frontend.
- Runtime actions are normalized on the backend.
- Stage collections preserve runtime actions.
- Redirect stages preserve legacy redirect behavior.
- Blocking presentations are sequenced safely.
- Countdown redirects are runtime-driven.
- CTA interactions execute through runtime actions.
- Runtime failures do not affect redemption correctness.
- x-change remains runtime-agnostic.

---

# Current Limitations

Not yet implemented:

- runtime persistence
- runtime analytics transport
- runtime state recovery
- cross-device runtime continuity
- server-authoritative runtime orchestration
- runtime replay
- signed runtime action envelopes

---

# Rule of Thumb

Use stages for what the user sees.

Use actions for what the runtime does.