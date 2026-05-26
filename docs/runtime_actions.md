# x-rider Runtime Actions

Runtime actions allow rider stages to perform safe frontend behavior.

They may affect page experience, but must never affect:

- claim correctness
- voucher redemption
- payout execution
- settlement
- ledger state

## Example: delayed redirect

```json
{
  "key": "success_redirect",
  "type": "message",
  "phase": "redirect",
  "enabled": true,
  "content": "Redirecting you now...",
  "actions": [
    {
      "key": "wait",
      "type": "delay",
      "timing": "on_mount",
      "payload": {
        "delay_ms": 3000
      }
    },
    {
      "key": "redirect",
      "type": "redirect",
      "timing": "on_complete",
      "payload": {
        "url": "/x/claim/ABC123/redirect"
      }
    }
  ]
}