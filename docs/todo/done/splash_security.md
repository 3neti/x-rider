# Strategy & Implementation Plan: Secure Rider Splash HTML

## Goal

Allow depositors/issuers to create rich Rider splash experiences without allowing unsafe HTML to execute inside the redeemer’s browser.

The platform should support expressive content such as:

- greetings
- images
- styled text
- links
- simple layout

But must block:

- JavaScript execution
- credential phishing helpers
- hidden forms
- event handlers
- dangerous URLs
- hostile embeds
- session-abusing payloads

---

# Core Security Principle

Depositor-authored HTML must never be treated as trusted HTML.

Authenticated sessions do not make HTML safe.

If malicious HTML runs while a user is authenticated, it may:

- read visible session data
- trigger authenticated requests
- redirect or mislead users
- simulate UI elements
- phish credentials
- interfere with claim/redemption flow

Therefore:

```text
raw depositor HTML → sanitize → store/render sanitized HTML
```

Never:

```text
raw depositor HTML → v-html
```

---

# Content Trust Tiers

## Tier 1 — Plain Text / Markdown

Default for ordinary users.

```yaml
content_type: markdown
```

Allowed for:

- depositors
- senders
- normal issuers
- public voucher generators

This should be the safest default.

---

## Tier 2 — Sanitized HTML

Allowed for rich Rider splash content from depositors.

```yaml
content_type: html
meta:
  sanitized: true
  html_profile: rider_splash
```

This is the preferred path for depositor-authored “beautiful splash” content.

---

## Tier 3 — Trusted Template HTML

Reserved for platform-owned or admin-curated templates.

```yaml
content_type: html
meta:
  trusted_html: true
```

Allowed only for:

- internal templates
- reviewed partner templates
- controlled campaign templates
- bank-approved templates

This should not be available to ordinary depositors.

---

# Sanitization Strategy

## Allowed Tags

The `rider_splash` profile should allow only presentation-safe tags:

```text
div
p
span
strong
b
em
i
br
hr
h1
h2
h3
ul
ol
li
img
a
```

Optional later:

```text
blockquote
small
code
pre
```

Avoid initially:

```text
form
input
button
iframe
script
style
object
embed
svg
canvas
video
audio
```

---

## Allowed Attributes

Safe attributes:

```text
class
href
src
alt
title
target
rel
```

Style should be restricted or avoided.

If style is allowed, sanitize it heavily.

Safe CSS properties may include:

```text
text-align
font-weight
font-style
color
background-color
margin
margin-top
margin-bottom
padding
border-radius
max-width
width
height
```

Block CSS capable of layout deception or overlay abuse:

```text
position
z-index
top
left
right
bottom
transform
display: none
visibility
opacity
pointer-events
```

---

## URL Rules

Allowed URL schemes:

```text
https
http
mailto
tel
```

Blocked schemes:

```text
javascript
data
vbscript
file
blob
```

Recommended rule:

```text
images must use https URLs
links must use http/https/mailto/tel only
```

For `target="_blank"`, always add:

```html
rel="noopener noreferrer"
```

---

# Backend Implementation Plan

## 1. Add HTML Sanitizer Dependency

In the host app or package boundary where voucher instructions are accepted:

```bash
composer require mews/purifier
```

Alternative later:

```text
HTMLPurifier directly
```

---

## 2. Add Sanitizer Configuration

Create a dedicated profile:

```text
rider_splash
```

Example config concept:

```php
'HTML.Allowed' => 'div,p,span,strong,b,em,i,br,hr,h1,h2,h3,ul,ol,li,img[src|alt|title|class],a[href|title|target|rel|class]',
'URI.AllowedSchemes' => [
    'http' => true,
    'https' => true,
    'mailto' => true,
    'tel' => true,
],
'Attr.EnableID' => false,
```

If supporting classes, rely on frontend Tailwind-safe classes only when possible.

---

## 3. Introduce RiderHtmlSanitizer

Create a service:

```php
final class RiderHtmlSanitizer
{
    public function sanitizeSplash(string $html): string
    {
        return clean($html, 'rider_splash');
    }
}
```

Responsibilities:

- sanitize depositor HTML
- normalize unsafe URLs
- strip event handlers
- strip scripts/forms/iframes
- optionally log removed dangerous content

---

## 4. Sanitize at Input Boundary

The safest point is when voucher instructions are created or updated.

Example flow:

```text
Depositor submits rider.splash
        ↓
Validate request
        ↓
Sanitize HTML if content_type/html detected
        ↓
Store sanitized content
        ↓
Mark metadata as sanitized
        ↓
Render sanitized content only
```

Do not wait until render-time if the content is persisted.

---

## 5. Mark Sanitized Metadata

After sanitization, store metadata:

```yaml
content_type: html
content: "<div>sanitized html...</div>"
meta:
  sanitized: true
  html_profile: rider_splash
  sanitized_at: "2026-05-27T..."
```

Optional:

