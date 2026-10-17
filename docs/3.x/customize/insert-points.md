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

Two ways to change the reading UI without forking it. An **insert point** adds one of your components at a named position on every page: a newsletter form after each article, a status badge in the header. A **slot override** replaces a built-in region, such as the footer, with your own component, which can still render the original inside it.

Inserts add, slots replace. Reach for an insert point first: it works without JavaScript, it never takes anything away, and LaRecipe keeps updating everything around it.

Both use components you register yourself. If you have not written one yet, start with [custom React components](custom-components.md).

## The seven insert points

| Insert point | Where it renders |
| --- | --- |
| `header.actions` | In the header, before the navigation links and the Ask button. Keep it small. |
| `sidebar.before` | At the top of the sidebar, above the navigation. |
| `sidebar.after` | In the sidebar, below the navigation. |
| `article.before` | Between the page header (title, description, the "Updated" line) and the article. |
| `article.after` | Right after the article, before the page footer (feedback, previous and next, the edit link). |
| `toc.after` | In the right rail, below the table of contents, the page actions and the last change. |
| `footer` | In the site footer, before its links. |

Each point holds a list of component names and renders them in order.

## Place a component

### In larecipe.json

List the components under `inserts` in the manifest. This site puts its newsletter form after every article with one line:

```json title="docs/larecipe.json"
{
  "inserts": { "article.after": ["Subscribe"] }
}
```

The keys must be the seven point names, and each name must be a PascalCase component name; `larecipe:validate` reports anything else as a `manifest.schema` error.

A component placed here renders on the server first. The page HTML already holds its fallback, so readers without JavaScript and crawlers get the form, and React takes it over in place without shifting the layout. That fallback comes from the component's PHP definition (`LaRecipe::component('Subscribe')->fallback(...)`), and it receives the page it is rendering on, so it can link back to it.

> [!NOTE]
> An insert point has no way to pass props, so the component gets its default props. Give every prop a default in its PHP definition, as `Subscribe` does with `'list' => ['type' => 'string', 'default' => 'larecipe']`.

### In the extension

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

### When both set a point

`larecipe.json` wins, point by point. If the manifest lists `article.after`, the extension's list for `article.after` is ignored, not merged. Points only the extension sets still apply.

## Opt a page out

A page can turn off one point, or all of them, in its frontmatter:

```yaml
inserts: { article.after: false }
```

```yaml
inserts: false
```

The [Introduction](../index.md) does the first. It already shows the newsletter form inline, so its frontmatter carries `inserts: { article.after: false }` and the page does not get a second copy.

The opt-out applies on the server and in the browser, during client-side navigation too, and to components the extension placed.

## What readers and agents get

On this site, every page except the Introduction carries this in its HTML, right after the article (abridged):

```html
<div class="lr-insert" data-lr-insert="article.after"><div data-lr-component="Subscribe" data-lr-props='{"list":"larecipe"}'><form class="lr-subscribe" …>…</form></div></div>
```

- **Readers with JavaScript** get the React component, mounted into that placeholder like any island.
- **Readers without JavaScript and crawlers** get the fallback HTML.
- **Agents** get nothing from insert points. They are chrome, like the navigation, so they stay out of the Markdown twin, `llms.txt` and MCP. Read [this page as Markdown](https://larecipe.saleem.dev/3.x/customize/insert-points.md) and you will not find the form at the end.

To style a point, target its wrapper: `.lr-insert[data-lr-insert="article.after"]`.

## Replace a built-in region

These regions of the reading UI are named slots. Replace one by name in your extension:

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

An override usually wraps the original instead of rewriting it. `useSlotDefault('Footer')` returns the built-in footer as a component you can render wherever you like:

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

- Call `useSlotDefault()` inside the override for that slot, with the slot's name. Anywhere else it throws.
- Leave `<Default />` out and your component replaces the region entirely. Return `null` and the region disappears.
- To remove the feedback widget, turn off `feedback.enabled` in `config/larecipe.php` instead of overriding `Feedback`: that also removes its endpoint.

Slot overrides run in the browser. The server HTML still holds the built-in region, and React swaps your override in when it mounts. Readers without JavaScript and crawlers see the original, and agents get no chrome at all, so keep overrides to presentation and put content that matters in the page or at an insert point.

## Next

- [Custom React components](custom-components.md): write the component you place.
- [Theme](theme.md): change colors, fonts and corners without any code.
- [Eject](eject.md): what is planned for owning the UI source, and what to use today.
