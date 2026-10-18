---
title: Markdown
description: The Markdown LaRecipe reads, from GitHub-flavored basics to alerts, directives, component tags and the links that keep working on GitHub.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/MarkdownEnvironmentFactory.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentProcessor.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/Slugger.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageSanitizer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Enums/HtmlInput.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/MarkdownServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/**
  - vendor/larecipe/larecipe/src/Domains/Content/Services/FrontmatterParser.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Page.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsLinkResolver.php
---

# Markdown

A LaRecipe page is a Markdown file with YAML frontmatter at the top. You write CommonMark with the GitHub extensions you already know, and LaRecipe adds a few things docs need: alerts, components, snippet includes and links that resolve to the right page in every version. Everything here also reads well on GitHub, which is where most of your contributors will see it first.

```markdown
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources: ["app/Webhooks/**", "routes/webhooks.php"]
---

# Webhooks

Every event is signed. Verify the signature before you trust the payload.
```

Frontmatter configures the page and nothing else. The keys are listed in the [frontmatter reference](../reference/frontmatter.md); set-wide settings live in [larecipe.json](../reference/manifest.md).

## Titles and the first heading

The page title comes from `title` in the frontmatter. Without one, LaRecipe takes the first `# Heading` in the body, and failing that, a title made from the file name (`index` becomes "Introduction").

The page header already shows the title, so a leading `# Heading` is not repeated in the article. Keep it anyway: it makes the file read well on GitHub and in your editor.

If the frontmatter is not valid YAML, the page still renders, with empty frontmatter, and `php artisan larecipe:validate` tells you what to fix.

## The basics

LaRecipe parses Markdown with league/commonmark, with these extensions on every page:

| Feature | Write |
| --- | --- |
| Tables, task lists, strikethrough, autolinks | GitHub-flavored Markdown, as on GitHub |
| Footnotes | `A claim.[^1]` and later `[^1]: The source.` |
| Description lists | A term on one line, then `: its definition` on the next |
| Code blocks | Fenced code with a language. See [code blocks](code-blocks.md) |
| Math | A `$$` block, rendered as a formula (see [components](components.md)) |
| Diagrams | A ` ```mermaid ` fence (see [components](components.md)) |

A footnote, a task list and a description list, as they render here:

- [x] Write the page
- [ ] Ask a teammate to read it

Provenance
: Who changed the page last, and with what.[^provenance]

[^provenance]: For a repository set, LaRecipe reads it from git history.

## Headings and anchors

Every heading gets an id, so `#anchors` work. The id is the heading text in lowercase, with punctuation removed and spaces turned into dashes: `## Retry failed deliveries` becomes `#retry-failed-deliveries`. Arabic and other scripts keep their letters. When two headings would share an id, the second gets `-1`, the third `-2`.

To pin an id that survives a rewording, set it yourself:

```markdown
## Retry failed deliveries {#retries}
```

`{#id}` and `{.class}` are the only attributes LaRecipe accepts, on headings and other blocks. Anything else, such as an `onclick`, is ignored.

H2 and H3 headings build the table of contents in the page rail and split the page into search records, so give each section its own heading rather than a bold line.

## Links

Link to other pages with relative `.md` paths, the way you would on GitHub:

```markdown
See [webhook retries](./webhooks.md#retries), or go back to [installation](../get-started/installation.md).
```

LaRecipe resolves each one to the reader's URL for the same version and language, so the file works on GitHub and the page works on your site. In the Markdown agents read, the same links point at the other pages' `.md` twins, as absolute URLs.

- A relative link resolves from the folder of the page that contains it.
- A link that starts with `/` and ends in `.md` resolves from the root of the current version: `[install](/get-started/installation.md)` reaches the same page from anywhere in the version.
- A root link that spells out a version, after the set's prefix and an optional language, reaches that page: `/3.x/index.md` is the 3.x landing page, and `/ar/3.x/index.md` the Arabic one. `larecipe:validate` checks these links the same way.
- Links with a scheme (`https://`, `mailto:`) and `#anchors` are left alone. To link to a URL that ends in `.md` but is not one of your pages, write the full URL.
- `javascript:` and other unsafe links are dropped.
- `php artisan larecipe:validate` reports links to pages or anchors that do not exist, with the closest match as a fix.

Content written for LaRecipe 2 can keep `{{version}}` in a URL: it is replaced with the current version, in links and in component props such as `href` and `src`. New pages should use relative links instead.

## Images and files

Put images next to the page, or in the version's `_assets` folder, and link to them with a path relative to the page:

```markdown
![The review queue](./_assets/review-queue.png)
```

LaRecipe resolves the path from the page's folder and looks for the file in the version folder, then in the version's `_assets` folder, then in an `_assets` folder at the docs root, and serves it from `/_assets/{version}/...` under the set's prefix. A path that starts with `/` resolves from the version root in the same way. For an image that lives anywhere else, such as your app's `public/` folder or a CDN, write the full URL with its scheme.

To give an image a caption, wrap it in a [Frame](components.md).

## Alerts

GitHub's alert syntax renders as a callout. It is the most portable way to write one: GitHub shows it, every agent reads it, and LaRecipe styles it.

> [!TIP] Prefer alerts for simple callouts
> They read the same in your editor, on GitHub and on your site.

```markdown
> [!TIP] Prefer alerts for simple callouts
> They read the same in your editor, on GitHub and on your site.
```

The five types are `NOTE`, `TIP`, `IMPORTANT`, `WARNING` and `CAUTION`. Text after the marker on the first line becomes the callout's title.

## Components

For anything Markdown cannot say on its own (tabs, steps, cards, a video), write a component as a PascalCase tag on its own line, with a blank line before and after:

```markdown
<Callout type="warning" title="Before you deploy">
Run `php artisan optimize` so the first reader never waits.
</Callout>

<Video placeholder="Demo: installing LaRecipe (2 min)" />

<Badge tone="success">Stable</Badge>
```

- A component with content opens with `<Name>` and closes with `</Name>`. Without content it closes itself: `<Name prop="value" />`. A component with short content can sit on one line, like the badge above.
- Content inside a component is Markdown, so lists, code blocks and other components work there.
- Props are strings in quotes. A bare prop (`open`) means true, `prop="false"` means false, and JSON goes in braces: `cols={3}`. There are no JSX expressions: LaRecipe never evaluates code from your docs.
- An unknown component renders as escaped text, never as HTML. Unknown components and missing or invalid props fail `php artisan larecipe:validate`.

Every built-in component is on the [components page](components.md), with a live example. You can add your own with [custom components](../customize/custom-components.md).

### Directives

Components also have a directive form, if you prefer it. A directive and a tag produce exactly the same page:

```markdown
:::warning Before you deploy
Run `php artisan optimize` so the first reader never waits.
:::

::::tabs {sync=package-manager}
:::tab npm
`npm install`
:::
:::tab pnpm
`pnpm install`
:::
::::
```

- `:::note`, `:::tip`, `:::important`, `:::warning`, `:::caution` and `:::danger` are callouts. Any other name is a component: `:::code-group` is `CodeGroup`.
- Text after the name fills the `title` prop (`label` for a tab). Other props go in braces: `{type=warning}`.
- To nest directives, give the outer one a longer fence (`::::`). A closing fence closes the innermost directive it can.

## Raw HTML

Raw HTML is escaped by default: `<div>` or `<details>` in a page shows up as text, never as markup. Component tags are recognized by LaRecipe's own parser, which is why they work while HTML does not. This keeps every page safe to render and keeps the agent Markdown clean. When you need something HTML would give you, there is usually a component for it ([Accordion](components.md) for `<details>`, [Frame](components.md) for a figure), or you can [build one](../customize/custom-components.md).

HTML comments are the exception: `<!-- a note for the next writer -->` is removed from the page, its Markdown twin and search, so you can leave notes in the source. Inside inline code and code blocks, comments stay as written.

A repository set whose pages need raw HTML can allow it with `html_input` in its entry in `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // route, source and access as usual
        'html_input' => 'allow',
    ],
],
```

Raw HTML then passes as written, except the tags GitHub filters, such as `<script>`, `<iframe>` and `<style>`, which stay escaped. Its pages are reviewed in git with your code, so that choice is yours. A database set always escapes raw HTML and also runs every page it renders through an HTML sanitizer, because people editing in the admin UI may be less trusted than your codebase; `html_input` has no effect there.

LaRecipe 2 compiled Blade inside Markdown. Version 3 does not evaluate Blade or PHP in a page, on any source.

## Code samples from your code

A line like `<<< @/app/Http/Controllers/SubscribeController.php` pulls a file, or one marked region of it, straight from your codebase into a code block. Samples taken from the code cannot drift from it, and a missing region fails validation. [Code blocks](code-blocks.md) covers the syntax.

## What agents read

Every page has a Markdown twin at the same URL with `.md` added. It is not your source file: LaRecipe renders it from the same parsed page as the HTML, with a short header (title, description, URL, version), components turned into portable Markdown, relative links made absolute and live references resolved to their current values. [Agent surfaces](../agents/surfaces.md) shows a full example.
