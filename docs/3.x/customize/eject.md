---
title: Eject
description: Ejecting the reading UI is planned, not shipped. What it will do, and the tools that cover most of the same needs today.
sources:
  - vendor/larecipe/larecipe/packages/ui/src/slots.tsx
  - vendor/larecipe/larecipe/packages/ui/src/registry.ts
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/LaRecipe.php
---

# Eject

<Badge tone="neutral">Coming soon</Badge>

Ejecting will copy LaRecipe's UI source into your app so you own it outright. It is planned, and it is not in this release: there is no `larecipe:ui:eject` or `larecipe:ui:diff` command yet. The tools below work today, and each of them keeps you on LaRecipe's updates.

## What to use today

| You want to | Use |
| --- | --- |
| Change the accent, fonts, corners or default color mode | [Theme tokens](theme.md) in `larecipe.json` |
| Restyle anything the tokens do not cover | A `theme.css` listed under `assets.css` in `larecipe.json`, or `LaRecipe::head()` (see [Theme](theme.md#add-your-own-stylesheet)) |
| Add a form, a banner or a badge on every page | An [insert point](insert-points.md) |
| Change or wrap a region such as the footer or the sidebar | A [slot override](insert-points.md#replace-a-built-in-region) with `useSlotDefault()` |
| Use your own component inside a page | A [custom React component](custom-components.md) |

A slot override is the closest thing to a per-component eject today. It replaces one region by name, and `useSlotDefault()` lets you keep the original inside your version instead of copying its code:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension, useSlotDefault } from '@larecipe/ui'

function PageFooter() {
  const Default = useSlotDefault('PageFooter')

  return (
    <>
      <Default />
      <p className="acme-page-help">Something missing? Ask in the community forum.</p>
    </>
  )
}

export default defineExtension({ slots: { PageFooter } })
```

## What eject is planned to do

Two sizes, so you take ownership of no more than you need:

- **One component.** `larecipe:ui:eject Sidebar` will copy that one component's source into your extension as a slot override. You own the sidebar; everything else keeps updating with LaRecipe.
- **The whole UI.** `larecipe:ui:eject` with no argument will copy the full UI source into `resources/larecipe-ui/` and wire it into your Vite build. From then on, upgrades to the UI are yours to make by hand.

In both cases, `larecipe:ui:diff` will show what changed upstream since you ejected, so you can bring fixes across.

## Next

- [Insert points and slots](insert-points.md): every insert point and every slot name.
- [Custom React components](custom-components.md): the extension these overrides live in.
