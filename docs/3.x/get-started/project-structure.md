---
title: Project structure
description: What lives in your docs folder, how each file path becomes a URL and a Markdown twin, and the frontmatter every page starts with.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageRef.php
  - vendor/larecipe/larecipe/src/Domains/Content/Services/SetRegistry.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/SkillLibrary.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/MakePageCommand.php
  - docs/larecipe.json
---

# Project structure

A doc set is a folder of Markdown files, `docs/` at the root of your app unless you [configure another](configuration.md). It holds one manifest for the whole set, a folder per version, and inside each version the pages, their navigation and any translations. The path of each file is the URL of its page.

This page uses this site's own `docs/` folder as the example. Everything here applies to a [repository set](../sources/repository.md); a database set stores the same pages and files as rows, in the same shape.

## This site's docs folder

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
      - your-first-docs.md
      - configuration.md
      - project-structure.md
    - writing/
      - markdown.md
      - components.md
      - navigation.md
    - agents/
      - surfaces.md
    - ar/
      - index.md
      - get-started/
        - installation.md
  - 2.x/
    - navigation.json
    - index.md
    - installation.md
    - configurations.md

</FileTree>

The tree is shortened: every folder in `3.x/` holds more pages. `3.x/` is the current docs, `2.x/` is the LaRecipe 2 docs converted by the [v2 upgrade](../upgrade.md), and `3.x/ar/` holds the Arabic translations.

## What each name means

| Name | Where | What it is |
| --- | --- | --- |
| `larecipe.json` | The root of the set | The [manifest](../reference/manifest.md): the set's name, logo, theme, versions, languages and more. Optional; without it the set is named after its key and has no versions. |
| `3.x/`, `2.x/` | The root of the set | One folder per version, named after the `id` in the manifest's `versions`. A set without versions keeps its pages directly in `docs/`. See [versions](../writing/versions.md). |
| `navigation.json` | Each version folder | The version's sidebar. Optional; without it, navigation comes from folders and frontmatter. See [navigation](../writing/navigation.md). |
| `index.md` | Any folder | The folder's own page: the version's landing page at its root, a section's overview in a subfolder. |
| `ar/` | Each version folder | A [language](../writing/languages.md): one folder per locale code, with the same paths as the original. The default language stays at the version root. |
| `ar/navigation.json` | A language folder | Optional. The language's own sidebar; without it, the version's is used. |
| `_assets/` | A version folder, or the root of the set | Images and other files the pages link to. |
| `skills/` | The root of the set | Hand-written skills for agents, one folder per skill with a `SKILL.md`. |

### What is not a page

Every `.md` file under a version folder is a page, except:

- files and folders whose name starts with `_` (keep drafts and partials in `_drafts/` or `_partials.md`),
- files and folders whose name starts with `.`,
- `node_modules/`, `vendor/` and `skills/`,
- a `README.md` inside a subfolder, so a folder can carry a readme for GitHub without adding a page,
- the folders of other languages, which belong to their own language.

Name files in lowercase with hyphens. The file name is the URL, letter for letter.

## From file to URL

A page's path is its file path inside the version, without `.md`. Its URL is that path under the set's prefix, the language and the version:

```text
/{prefix}/{locale}/{version}/{path}
```

This site's set is mounted at the domain root (its prefix is `''`), and English is the default language, so most URLs hold only the version and the path:

| File | Page URL | Markdown twin |
| --- | --- | --- |
| `docs/3.x/index.md` | `/3.x` (and `/`, the site's front page) | `/3.x/index.md` |
| `docs/3.x/get-started/installation.md` | `/3.x/get-started/installation` | `/3.x/get-started/installation.md` |
| `docs/3.x/ar/get-started/installation.md` | `/ar/3.x/get-started/installation` | `/ar/3.x/get-started/installation.md` |
| `docs/2.x/installation.md` | `/2.x/installation` | `/2.x/installation.md` |

With the default prefix, the same pages live under `/docs`: `/docs/3.x/get-started/installation`.

A few rules fill in the rest:

- **Index pages** take their folder's URL. `get-started/index.md` is the page `get-started`, at `/3.x/get-started`. A file named `get-started.md` next to the folder makes the same page, so use one or the other.
- **Versions.** A URL without a version redirects to the same page in the default version: `/get-started/installation` goes to `/3.x/get-started/installation`. On a set mounted at the domain root, `/` itself renders the default version's landing page.
- **Languages.** The default language has no segment. A page not yet translated still opens in every language, showing the original. See [languages](../writing/languages.md).
- **Markdown twins.** Add `.md` to any page URL for the Markdown version agents read, or request the page URL with `Accept: text/markdown`. The twin of a version's landing page is `/3.x/index.md`. See [agent surfaces](../agents/surfaces.md).
- **Assets.** An image the page links to, such as `![Diagram](./_assets/diagram.png)` in `3.x/`, is served from `/_assets/3.x/diagram.png` under the set's prefix. [Markdown](../writing/markdown.md) explains where LaRecipe looks for the file.
- **Skills.** `docs/skills/billing-integration/SKILL.md` is served at `/skills/billing-integration/SKILL.md`, with its `references/`, `scripts/` and `assets/` files next to it. Only repository sets serve them.

Links between pages use the file paths, not the URLs: write `[installation](../get-started/installation.md)` and LaRecipe turns it into the right URL for the reader's version and language.

## Frontmatter

Every page starts with YAML frontmatter that configures that page and nothing else. Three keys belong on every page:

```markdown
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources:
  - app/Webhooks/**
  - routes/webhooks.php
---

# Webhooks

Answer first: what this page is about, and when you need it.
```

- `title` names the page in the header, the sidebar, search and `llms.txt`. Without it, LaRecipe uses the first `# Heading`.
- `description` is one sentence. It appears under the title and in search results, `llms.txt`, meta tags and the MCP `list_pages` tool.
- `sources` lists the code the page documents, as paths or globs from your project root. It is how `php artisan larecipe:affected` knows which pages a change made stale. See [how it works](../keeping-current/how-it-works.md).

Other keys you will meet early: `order` sorts the page when there is no `navigation.json`, `hidden: true` keeps a page out of the derived navigation, search and the sitemap while it stays reachable by URL, and `visibility: private` puts it behind your [`viewLarecipe` gate](../writing/gate-blocks.md). The [frontmatter reference](../reference/frontmatter.md) lists every key.

Set-wide settings never go in frontmatter. The theme, versions and languages belong in `larecipe.json`, and the sidebar in `navigation.json`.

## Add a page

Create the file by hand, or let LaRecipe write it with the frontmatter filled in:

```bash
php artisan larecipe:make:page billing/refunds --description="Refund a payment in full or in part." --source="app/Billing/**"
```

It writes `docs/3.x/billing/refunds.md` in the default version (`--docs-version` and `--locale` pick another). When the version has a `navigation.json`, add the page there too; [navigation](../writing/navigation.md) shows how.

## Next

- [Navigation](../writing/navigation.md): the sidebar, from folders or from `navigation.json`.
- [Markdown](../writing/markdown.md): what a page can hold.
- [Versions](../writing/versions.md) and [languages](../writing/languages.md): the folders inside a set.
