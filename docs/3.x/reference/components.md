---
title: Components
description: The props of every built-in component, the live references, the gate blocks and this site's Subscribe component, with what each renders as server HTML and as agent Markdown.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltIn/**
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltInComponents.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/ComponentDefinition.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/COMPONENTS.md
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/References/Markdown/ReferenceComponents.php
  - vendor/larecipe/larecipe/src/Domains/References/Services/ReferenceHtml.php
  - vendor/larecipe/larecipe/src/Domains/References/Services/ReferenceMarkdown.php
  - vendor/larecipe/larecipe/src/Domains/Access/Blocks/**
  - app/Providers/AppServiceProvider.php
  - resources/views/docs/subscribe.blade.php
---

# Components

Every component a page on this site can use: the props each one takes, the HTML the server renders and the Markdown agents read. For live examples of each, see [Components](../writing/components.md).

## How props are checked

- A missing required prop, or a value outside the allowed values, is a `components.props` error. An unknown tag is a `components.unknown` error and renders as escaped text. `larecipe:validate` reports both, and a page that renders with errors is not cached.
- Props a component does not declare pass through untouched.
- `boolean` props read `"false"`, `"0"`, `"no"` and `""` as false; `integer` props are converted from their string.
- Components marked **island** are also mounted by the React UI. Their server HTML is wrapped in `<div data-lr-component="Name" data-lr-props='{...}'>`, where the props are JSON with defaults filled in. The server HTML is complete without JavaScript.
- In agent Markdown, every URL is absolute.

## Built-in components

### Callout

A highlighted note, tip or warning. A GitHub alert (`> [!WARNING]`) and a `:::warning` directive produce the same component.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `type` | string | no | `note` | `note`, `tip`, `important`, `warning`, `danger`, `caution`, `info`, `success` | The kind of callout, and its label. |
| `title` | string | no | | | A label shown above the content, in place of the type. |

- **HTML:** `<aside class="lr-callout" data-type="{type}" role="note">` with `.lr-callout-label` and `.lr-callout-body`.
- **Markdown:** a GitHub alert, with the title as a bold first line. `tip` and `success` become `[!TIP]`, `important` `[!IMPORTANT]`, `warning` `[!WARNING]`, `danger` and `caution` `[!CAUTION]`, the others `[!NOTE]`.

### Steps and Step

A numbered procedure: one `<Step>` per step inside `<Steps>`. `Steps` has no props.

| Component | Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- | --- |
| `Step` | `title` | string | yes | | | What the reader does in this step. |

- **HTML:** `<ol class="lr-steps">`, and each step `<li class="lr-step">` with `.lr-step-title` and `.lr-step-body`.
- **Markdown:** a numbered list, each item `1. **Title**` with its body indented under it.

### Tabs and Tab

Tabbed content: one `<Tab>` per panel inside `<Tabs>`. **Island:** `Tabs`, with a derived `labels` prop.

| Component | Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- | --- |
| `Tabs` | `default` | string | no | | | The label of the tab shown first. |
| `Tabs` | `sync` | string | no | | | Tabs with the same key switch together across the page. |
| `Tab` | `label` | string | yes | | | The tab's label. |

- **HTML:** `<div class="lr-tabs">`, and each panel `<section class="lr-tab" data-label="{label}">` with an `h4.lr-tab-label` and `.lr-tab-body`. Without JavaScript every panel shows, in order.
- **Markdown:** each tab as `**Label**` followed by its content.

### CodeGroup

Consecutive code blocks shown as tabs, labelled by each block's `title`, else its language. No props. **Island:** `CodeGroup`, with a derived `labels` prop.

- **HTML:** `<div class="lr-code-group">` around one `figure.lr-code` per block.
- **Markdown:** the code fences, each with its title.

### Card

A link block with a title and a short description.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `title` | string | no | | | The card title. A card can be body only. |
| `href` | string | no | | | Where the card links. A relative `.md` link works. |
| `icon` | string | no | | | A Lucide icon name. |

- **HTML:** `<a class="lr-card" href="...">` with `.lr-card-title` and `.lr-card-body`, or `<div class="lr-card">` without `href`. `icon` becomes `data-icon`. A one-paragraph body is unwrapped.
- **Markdown:** `- [Title](url): description`, or `- **Title**: description` without `href`.

### CardGroup and Columns

A grid of cards, and side-by-side content.

| Component | Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- | --- |
| `CardGroup` | `cols` | integer | no | `2` | | Columns on wide screens. |
| `Columns` | `cols` | integer | no | `2` | | Columns on wide screens. |

- **HTML:** `<div class="lr-card-group" data-cols="2">` and `<div class="lr-columns" data-cols="2">`.
- **Markdown:** the children. A card group's cards become one list.

### Accordion and AccordionGroup

A collapsible section, and a stack of them. `AccordionGroup` has no props.

| Component | Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- | --- |
| `Accordion` | `title` | string | yes | | | The summary that is always visible. |
| `Accordion` | `open` | boolean | no | `false` | | Start expanded. |

- **HTML:** a native `<details class="lr-accordion">` (with `open` when set), `<summary>` and `.lr-accordion-body`. A group is `<div class="lr-accordion-group">`.
- **Markdown:** `**Title**` followed by the content. A group is its children.

### Badge

A short status label, written on its own line: `<Badge tone="success">Stable</Badge>`.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `tone` | string | no | `info` | `neutral`, `info`, `success`, `warning`, `danger` | The color. |

- **HTML:** `<p><span class="lr-badge" data-tone="{tone}">text</span></p>`.
- **Markdown:** `**text**`.

### Button

A link styled as a button, kept for LaRecipe 2 pages.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `href` | string | no | | | Where the button links. Without it, the button is a static label. |
| `variant` | string | no | `primary` | `primary`, `secondary`, `outline`, `ghost` | The style. |

- **HTML:** `<a class="lr-button lr-button-{variant}" href="...">`, or a `<span>` with the same classes without `href`.
- **Markdown:** `[text](url)`, or the text alone.

### FileTree

A folder structure, written as a nested Markdown list. No props.

- **HTML:** `<div class="lr-file-tree">` around the list.
- **Markdown:** the list.

### Frame

Captioned, zoomable media. **Island:** `Frame`.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `caption` | string | no | | | Shown under the media. |

- **HTML:** `<figure class="lr-frame">` around the media, with a `<figcaption>` when `caption` is set.
- **Markdown:** the media, then `*caption*`.

### Video

A video that loads only when the reader asks. Without `src` it shows a labelled placeholder. **Island:** `Video`.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `src` | string | no | | | A video file, or a YouTube or Vimeo URL. |
| `poster` | string | no | | | The image shown before the video plays. |
| `title` | string | no | | | What the video shows. |
| `duration` | string | no | | | The length, for example `2:40`. |
| `placeholder` | string | no | | | The label of the frame shown until `src` is set. |

- **HTML:** a file is `<figure class="lr-video"><video controls preload="none">`. A YouTube or Vimeo URL is a link (`a.lr-video-link`, "Watch on YouTube") and nothing from the provider loads until the reader asks; the island's props carry `embed` (a `youtube-nocookie.com` or `player.vimeo.com` URL) and `provider`. Without `src`: `figure.lr-video.lr-video-placeholder` with the placeholder, else "Demo video coming soon", and a `media.placeholder` warning. The caption is `title · duration`.
- **Markdown:** `[Video: Title · 2:40](src)`, or `*Video coming soon: {placeholder}*` without `src`.

### Mermaid

A Mermaid diagram. A ```` ```mermaid ```` fence renders the same. No props. **Island:** `Mermaid`, with a derived `source` prop.

- **HTML:** `<pre class="lr-mermaid"><code>` with the escaped source.
- **Markdown:** a `mermaid` fence.

### Math

A TeX formula. A `$$ ... $$` block renders the same. **Island:** `Math`, with a derived `source` prop.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `display` | string | no | `block` | `block`, `inline` | How the formula is set. |

- **HTML:** `<div class="lr-math" data-display="{display}"><code>` with the escaped TeX.
- **Markdown:** a `$$` block.

## Live references

`<Route>`, `<Artisan>`, `<Model>` and `<Event>` read facts from the running app. Each takes one required prop that names its target. See [live references](../writing/live-references.md).

| Component | Prop | Type | Required | Description |
| --- | --- | --- | --- | --- |
| `Route` | `name` | string | yes | The route name, such as `orders.store`. |
| `Artisan` | `command` | string | yes | The command name, such as `orders:prune`. |
| `Model` | `name` | string | yes | The model class, such as `Order` or `App\Models\Order`. |
| `Event` | `name` | string | yes | The event class, such as `OrderShipped` or `App\Events\OrderShipped`. |

- **HTML:** `<div class="lr-reference" data-kind="{route|artisan|model|event}">` with `.lr-reference-head` (the kind and the target), an optional `.lr-reference-description`, a `table.lr-reference-facts`, a `pre.lr-reference-usage` for commands, and one `table.lr-reference-table` per table (arguments, options, columns, relations, payload, listeners). Every value is escaped.
- **Markdown:** a bold line such as `` **Command** `orders:prune`: description ``, the facts as a table, a command's usage in a `bash` fence, then each table under its title.
- **When the target is gone:** the HTML is `<div class="lr-reference" data-kind="..." data-missing="true">` with a notice such as "The route `orders.store` is not defined in this app.", and the Markdown is the same notice as a `> [!WARNING]` alert. `larecipe:validate` reports `references.missing`.

## Gate blocks

`<Can>` and `<Cannot>` show content to some readers only. LaRecipe removes the blocks a reader may not see from the parsed page before any renderer runs, so the HTML, the table of contents, agent Markdown, search and MCP all agree. See [gate blocks and private docs](../writing/gate-blocks.md).

| Component | Prop | Type | Required | Description |
| --- | --- | --- | --- | --- |
| `Can` | `ability` | string | yes | A gate or policy ability your app defines, such as `view-billing`. Shown only to readers it allows. |
| `Cannot` | `ability` | string | yes | Shown only to readers it does not allow, guests included. |

- **HTML and Markdown:** the content, with no wrapper. A block the reader may not see is not in the output at all.
- A block without `ability` is hidden from every reader and reported as an error. An ability your app never defined denies every reader, and `larecipe:validate` warns with the closest defined name.

## This site's components

### Subscribe

The newsletter form after every article on this site. It is a host component, registered in `app/Providers/AppServiceProvider.php` with `LaRecipe::component('Subscribe')` and placed with `inserts` in [larecipe.json](manifest.md). **Island:** `Subscribe`, the React form in `resources/larecipe/Subscribe.tsx`.

| Prop | Type | Required | Default | Allowed values | Description |
| --- | --- | --- | --- | --- | --- |
| `list` | string | no | `larecipe` | | The newsletter list to join. |

- **HTML:** a plain `<form class="lr-subscribe" method="post" action="/newsletter/subscribe">` from `resources/views/docs/subscribe.blade.php`, in the page's language (English or Arabic), so the form works without JavaScript.
- **Markdown:** one line, "Subscribe to the LaRecipe newsletter:" and the URL of the `/newsletter` page.

See [custom components](../customize/custom-components.md) to add your own.
