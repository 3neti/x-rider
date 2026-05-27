# TODO — Structured Splash Authoring System

## Goal

Replace arbitrary depositor-authored Rider splash HTML with structured, template-driven splash experiences.

This reduces security risk while improving:

- UX consistency
- mobile rendering
- accessibility
- localization
- campaign portability
- runtime predictability

---

# Problem

Current Rider splash authoring still relies on free-form HTML:

```yaml
rider:
  splash: "<div>...</div>"
```

Even though HTML is now sanitized, arbitrary HTML still introduces:

- layout unpredictability
- rendering inconsistencies
- styling abuse
- difficult mobile optimization
- poor composability
- template duplication

---

# Desired Direction

Prefer structured splash drivers:

```yaml
driver: dedication_splash
```

instead of raw HTML.

Example:

```yaml
driver: dedication_splash

payload:
  title: "Für Anaïs"
  message: "Dans une autre vie..."
  image_url: "https://..."
  accent: "romantic"
```

The runtime owns rendering.

Users provide data, not markup.

---

# Proposed Architecture

## 1. Structured Splash Drivers

Introduce predefined drivers:

- dedication_splash
- marketing_splash
- campaign_splash
- announcement_splash
- sponsor_splash
- receipt_splash

Each driver defines:

- allowed payload schema
- presentation behavior
- runtime rendering contract

---

## 2. Runtime Renderer Registry

Introduce renderer resolution:

```text
driver
    ↓
runtime renderer
    ↓
safe generated stage
```

Possible future component:

```text
SplashTemplateRegistry
```

---

## 3. Driver Payload Validation

Each driver should validate structured fields:

```yaml
title:
message:
image_url:
cta:
accent:
theme:
```

instead of allowing arbitrary HTML.

---

## 4. Safe Rendering Components

Examples:

```text
DedicationSplash.vue
CampaignSplash.vue
SponsorSplash.vue
```

These components render trusted layouts using controlled props.

---

## 5. Runtime Compatibility

Structured drivers should still normalize into standard Rider stages:

```yaml
type: splash
presentation: fullscreen
content_type: html
meta:
  trusted_html: true
```

But generated internally by the runtime.

Not by depositors.

---

# Future Benefits

Structured splash drivers enable:

- reusable campaigns
- bank-approved branding
- sponsor inventory
- analytics
- mobile-safe layouts
- localization
- theme systems
- safer kiosk rendering
- embeddable runtime surfaces

---

# Long-Term Principle

```text
Prefer structured runtime templates over arbitrary depositor-authored HTML.
```