# Phase 7 — x-rider Runtime Actions

## Goal

Turn rider stages from passive renderable content into controlled runtime experiences.

Runtime actions may affect the page experience, but must never affect:

- claim correctness
- voucher redemption
- payout execution
- settlement
- ledger state

---

# Phase 7.1 — Define Runtime Action Contract

- [x] Add `RiderRuntimeAction` type in `x-rider` frontend types
- [x] Define supported action names:
  - [x] `redirect`
  - [x] `open_url`
  - [x] `copy_to_clipboard`
  - [x] `track_event`
  - [x] `delay`
  - [x] `show_stage`
  - [x] `close`
- [x] Add action payload typing
- [x] Add action timing metadata:
  - [x] `on_mount`
  - [x] `on_click`
  - [x] `after_delay`
  - [x] `on_complete`
- [x] Add safety flags:
  - [x] `requires_user_gesture`
  - [x] `external`
  - [x] `enabled`

---

# Phase 7.2 — Extend Stage Payload Shape

- [x] Allow stages to declare `actions`
- [x] Keep backward compatibility with existing stage payloads
- [x] Ensure old `redirect` stages still work
- [x] Ensure visual-only stages remain valid
- [x] Add examples for:
  - [x] splash with delayed redirect
  - [x] message with CTA open URL
  - [ ] success stage with copy action
  - [x] redirect stage with countdown

---

# Phase 7.3 — Build Runtime Action Executor

- [x] Create `useRiderRuntimeActions.ts`
- [x] Implement safe executor registry
- [x] Implement `redirect`
- [x] Implement `open_url`
- [x] Implement `copy_to_clipboard`
- [x] Implement `track_event` as no-op/log seam first
- [x] Implement `delay`
- [x] Implement `show_stage`
- [x] Implement `close`
- [x] Add guard for disabled actions
- [x] Add guard for unsafe external URLs
- [x] Add guard for user-gesture-only actions

---

# Phase 7.4 — Upgrade RiderRuntimeSequencer

- [x] Accept stages with runtime actions
- [x] Execute `on_mount` actions safely
- [x] Execute `after_delay` actions safely
- [x] Expose `on_complete` behavior
- [x] Preserve current redirect countdown behavior
- [x] Prevent duplicate execution on re-render
- [x] Track executed action keys locally
- [x] Add fallback behavior when action fails
- [x] Sequence blocking modal/fullscreen stages
- [x] Restore modal/fullscreen runtime presentations

---

# Phase 7.5 — Add Action-Aware Presenter Support

- [x] Update `RiderStagePresenter`
- [x] Render CTA buttons when stage has `on_click` actions
- [x] Support copy buttons
- [x] Support external link buttons
- [x] Support close/dismiss buttons
- [x] Keep visual rendering separate from action execution
- [x] Do not execute actions from plain visual render
- [x] Support runtime redirect countdown rendering

---

# Phase 7.6 — Normalize Backend DTO / PHP Data Shape

- [x] Add runtime action DTO in `x-rider`
- [x] Add action collection to stage DTO
- [x] Preserve old stage serialization
- [ ] Add validation rules for action payloads
- [x] Add safe defaults
- [x] Ensure x-change only transports action payloads
- [x] Ensure x-change does not interpret runtime action semantics
- [x] Normalize runtime action collections through stage collections

---

# Phase 7.7 — Update x-change Integration

- [x] Confirm `ClaimWidget.vue` still only renders:
  - [x] `pre_claim`
  - [x] `runtime`
- [x] Confirm `ClaimWidget.vue` still does not execute:
  - [x] `redirect`
- [x] Confirm `Success.vue` still only renders:
  - [x] `success`
  - [x] `post_claim`
  - [x] `redirect`
- [x] Confirm `Success.vue` does not render:
  - [x] `pre_claim`
- [x] Wire action-aware sequencer into success redirect runtime
- [x] Keep legacy `RiderCountdown` fallback
- [x] Restore modal/fullscreen runtime sequencing

---

# Phase 7.8 — Tests

## x-rider tests

- [x] Runtime action type tests
- [x] Stage serialization tests
- [x] Action executor tests
- [x] Sequencer execution-order tests
- [x] Duplicate-execution prevention tests
- [x] URL safety tests
- [x] Backward compatibility tests
- [x] Runtime action normalization tests
- [x] Runtime action propagation tests

---

## x-change tests

- [x] Claim preview does not execute redirect actions
- [x] Claim preview renders pre-claim/runtime stages
- [x] Success page renders success/post-claim stages
- [x] Success page does not render pre-claim stages
- [x] Success page executes redirect runtime stages
- [x] Legacy rider redirect still works
- [x] Lifecycle isolation tests
- [x] Lifecycle phase policy extraction

---

# Phase 7.9 — Documentation

- [x] Add `x-rider/docs/runtime_actions.md`
- [x] Add action payload examples
- [x] Add safety rules
- [x] Add lifecycle placement rules
- [x] Update `stage_payload_contracts.md`
- [x] Update `preview_runtime.md`
- [ ] Update package boundary docs if needed
- [x] Document lifecycle isolation guarantees
- [x] Document runtime responsibility boundaries
- [x] Document runtime action normalization behavior

---

# Phase 7.10 — Commit Structure

## Commit 1

- [x] Runtime action types and DTOs

Suggested message:

```bash
git commit -m "Add rider runtime action contract"
```

---

## Commit 2

- [x] Frontend runtime executor and sequencer upgrade

Suggested message:

```bash
git commit -m "Implement rider runtime action executor"
```

---

## Commit 3

- [x] Presenter CTA support

Suggested message:

```bash
git commit -m "Add action-aware rider stage presentation"
```

---

## Commit 4

- [x] x-change lifecycle integration tests

Suggested message:

```bash
git commit -m "Verify rider runtime actions across claim lifecycle"
```

---

## Commit 5

- [x] Documentation

Suggested message:

```bash
git commit -m "Document rider runtime actions"
```

---

# Phase 7 Done Criteria

- [x] Runtime actions are typed
- [x] Runtime actions are safely executable
- [x] Redirect behavior is action-driven where possible
- [x] Legacy redirect behavior still works
- [x] Claim preview cannot accidentally execute redirect actions
- [x] Success page owns post-claim and redirect runtime behavior
- [x] x-change transports rider runtime payloads without owning semantics
- [x] x-rider owns action semantics
- [x] Tests prove lifecycle isolation
- [x] Docs explain how to add new runtime actions

---

# Architectural Outcome

Phase 7 established:

```text
deterministic rider runtime lifecycle orchestration
```

including:

- lifecycle isolation
- runtime action semantics
- blocking presentation sequencing
- runtime ownership boundaries
- redirect runtime orchestration
- frontend/backend runtime separation

This forms the foundation for:

- Phase 8 — Driver composition runtime
- Phase 9 — x-ray extraction runtime
- Phase 10 — analytics + monetization runtime
- Phase 11 — multi-client runtime protocol