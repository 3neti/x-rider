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

## Phase 7.1 — Define Runtime Action Contract

- [ ] Add `RiderRuntimeAction` type in `x-rider` frontend types
- [ ] Define supported action names:
    - [ ] `redirect`
    - [ ] `open_url`
    - [ ] `copy_to_clipboard`
    - [ ] `track_event`
    - [ ] `delay`
    - [ ] `show_stage`
    - [ ] `close`
- [ ] Add action payload typing
- [ ] Add action timing metadata:
    - [ ] `on_mount`
    - [ ] `on_click`
    - [ ] `after_delay`
    - [ ] `on_complete`
- [ ] Add safety flags:
    - [ ] `requires_user_gesture`
    - [ ] `external`
    - [ ] `enabled`

---

## Phase 7.2 — Extend Stage Payload Shape

- [ ] Allow stages to declare `actions`
- [ ] Keep backward compatibility with existing stage payloads
- [ ] Ensure old `redirect` stages still work
- [ ] Ensure visual-only stages remain valid
- [ ] Add examples for:
    - [ ] splash with delayed redirect
    - [ ] message with CTA open URL
    - [ ] success stage with copy action
    - [ ] redirect stage with countdown

---

## Phase 7.3 — Build Runtime Action Executor

- [ ] Create `useRiderRuntimeActions.ts`
- [ ] Implement safe executor registry
- [ ] Implement `redirect`
- [ ] Implement `open_url`
- [ ] Implement `copy_to_clipboard`
- [ ] Implement `track_event` as no-op/log seam first
- [ ] Implement `delay`
- [ ] Implement `show_stage`
- [ ] Implement `close`
- [ ] Add guard for disabled actions
- [ ] Add guard for unsafe external URLs
- [ ] Add guard for user-gesture-only actions

---

## Phase 7.4 — Upgrade RiderRuntimeSequencer

- [ ] Accept stages with runtime actions
- [ ] Execute `on_mount` actions safely
- [ ] Execute `after_delay` actions safely
- [ ] Expose `on_complete` behavior
- [ ] Preserve current redirect countdown behavior
- [ ] Prevent duplicate execution on re-render
- [ ] Track executed action keys locally
- [ ] Add fallback behavior when action fails

---

## Phase 7.5 — Add Action-Aware Presenter Support

- [ ] Update `RiderStagePresenter`
- [ ] Render CTA buttons when stage has `on_click` actions
- [ ] Support copy buttons
- [ ] Support external link buttons
- [ ] Support close/dismiss buttons
- [ ] Keep visual rendering separate from action execution
- [ ] Do not execute actions from plain visual render

---

## Phase 7.6 — Normalize Backend DTO / PHP Data Shape

- [ ] Add runtime action DTO in `x-rider`
- [ ] Add action collection to stage DTO
- [ ] Preserve old stage serialization
- [ ] Add validation rules for action payloads
- [ ] Add safe defaults
- [ ] Ensure x-change only transports action payloads
- [ ] Ensure x-change does not interpret runtime action semantics

---

## Phase 7.7 — Update x-change Integration

- [ ] Confirm `ClaimWidget.vue` still only renders:
    - [ ] `pre_claim`
    - [ ] `runtime`
- [ ] Confirm `ClaimWidget.vue` still does not execute:
    - [ ] `redirect`
- [ ] Confirm `Success.vue` still only renders:
    - [ ] `success`
    - [ ] `post_claim`
    - [ ] `redirect`
- [ ] Confirm `Success.vue` does not render:
    - [ ] `pre_claim`
- [ ] Wire action-aware sequencer into success redirect runtime
- [ ] Keep legacy `RiderCountdown` fallback

---

## Phase 7.8 — Tests

### x-rider tests

- [ ] Runtime action type tests
- [ ] Stage serialization tests
- [ ] Action executor tests
- [ ] Sequencer execution-order tests
- [ ] Duplicate-execution prevention tests
- [ ] URL safety tests
- [ ] Backward compatibility tests

### x-change tests

- [ ] Claim preview does not execute redirect actions
- [ ] Claim preview renders pre-claim/runtime stages
- [ ] Success page renders success/post-claim stages
- [ ] Success page executes redirect runtime stages
- [ ] Success page does not render pre-claim stages
- [ ] Legacy rider redirect still works

---

## Phase 7.9 — Documentation

- [ ] Add `x-rider/docs/runtime_actions.md`
- [ ] Add action payload examples
- [ ] Add safety rules
- [ ] Add lifecycle placement rules
- [ ] Update `stage_payload_contracts.md`
- [ ] Update `preview_runtime.md`
- [ ] Update package boundary docs if needed

---

## Phase 7.10 — Commit Structure

### Commit 1

- [ ] Runtime action types and DTOs

Suggested message:

```bash
git commit -m "Add rider runtime action contract"
```

### Commit 2

- [ ] Frontend runtime executor and sequencer upgrade

Suggested message:

```bash
git commit -m "Implement rider runtime action executor"
```

### Commit 3

- [ ] Presenter CTA support

Suggested message:

```bash
git commit -m "Add action-aware rider stage presentation"
```

### Commit 4

- [ ] x-change lifecycle integration tests

Suggested message:

```bash
git commit -m "Verify rider runtime actions across claim lifecycle"
```

### Commit 5

- [ ] Documentation

Suggested message:

```bash
git commit -m "Document rider runtime actions"
```

---

## Phase 7 Done Criteria

- [ ] Runtime actions are typed
- [ ] Runtime actions are safely executable
- [ ] Redirect behavior is action-driven where possible
- [ ] Legacy redirect behavior still works
- [ ] Claim preview cannot accidentally execute redirect actions
- [ ] Success page owns post-claim and redirect runtime behavior
- [ ] x-change transports rider runtime payloads without owning semantics
- [ ] x-rider owns action semantics
- [ ] Tests prove lifecycle isolation
- [ ] Docs explain how to add new runtime actions