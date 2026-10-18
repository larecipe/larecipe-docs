---
title: Markdown
description: Write pages in GitHub-flavored Markdown, link them with relative .md paths, and add alerts and components where plain Markdown falls short.
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

A page is a Markdown file with a short YAML frontmatter block on top. Write the GitHub-flavored Markdown you already know, link to other pages with relative `.md` paths, and the same file reads well on GitHub and on your site.

```markdown
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources: ["app/Webhooks/**", "routes/webhooks.php"]
---

# Webhooks

Every event is signed. Verify the signature before you trust the payload.
See [installation](../get-started/installation.md) for the setup.
```

[Project structure](../get-started/project-structure.md) explains the three frontmatter keys every page needs and where the file goes.

## Links

Link to other pages with relative `.md` paths, the way you would on GitHub:

```markdown
See [webhook retries](./webhooks.md#retries), or go back to [installation](../get-started/installation.md).
```

LaRecipe turns each link into the reader's URL for the same version and language. `php artisan larecipe:validate` reports links to pages or anchors that do not exist, with the closest match as a fix.

## Headings and anchors

Every heading gets an id: the text in lowercase, with punctuation removed and spaces turned into dashes. `## Retry failed deliveries` becomes `#retry-failed-deliveries`. To keep an id stable when you reword the heading, set it yourself:

```markdown
## Retry failed deliveries {#retries}
```

H2 and H3 headings build the table of contents and split the page into search records, so give each section its own heading rather than a bold line.

## Images and files

Put images next to the page, or in the version's `_assets` folder, and link with a relative path:

```markdown
![The review queue](./_assets/review-queue.png)
```

