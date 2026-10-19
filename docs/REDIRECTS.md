# Strict destination validation

`LBHurtado\XRider\Services\RedirectDestinationPolicy::validate($url)` returns an unchanged permitted HTTP(S) destination or throws Laravel ValidationException for `rider.url`. Configure `x-rider.redirects.allowed_hosts` or explicitly enable `allow_any_host`. Schemes also honor the configured allowlist. Credentials, control characters, invalid URLs, URLs longer than 4096 bytes, and the reserved `x_rider_handoff` query parameter are rejected.

Use strict validation before accepting a paid redirect instruction. It does not fetch the destination or prove availability. Freeze the accepted destination in the calling domain. The legacy fallback resolver retains its existing behavior for legacy consumers.
