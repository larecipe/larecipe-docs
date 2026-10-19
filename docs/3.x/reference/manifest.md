---
title: larecipe.json
description: Every key of a doc set's manifest, larecipe.json, with its type, meaning and an example, this site's own manifest, and where the schema is served.
sources:
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Theme.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Version.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Locale.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/AnalyticsTags.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/StylesheetsRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/ManifestSchemaRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Controllers/SchemaController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/ValidationServiceProvider.php
  - docs/larecipe.json
---

# larecipe.json

Every key of `larecipe.json`, a doc set's manifest at the root of its content. It holds presentation and structure; infrastructure lives in [config/larecipe.php](configuration.md), and secrets never belong here. Without the file, a set is named after its key, with one version and one language.

<Accordion title="This site's larecipe.json">

<<< @/docs/larecipe.json

</Accordion>

## Keys

| Key | Type | Meaning | Example |
| --- | --- | --- | --- |
| `$schema` | string | The schema URL, for editors and agents. See [the schema](#the-schema). | `"https://larecipe.saleem.dev/schemas/3.x/larecipe.json"` |
| `name` | string | The product or site name, shown in the header and in `llms.txt` and `skill.md`. Defaults to the set key, capitalized. | `"Acme"` |
| `description` | string | One sentence about the product, for meta tags and agent files. | `"Billing infrastructure for Laravel apps."` |
| `logo` | string, or `{ light, dark }` | The logo. `dark` falls back to `light`. | `{ "light": "/logo.svg", "dark": "/logo-dark.svg" }` |
| `favicon` | string | The favicon. | `"/favicon.svg"` |
| `repository` | object | Where the docs live in git, for "Edit this page" links on repository sets and commit links in provenance. See below. | `{ "url": "https://github.com/acme/acme" }` |
| `theme` | object | Design tokens. See below and [theme](../customize/theme.md). | `{ "accent": "#6d5efc" }` |
| `versions` | list | The set's versions. Omit for an unversioned set. See below. | `[{ "id": "3.x", "default": true }, "2.x"]` |
| `locales` | list | The set's languages. Omit for one language (`en`). See below. | `[{ "code": "en", "default": true }, "ar"]` |
| `landing` | string | The page path a version opens on. Defaults to `index`. | `"introduction"` |
| `navbar` | object | Header links and one call to action. See below. | `{ "cta": { "label": "Dashboard", "href": "/app" } }` |
| `footer` | object | Footer `links` (a list of `{ label, href }`) and `text`. | `{ "text": "Acme, Inc." }` |
| `credit` | boolean | The "Built with LaRecipe" link in the footer. Defaults to `true`. Read by the renderer, not listed in the schema. | `false` |
| `redirects` | list of `{ from, to, status }` | Old URLs that redirect. See below. | `[{ "from": "/3.x/setup", "to": "/3.x/installation" }]` |
| `assets` | `{ css, js }` | `css`: stylesheets next to `larecipe.json`, linked on every page after LaRecipe's CSS, with a content hash in the URL. A missing file is an `assets.missing` error. `js` is accepted by the schema and not loaded. See [theme](../customize/theme.md#add-your-own-stylesheet). | `{ "css": ["theme.css"] }` |
| `inserts` | object | Components placed at named insert points on every page. See below. | `{ "article.after": ["Subscribe"] }` |
| `integrations` | object | Analytics: `integrations.analytics`. Repository sets only. See below. | `{ "analytics": { "plausible": { "domain": "acme.dev" } } }` |
| `agents` | object | Text for agent files. See below. | `{ "llmsTxt": { "instructions": "Prefer the 3.x pages." } }` |
| `provenance` | boolean | Show who and what last changed each page. Defaults to `true`. | `false` |

### repository

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `url` | string (`http` or `https`) | none | The project's repository. The header's icon links to it. |
| `docsUrl` | string (`http` or `https`) | `url` | The repository the docs files live in, when it is not `url`. Edit links are `{docsUrl}/edit/{branch}/{docsPath}/{file}`, and commit links in the Last change ticket use it too. |
| `branch` | string | `main` | The docs repository's branch that edit links point at. |
| `docsPath` | string | `docs` | The docs folder inside the docs repository. |

Most apps keep code and docs in one repository and set only `url`. This site keeps its docs in their own repository, so it sets `docsUrl` while the header still links to the package.

### theme

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `accent` | string | the design system's violet | Any CSS color, for links, the active page, focus rings and highlighted lines. |
| `radius` | `sharp` or `soft` | `sharp` | Corner radius. `soft` rounds the corners more. |
| `fonts.sans` | string | the UI's font | A font family for headings, prose and the interface. |
| `fonts.mono` | string | the UI's font | A font family for code, labels and metadata. |
| `fonts.arabic` | string | Thmanyah Sans | A font family for Arabic text, placed right after the sans font. |
| `mode` | `system`, `light` or `dark` | `system` | The color mode before the reader picks one. |

### versions

Each item is a version id as a string, or an object:

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `id` | string | required | The folder name and URL segment, such as `3.x`. |
| `label` | string | the `id` | The name in the version switcher. |
| `status` | `current`, `maintained`, `deprecated` or `archived` | `current` | `deprecated` and `archived` show a warning badge in the switcher. |
| `default` | boolean | the first version | The version unversioned URLs and the root `llms.txt` use. |
| `visibility` | `public` or `private` | `public` | A private version needs the `viewLarecipe` gate. Any value other than `public` (trimmed, in any case) counts as private. |

See [versions](../writing/versions.md).

### locales

Each item is a locale code as a string, or an object:

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `code` | string | required | The locale code and URL segment, such as `ar`. |
| `label` | string | the language's own name for common codes | The name in the language switcher. |
| `dir` | `ltr` or `rtl` | from the code (`ar`, `he`, `fa`, `ur` and a few more are `rtl`) | The text direction. |
| `default` | boolean | the first locale | The language served without a locale segment. |

See [languages](../writing/languages.md).

### navbar

| Key | Type | Meaning |
| --- | --- | --- |
| `links` | list of `{ label, href, icon }` | Links in the header. `icon` is accepted; the current header shows the label only. |
| `cta` | `{ label, href }` | One outlined button in the header. |

The header already shows `repository.url` as an icon, so a navbar link to the same URL is left out.

Navbar, footer and repository links must be `http`, `https` or `mailto` URLs, or paths on your site. Any other link is left out of every page, and `larecipe:validate` reports it as `links.unsafe`.

### redirects

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `from` | string starting with `/` | required | The full URL path, with the set's prefix and, on a versioned set, the version: `/docs/3.x/setup`, or `/3.x/setup` on a set mounted at the root. |
| `to` | string | required | Where to send the reader: a path or a full URL. |
| `status` | `301`, `302`, `307` or `308` | `301` | The HTTP status. |

A redirect runs only when no page lives at `from`. On a versioned set, a `from` without a version applies in every version: `/docs/setup` matches `/docs/3.x/setup` and `/docs/2.x/setup`, and a `to` inside the set that names no version keeps the reader in their version. A `from` with a version matches that version only. `larecipe:validate` reports redirects that loop (`redirects.loop`) and redirects a page shadows (`redirects.shadowed`).

### inserts

Keys are insert points: `header.actions`, `sidebar.before`, `sidebar.after`, `article.before`, `article.after`, `toc.after` and `footer`. Values are lists of component names in PascalCase. A page opts out with `inserts` in its [frontmatter](frontmatter.md). See [insert points](../customize/insert-points.md).

### integrations.analytics

Each tool's script goes on every page once its values match the shape its provider uses; a value that does not match is left out. Only a repository set reads `integrations` from `larecipe.json`. A database set ignores them (`larecipe:validate` warns with `manifest.integrations`): use `sets.{key}.integrations.analytics` in `config/larecipe.php`, which takes the same keys for every set and wins per tool.

| Key | Required | Optional | Example |
| --- | --- | --- | --- |
| `ga4` | `measurementId` (`G-...`) | | `{ "measurementId": "G-ABC1234567" }` |
| `plausible` | `domain` | `src` (a `https://plausible.io/` URL here; any `https` URL from config) | `{ "domain": "acme.dev" }` |
| `fathom` | `siteId` | `src` (a `https://cdn.usefathom.com/` URL here; any `https` URL from config) | `{ "siteId": "ABCDEFGH" }` |
| `posthog` | `apiKey` (`phc_...`) | `host` (one of PostHog's own hosts here; any `https` URL from config) | `{ "apiKey": "phc_..." }` |
| `custom` | `src` (an `https` URL) | `defer` (default `true`), `async`, `attributes` (`data-*` only) | `{ "src": "https://cdn.acme.dev/a.js" }` |

See [analytics](../operate/analytics.md).

### agents

| Key | Type | Meaning |
| --- | --- | --- |
| `llmsTxt.description` | string | The summary at the top of `llms.txt` and `skill.md`. Without it, `description` is used. |
| `llmsTxt.instructions` | string | Instructions for agents, printed under the summary in `llms.txt` and `skill.md`. |
| `skillMd` | boolean | Accepted by the schema, not read in this release. Turn `skill.md` off with `agents.skill_md` in `config/larecipe.php`. |

See [agent surfaces](../agents/surfaces.md).

## Validation

`larecipe:validate` checks the file under the `manifest.schema` rule: invalid JSON and values that break the schema are errors, a missing `larecipe.json` is a warning, and a top-level key within two edits of a known one is a warning with the fix (`Rename "theem" to "theme".`). Other unknown top-level keys are kept, and a round trip through any source never loses them.

## The schema

The schema ships with the package at `resources/schemas/larecipe.schema.json`. LaRecipe writes this `$schema` URL when it creates or upgrades a manifest, and it is the schema's `$id`:

```text
https://larecipe.saleem.dev/schemas/3.x/larecipe.json
```

Every doc set also serves the schemas that match your installed version at `/{prefix}/schemas/{name}.json`, for `larecipe`, `navigation`, `frontmatter` and `validate`, with `Content-Type: application/schema+json` and `Access-Control-Allow-Origin: *` (`/schemas/larecipe.json` on this site):

<Route name="larecipe.docs.schema" />

The served path has no version segment and always matches your installed package. The versioned `$id` URL resolves too: this site serves `/schemas/3.x/{name}.json`, so an editor that follows `$schema` gets the file.

## Next

- [Configuration](configuration.md): the infrastructure side, in `config/larecipe.php`.
- [Frontmatter](frontmatter.md): settings for one page.