```yaml
meta:
  original_content_hash: "sha256..."
```

Do not expose raw original HTML publicly.

---

# Frontend Runtime Guard

## RiderRenderer.vue

Current behavior renders any `content_type: html` using `v-html`.

Harden it.

Recommended behavior:

```vue
<div
    v-if="type === 'html' && content?.meta?.sanitized === true"
    v-html="content?.content"
/>

<p v-else class="whitespace-pre-line">
    {{ content?.content }}
</p>
```

This means HTML renders only when backend explicitly says:

```yaml
meta:
  sanitized: true
```

Otherwise, HTML is escaped as text.

---

# Compatibility Rule

For now:

```text
content_type: html without meta.sanitized=true must not render as HTML
```

This may temporarily break unsanitized demo HTML until the demo payload is marked sanitized.

That is acceptable because security should fail closed.

---

# x-rider DTO / Runtime Contract

Update RiderContent / RawRiderStage metadata expectations:

```ts
meta?: {
  sanitized?: boolean;
  html_profile?: string;
  trusted_html?: boolean;
}
```

For stage payload content:

```yaml
payload:
  content_type: html
  content: ...
  meta:
    sanitized: true
```

or top-level:

```yaml
content_type: html
content: ...
meta:
  sanitized: true
```

---

# Tests

## Backend Tests

Add tests proving sanitizer removes:

- `<script>`
- `onclick`
- `onerror`
- `javascript:` URLs
- `data:` URLs
- `<iframe>`
- `<form>`
- hidden inputs
- unsafe CSS

Example:

```php
it('sanitizes depositor rider splash html', function () {
    $html = '<img src=x onerror=alert(1)><script>alert(1)</script>';

    $clean = app(RiderHtmlSanitizer::class)->sanitizeSplash($html);

    expect($clean)
        ->not->toContain('<script>')
        ->not->toContain('onerror');
});
```

---

## Frontend Tests

Add tests proving:

- sanitized HTML renders with `v-html`
- unsanitized HTML renders as escaped text
- payload metadata is respected
- top-level metadata is respected
- markdown still renders normally

Example:

```ts
it('does not render html unless sanitized', () => {
    const wrapper = mount(RiderRenderer, {
        props: {
            content: {
                enabled: true,
                type: 'html',
                content: '<strong>Hello</strong>',
            },
        },
    });

    expect(wrapper.html()).not.toContain('<strong>Hello</strong>');
    expect(wrapper.text()).toContain('<strong>Hello</strong>');
});
```

---

# Operational Policy

## Ordinary Depositors

Can submit:

- markdown
- sanitized rich HTML

Cannot submit:

- raw executable HTML
- scripts
- forms
- iframes
- arbitrary embedded content

---

## Admins / Platform Operators

Can create:

- trusted templates
- reviewed campaign layouts
- bank-approved splash screens

But even admin templates should ideally pass through sanitizer unless there is a strong reason not to.

---

## Future Template System

Phase 8 can introduce:

```yaml
driver: dedication_splash
```

with safe fields:

```yaml
title: Für Anaïs
image_url: https://cataas.com/cat?width=600&height=400
message: Dans une autre vie...
accent: romantic
```

This is safer than arbitrary HTML because the runtime controls rendering.

Long-term recommendation:

```text
Prefer structured splash templates over free-form HTML.
```

---

# Recommended Implementation Slices

## Slice 1 — Frontend Fail-Closed HTML Guard

- Update `RiderRenderer.vue`
- Render HTML only if `meta.sanitized === true` or `meta.trusted_html === true`
- Add frontend tests

Commit:

```bash
git commit -m "Require sanitized metadata for rider HTML rendering"
```

---

## Slice 2 — Backend Sanitizer Service

- Add sanitizer dependency
- Add `RiderHtmlSanitizer`
- Add sanitizer config/profile
- Add backend tests

Commit:

```bash
git commit -m "Add rider splash HTML sanitizer"
```

---

## Slice 3 — Input Boundary Sanitization

- Sanitize depositor rider splash at voucher creation/update
- Mark sanitized metadata
- Preserve safe HTML only

Commit:

```bash
git commit -m "Sanitize depositor rider splash content"
```

---

## Slice 4 — Runtime Contract Docs

- Update `runtime_actions.md`
- Update `stage_payload_contracts.md`
- Update `preview_runtime.md`
- Add explicit security policy

Commit:

```bash
git commit -m "Document rider HTML sanitization policy"
```

---

## Slice 5 — Structured Splash Template Driver

Later, probably Phase 8:

- Add `dedication_splash` driver
- Accept structured inputs
- Generate safe splash stage
- Avoid free-form HTML where possible

Commit:

```bash
git commit -m "Add dedication splash rider driver"
```

---

# Final Rule

The platform may support beautiful depositor-authored Rider splash content.

But it must treat all depositor-authored HTML as hostile until sanitized.

```text
Beautiful is allowed.
Executable is not.
```