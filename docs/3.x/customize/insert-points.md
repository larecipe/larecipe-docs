---
title: Insert points and slots
description: Add a component to every page at one of seven named positions, opt a page out, and replace a built-in region such as the footer while keeping the original inside it.
sources:
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/resources/views/page.blade.php
  - vendor/larecipe/larecipe/resources/views/prerender/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/InsertRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Frontmatter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/ui/src/app/Insert.tsx
  - vendor/larecipe/larecipe/ui/src/app/App.tsx
  - vendor/larecipe/larecipe/ui/src/regions/**
  - vendor/larecipe/larecipe/packages/ui/src/slots.tsx
  - vendor/larecipe/larecipe/packages/ui/src/registry.ts
  - vendor/larecipe/larecipe/packages/ui/src/types.ts
  - docs/larecipe.json
  - docs/3.x/index.md
---

# Insert points and slots

An **insert point** adds one of your components at a named position on every page. This site puts its newsletter form after every article with one line in `larecipe.json`:

```json title="docs/larecipe.json"
{
  "inserts": { "article.after": ["Subscribe"] }
}
```

Each point holds a list of component names and renders them in order:

| Insert point | Where it renders |
| --- | --- |
| `header.actions` | In the header, before the navigation links and the Ask button. Keep it small. |
| `sidebar.before` | At the top of the sidebar, above the navigation. |
| `sidebar.after` | In the sidebar, below the navigation. |
| `article.before` | Between the page header (title, description, the "Updated" line) and the article. |
| `article.after` | Right after the article, before the page footer (feedback, previous and next, the edit link). |
| `toc.after` | In the right rail, below the table of contents, the page actions and the last change. |
| `footer` | In the site footer, before its links. |

A component placed here renders on the server first, so readers without JavaScript and crawlers get its fallback, and React takes it over in place. Agents get nothing from insert points: they are page chrome, like the navigation, so they stay out of the Markdown twin, `llms.txt` and MCP.

The component must be one you registered yourself. If you have not written one yet, start with [custom React components](custom-components.md).

> [!NOTE]
> An insert point has no way to pass props, so the component gets its default props. Give every prop a default in its PHP definition, as `Subscribe` does with `'list' => ['type' => 'string', 'default' => 'larecipe']`.

## Opt a page out

A page can turn off one point, or all of them, in its frontmatter:

```yaml
inserts: { article.after: false }
```

```yaml
inserts: false
```

The [Introduction](../index.md) does the first. It already shows the newsletter form inline, so it does not get a second copy. The opt-out applies on the server and in the browser, during client-side navigation too, and to components the extension placed.

## Replace a built-in region

A **slot override** replaces a built-in region of the reading UI with your own component, which can still render the original inside it. Inserts add, slots replace: reach for an insert point first, because it works without JavaScript and never takes anything away.

These regions are named slots:

| Slot | The region |
| --- | --- |
| `Header` | The top bar, with everything in it |
| `Logo` | The logo and site name in the header |
| `VersionSwitcher` | The version menu |
| `Search` | The search button that opens the palette |
| `LocaleSwitcher` | The language menu |
| `Sidebar` | The navigation sidebar |
| `PageHeader` | The breadcrumb, title, description and the "Updated" line |
| `PageFooter` | Feedback, previous and next, and the edit link |
| `Feedback` | The "Was this page helpful?" widget |
| `Toc` | The table of contents |
| `PageActions` | The "For agents" actions in the rail |
| `LastChange` | The card for the change that last touched the page |
| `Footer` | The site footer |

Replace one by name in your extension. `useSlotDefault('Footer')` returns the built-in footer as a component you can render wherever you like:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension, useSlotDefault } from '@larecipe/ui'
import { Subscribe } from './Subscribe'

function Footer() {
  const Default = useSlotDefault('Footer')

  return (
    <>
      <Default />
      <p className="acme-footer-note">These docs cover Acme 4. Older releases live in the version menu.</p>
    </>
  )
}

export default defineExtension({ components: { Subscribe }, slots: { Footer } })
```

Slot overrides run in the browser only. Readers without JavaScript and crawlers see the original region, so keep overrides to presentation and put content that matters in the page or at an insert point.

## Details

<AccordionGroup>

<Accordion title="Place components from the extension">

An extension can place components too, next to the components it registers:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension } from '@larecipe/ui'
import { Subscribe } from './Subscribe'

export default defineExtension({
  components: { Subscribe },
  inserts: { 'article.after': ['Subscribe'] },
})
```

The server does not know about placements made here. The page HTML holds an empty placeholder, and the component appears only once the reader's browser has loaded the extension. Readers without JavaScript and crawlers see nothing there, so prefer `larecipe.json` for anything that matters.

When both set a point, `larecipe.json` wins, point by point. If the manifest lists `article.after`, the extension's list for `article.after` is ignored, not merged. Points only the extension sets still apply.

</Accordion>

<Accordion title="Rules for insert points in larecipe.json">

The keys must be the seven point names, and each name must be a PascalCase component name; `larecipe:validate` reports anything else as a `manifest.schema` error.

The page HTML already holds the component's fallback, and React takes it over in place without shifting the layout. That fallback comes from the component's PHP definition (`LaRecipe::component('Subscribe')->fallback(...)`), and it receives the page it is rendering on, so it can link back to it.

</Accordion>

<Accordion title="What the page HTML holds">

On this site, every page except the Introduction carries this in its HTML, right after the article (abridged):

```html
<div class="lr-insert" data-lr-insert="article.after"><div data-lr-component="Subscribe" data-lr-props='{"list":"larecipe"}'><form class="lr-subscribe" …>…</form></div></div>
```

- **Readers with JavaScript** get the React component, mounted into that placeholder like any island.
- **Readers without JavaScript and crawlers** get the fallback HTML.
- **Agents** get nothing. Read [this page as Markdown](https://larecipe.saleem.dev/3.x/customize/insert-points.md) and you will not find the form at the end.

To style a point, target its wrapper: `.lr-insert[data-lr-insert="article.after"]`.

</Accordion>

<Accordion title="Rules for slot overrides">

- Call `useSlotDefault()` inside the override for that slot, with the slot's name. Anywhere else it throws.
- Leave `<Default />` out and your component replaces the region entirely. Return `null` and the region disappears.
- To remove the feedback widget, turn off `feedback.enabled` in `config/larecipe.php` instead of overriding `Feedback`: that also removes its endpoint.
- The server HTML still holds the built-in region, and React swaps your override in when it mounts. Agents get no page chrome at all.

</Accordion>

</AccordionGroup>

## Next

- [Custom React components](custom-components.md): write the component you place.
- [Theme](theme.md): change colors, fonts and corners without any code.
