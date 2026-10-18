---
title: Frontmatter
description: Every frontmatter key a page can set, with its type, meaning and an example, how unknown keys are handled, and the schema URL.
sources:
  - vendor/larecipe/larecipe/resources/schemas/frontmatter.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Frontmatter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Page.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/FrontmatterSchemaRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/PageBasicsRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Search/Services/SearchIndexRepository.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SourcePattern.php
---

# Frontmatter

Frontmatter is the YAML between the `---` lines at the top of a page. It configures that page only. Set-wide settings belong in [larecipe.json](manifest.md), and navigation structure in `navigation.json`.

Every page should set `title`, `description` and `sources`.

```yaml
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources:
  - app/Webhooks/**
  - routes/webhooks.php
order: 20
---
```

## Keys

| Key | Type | Meaning | Example |
| --- | --- | --- | --- |
| `title` | string | The page title. Without it, the first `# ` heading is used, then the file name. | `title: Webhooks` |
| `description` | string | One sentence about the page. Used in `llms.txt`, meta tags, search results and the MCP `list_pages` tool. `larecipe:validate` warns when it is missing. | `description: Receive signed events and verify them.` |
| `sources` | string or list of strings | Globs of the code the page documents, from the project root, so `larecipe:affected` can tell when the page goes stale. `path#region` narrows a file to one `#region`. | `sources: [app/Webhooks/**, routes/api.php#webhooks]` |
| `order` | number | Position in navigation derived from folders, lower first. Read as an integer. Ignored when the version has a `navigation.json`. | `order: 20` |
| `icon` | string | A Lucide icon name, passed to the UI with the page's navigation entry. The built-in sidebar does not draw page icons in this release. | `icon: webhook` |
| `tags` | list of strings | Extra words for search. | `tags: [billing, events]` |
| `badge` | string, or `{ label, tone }` | A label next to the page's entry in the navigation. `tone` is `info`, `success`, `warning`, `danger` or `neutral`. | `badge: { label: New, tone: info }` |
| `since` | string or number | The version that introduced the feature. Accepted and validated; not displayed in this release. | `since: "3.1"` |
| `deprecated` | boolean or string | Marks the page as deprecated. Accepted and validated; not displayed in this release. | `deprecated: true` |
| `hidden` | boolean | The page is routable but left out of the navigation, search and the sitemap. | `hidden: true` |
| `noindex` | boolean | Sends `X-Robots-Tag: noindex` and a robots meta tag, and leaves the page out of the sitemap. | `noindex: true` |
| `visibility` | `public` or `private` | A private page needs the `viewLarecipe` gate on every surface: HTML, Markdown, search, `llms.txt` and MCP. The value is trimmed and lowercased, and anything other than `public` counts as private, so a typo never publishes a page. | `visibility: private` |
| `redirect_from` | string or list of strings | Old paths that answer with a `301` to this page. Write a path inside the version (`/hooks`) or the full URL path. | `redirect_from: [/hooks]` |
| `layout` | `default`, `wide` or `full` | `default` has the sidebar and the page rail. `wide` drops the rail. `full` drops both. | `layout: wide` |
| `inserts` | `false`, or an object of booleans | Opt this page out of insert points set in `larecipe.json`. `false` turns off all of them. | `inserts: { article.after: false }` |
| `type` | `page` or `openapi` | The page type. `openapi` pages arrive in a later 3.x release; today the value only allows an empty body. | `type: page` |
| `spec` | string | The spec for an `openapi` page. Accepted; not read in this release. | `spec: openapi.yaml` |
| `meta` | object | Values for your own components, which read them as `page.meta`. The only frontmatter readers receive beyond the keys above. | `meta: { plan: enterprise }` |

The insert points `inserts` can name are `header.actions`, `sidebar.before`, `sidebar.after`, `article.before`, `article.after`, `toc.after` and `footer`.

## Unknown keys

Keys LaRecipe does not know are kept on the server and are never an error, but they do not reach readers: they often hold notes for the team, such as an owner or a ticket. Values your own components and extensions read go under `meta:`, the one map passed to the UI, as `page.meta`:

```yaml
---
title: Pricing
owner: billing-team        # stays on the server
meta:
  plan: enterprise         # reaches the browser as page.meta.plan
---
```

`larecipe:validate` checks every page's frontmatter under the `frontmatter.schema` rule:

| Finding | Level | Example message |
| --- | --- | --- |
| YAML that does not parse | error | The frontmatter is not valid YAML: ... |
| A known key with the wrong type or value | error | Frontmatter at /layout: The data should match one item from enum |
| An unknown key within two edits of a known one | warning | Unknown frontmatter key "descripton". Fix: Rename "descripton" to "description". |

Dashes count as underscores when looking for the closest key, so `redirect-from` is caught too.

## The schema

The schema ships with the package at `resources/schemas/frontmatter.schema.json`. Its `$id` is:

```text
https://larecipe.saleem.dev/schemas/3.x/frontmatter.json
```

That URL resolves on this site. Every doc set also serves the copy that matches your installed version at `/{prefix}/schemas/frontmatter.json`; this site's set is mounted at the root, so its copy is at `/schemas/frontmatter.json`. See [larecipe.json](manifest.md#the-schema) for how the schemas are served.

## Next

- [Markdown](../writing/markdown.md): what the page body supports.
- [larecipe.json](manifest.md): the set-wide settings.
