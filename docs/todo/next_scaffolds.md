# x-rider / x-change Runtime Next Scaffold Checklist

---

# PRIORITY 1
## Finish Phase 7 Runtime Hardening
### TODO: runtime_accessibility_and_resilience.md

Goal:
Production-grade runtime survivability.

---

## 1. Accessibility Semantics
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] Add `role="dialog"` to modal/fullscreen stages
- [ ] Add `aria-modal="true"`
- [ ] Add `aria-live` support for redirect countdowns
- [ ] Add `aria-label` support for CTA/runtime buttons

---

## 2. Keyboard Runtime Support
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] Escape-to-dismiss support
- [ ] Enter/Space CTA activation
- [ ] keyboard-safe modal interaction
- [ ] tab navigation verification

---

## 3. Runtime Resilience Tests
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] malformed stage payload tests
- [ ] malformed action tests
- [ ] invalid timing hook tests
- [ ] missing payload field tests
- [ ] invalid stage type tests

---

## 4. Timer / Cleanup Safety
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] unmount cleanup tests
- [ ] countdown leak prevention tests
- [ ] delayed action cancellation tests
- [ ] duplicate timer prevention tests

---

## 5. Slow/Offline Runtime Safety
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] failed image fallback rendering
- [ ] redirect failure survivability
- [ ] missing asset fallback behavior
- [ ] runtime degradation verification

---

## 6. Runtime Isolation Boundary
### Phase 7
### File: runtime_accessibility_and_resilience.md

- [ ] investigate RuntimeErrorBoundary
- [ ] isolate stage rendering failures
- [ ] ensure runtime failure never breaks claim flow
- [ ] ensure runtime failure never breaks settlement flow

---

# PRIORITY 2
## Begin Phase 8 Entry Point
### TODO: structured_splash_authoring.md

Goal:
Introduce the first structured runtime driver system.

---

## 7. Structured Splash DTO Design
### Phase 8
### File: structured_splash_authoring.md

- [ ] define structured splash payload schema
- [ ] define allowed fields
- [ ] define driver metadata contract

Example:

```yaml
driver: dedication_splash

payload:
  title:
  message:
  image_url:
  accent:
```

---

## 8. SplashTemplateRegistry
### Phase 8
### File: structured_splash_authoring.md

- [ ] create SplashTemplateRegistry
- [ ] register template drivers
- [ ] resolve driver → runtime renderer

---

## 9. Safe Splash Components
### Phase 8
### File: structured_splash_authoring.md

- [ ] DedicationSplash.vue
- [ ] CampaignSplash.vue
- [ ] SponsorSplash.vue

---

## 10. Structured Driver Normalization
### Phase 8
### File: structured_splash_authoring.md

- [ ] normalize drivers into Rider stages
- [ ] internally generate trusted HTML
- [ ] mark generated HTML as trusted_html=true

---

## 11. Structured Driver Tests
### Phase 8
### File: structured_splash_authoring.md

- [ ] payload validation tests
- [ ] registry resolution tests
- [ ] renderer compatibility tests
- [ ] malformed payload tests

---

# PRIORITY 3
## Complete Remaining Phase 7 Formalization
### TODO: runtime_roadmap.md

Goal:
Officially finalize Runtime Actions architecture.

---

## 12. Runtime Action Registry
### Phase 7
### File: runtime_roadmap.md

- [ ] formal runtime action registry
- [ ] runtime action resolution contract
- [ ] typed action handler registration

---

## 13. Backend Runtime DTO Formalization
### Phase 7
### File: runtime_roadmap.md

- [ ] action DTO contracts
- [ ] serialization rules
- [ ] validation rules
- [ ] backend/frontend parity verification

---

## 14. Runtime Capability Guards
### Phase 7
### File: runtime_roadmap.md

- [ ] runtime permission guards
- [ ] action capability checks
- [ ] restricted runtime modes

---

# PRIORITY 4
## Long-Term Runtime Ecosystem
### TODO: future_runtime_ecosystem.md

Goal:
Expand runtime into programmable engagement infrastructure.

DO NOT start until:
- runtime hardening is complete
- structured drivers exist
- runtime registry is stable

---

## Future Areas (Deferred)

### Phase 10+
### File: future_runtime_ecosystem.md

- [ ] analytics event bus
- [ ] sponsor runtime
- [ ] campaign orchestration
- [ ] affiliate runtime
- [ ] loyalty runtime
- [ ] runtime telemetry
- [ ] portable renderer protocol
- [ ] capability negotiation
- [ ] mobile runtime protocol
- [ ] kiosk runtime protocol

---

# Recommended Immediate Next Slice

## Start Here

```text
runtime_accessibility_and_resilience.md
```

Specifically:

1. accessibility semantics
2. malformed payload resilience
3. timer cleanup tests

These are:
- low risk
- high leverage
- foundation-strengthening
- Phase 7 completing