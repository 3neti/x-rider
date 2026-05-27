# Runtime Frontend Tests TODO

## Goal

Add frontend tests for `useRiderRuntimeActions.ts` and `RiderRuntimeSequencer.vue`.

## Executor tests

- [ ] disabled actions do not execute
- [ ] unsafe URLs do not execute
- [ ] redirect changes `window.location.href`
- [ ] open_url calls `window.open`
- [ ] copy_to_clipboard calls clipboard API
- [ ] delay waits before resolving
- [ ] show_stage calls callback
- [ ] close calls callback
- [ ] onError receives thrown errors

## Sequencer tests

- [ ] executes `on_mount`
- [ ] executes `after_delay`
- [ ] executes `on_complete`
- [ ] preserves action order
- [ ] prevents duplicate execution
- [ ] renders inline stages immediately
- [ ] sequences modal/fullscreen one at a time
- [ ] advances blocking stage after dismissal
- [ ] preserves legacy redirect timeout

## Presenter tests

- [ ] renders inline content
- [ ] renders modal presentation
- [ ] renders fullscreen presentation
- [ ] emits dismissed
- [ ] runs on_click actions for CTA
- [ ] displays redirect countdown