To give an image a caption, wrap it in a [Frame](components.md#frame).

## Alerts

GitHub's alert syntax renders as a callout. It is the most portable way to write one: GitHub shows it, agents read it, and LaRecipe styles it.

> [!TIP] Prefer alerts for simple callouts
> They read the same in your editor, on GitHub and on your site.

```markdown
> [!TIP] Prefer alerts for simple callouts
> They read the same in your editor, on GitHub and on your site.
```

The types are `NOTE`, `TIP`, `IMPORTANT`, `WARNING` and `CAUTION`. Text after the marker on the first line becomes the title.

## Components

For what Markdown cannot say on its own (steps, tabs, cards, a video), write a component as a PascalCase tag on its own line, with a blank line before and after:

```markdown
<Callout type="warning" title="Before you deploy">
Run `php artisan optimize` so the first reader never waits.
</Callout>

<Badge tone="success">Stable</Badge>
```

Content inside a component is Markdown. Props are quoted strings. An unknown tag renders as escaped text and fails `larecipe:validate`. The [components page](components.md) shows every built-in, live.

### Directives

Every component also has a `:::` directive form. A directive and a tag produce the same page.

<Accordion title="Write components as directives">

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

</Accordion>

## Raw HTML

Raw HTML is escaped: `<div>` or `<details>` in a page shows up as text, never as markup. Use a component instead ([Accordion](components.md#accordion-and-accordiongroup) for `<details>`, [Frame](components.md#frame) for a figure), or [build your own](../customize/custom-components.md).

<Accordion title="Allow raw HTML on a repository set">

A repository set whose pages need raw HTML can allow it with `html_input` in its entry in `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // route, source and access as usual
        'html_input' => 'allow',
    ],
],
```

Raw HTML then passes as written, except the tags GitHub filters, such as `<script>`, `<iframe>` and `<style>`, which stay escaped. Its pages are reviewed in git with your code, so that choice is yours.

A database set always escapes raw HTML and also runs every page through an HTML sanitizer, so `html_input` has no effect there. See [the database source](../sources/database.md).

LaRecipe 2 compiled Blade inside Markdown. Version 3 never evaluates Blade or PHP in a page, on any source.

</Accordion>

## Details

<AccordionGroup>

<Accordion title="Every Markdown feature">

LaRecipe parses Markdown with league/commonmark, with these extensions on every page:

| Feature | Write |
| --- | --- |
| Tables, task lists, strikethrough, autolinks | GitHub-flavored Markdown, as on GitHub |
| Footnotes | `A claim.[^1]` and later `[^1]: The source.` |
| Description lists | A term on one line, then `: its definition` on the next |
| Code blocks | Fenced code with a language. See [code blocks](code-blocks.md) |
| Math | A `$$` block, rendered as a formula. See [components](components.md#math) |
| Diagrams | A ` ```mermaid ` fence. See [components](components.md#mermaid) |

</Accordion>

<Accordion title="Titles and the first heading">

The page title comes from `title` in the frontmatter. Without one, LaRecipe takes the first `# Heading` in the body, and failing that, a title made from the file name (`index` becomes "Introduction").

The page header already shows the title, so a leading `# Heading` is not repeated in the article. Keep it anyway: the file then reads well on GitHub and in your editor.

If the frontmatter is not valid YAML, the page still renders, with empty frontmatter, and `php artisan larecipe:validate` tells you what to fix.

</Accordion>

<Accordion title="How heading ids are made">

Arabic and other scripts keep their letters in the id. When two headings would share an id, the second gets `-1`, the third `-2`. `{#id}` and `{.class}` are the only attributes LaRecipe accepts, on headings and other blocks. Anything else, such as an `onclick`, is ignored.

</Accordion>

<Accordion title="How links resolve">

- A relative link resolves from the folder of the page that contains it.
- A link that starts with `/` and ends in `.md` resolves from the root of the current version: `[install](/get-started/installation.md)` reaches the same page from anywhere in the version.
- A root link that spells out a version, after the set's prefix and an optional language, reaches that page: `/3.x/index.md` is the 3.x landing page, and `/ar/3.x/index.md` the Arabic one. `larecipe:validate` checks these links the same way.
- Links with a scheme (`https://`, `mailto:`) and `#anchors` are left alone. To link to a URL that ends in `.md` but is not one of your pages, write the full URL.
- `javascript:` and other unsafe links are dropped.
- In the Markdown agents read, links to other pages point at their `.md` twins, as absolute URLs.

Content written for LaRecipe 2 can keep `{{version}}` in a URL: it is replaced with the current version, in links and in component props such as `href` and `src`. New pages should use relative links.

</Accordion>

<Accordion title="Where LaRecipe looks for images">

LaRecipe resolves an image path from the page's folder and looks for the file in the version folder, then in the version's `_assets` folder, then in an `_assets` folder at the docs root. It serves the file from `/_assets/{version}/...` under the set's prefix. A path that starts with `/` resolves from the version root in the same way. For an image that lives anywhere else, such as your app's `public/` folder or a CDN, write the full URL with its scheme.

</Accordion>

<Accordion title="Component syntax in full">

- A component with content opens with `<Name>` and closes with `</Name>`. Without content it closes itself: `<Name prop="value" />`. A component with short content can sit on one line, like a badge.
- Lists, code blocks and other components work inside a component.
- A bare prop (`open`) means true, `prop="false"` means false, and JSON goes in braces: `cols={3}`. There are no JSX expressions: LaRecipe never evaluates code from your docs.
- Unknown components and missing or invalid props fail `php artisan larecipe:validate`.

</Accordion>

<Accordion title="Notes for the next writer">

HTML comments are removed from the page, its Markdown twin and search, so `<!-- a note for the next writer -->` stays in the source only. Inside inline code and code blocks, comments stay as written.

</Accordion>

</AccordionGroup>

A line like `<<< @/app/Billing/Invoice.php#refund` pulls code straight from your app into a code block, so a sample cannot drift from the code: see [code blocks](code-blocks.md#snippet-includes). Every page also has a Markdown twin for agents, rendered from the same parsed page: see [agent surfaces](../agents/surfaces.md).

## Next

- [Components](components.md): every built-in component, with a live example.
- [Code blocks](code-blocks.md): titles, highlights, diffs and snippet includes.
- [Frontmatter reference](../reference/frontmatter.md): every key a page can set.
