---
title: Project structure
description: What lives in your docs folder, how to add a page, how each file path becomes a URL, and the three frontmatter keys every page starts with.
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

Your docs are a folder of Markdown files, `docs/` at the root of your app. The path of each file is the URL of its page: `docs/billing/refunds.md` is served at `/docs/billing/refunds`.

To add a page, create the file, or let LaRecipe write it with the frontmatter filled in:

```bash
php artisan larecipe:make:page billing/refunds --description="Refund a payment in full or in part." --source="app/Billing/**"
```

Every page starts with three frontmatter keys:

```markdown
---
title: Refunds
description: Refund a payment in full or in part.
sources:
  - app/Billing/**
---

# Refunds

Answer first: what this page is about, and when you need it.
```

- `title` names the page in the header, the sidebar, search and `llms.txt`. Without it, LaRecipe uses the first `# Heading`.
- `description` is one sentence. It appears under the title and in search results, `llms.txt` and the MCP `list_pages` tool.
- `sources` lists the code the page documents, as paths or globs from your project root. It is how LaRecipe knows the page is stale when that code changes. See [how it works](../keeping-current/how-it-works.md).

The [frontmatter reference](../reference/frontmatter.md) lists every other key, such as `order`, `hidden` and `visibility`.

## What lives in the folder

This site's own `docs/` folder, shortened:

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
      - your-first-docs.md
    - writing/
      - markdown.md
    - ar/
      - index.md
      - get-started/
        - installation.md
  - 2.x/
    - navigation.json
    - index.md

</FileTree>

| Name | What it is |
| --- | --- |
| `larecipe.json` | The set's [manifest](../reference/manifest.md): its name, logo, theme, versions and languages. Optional. |
| `3.x/`, `2.x/` | One folder per [version](../writing/versions.md). A set without versions keeps its pages directly in `docs/`. |
| `navigation.json` | The version's sidebar. Optional: without it, the sidebar comes from your folders. See [navigation](../writing/navigation.md). |
| `index.md` | A folder's own page: the landing page at the version root, a section's overview in a subfolder. |
| `ar/` | A [language](../writing/languages.md): one folder per locale code, with the same paths as the original. |
| `_assets/` | Images and other files the pages link to, in a version folder or at the root of the set. |

## From file to URL

A page's URL is its path inside the version, without `.md`, under the set's prefix, the language and the version:

```text
/{prefix}/{locale}/{version}/{path}
```

This site is mounted at the domain root and English is its default language, so most URLs hold only the version and the path:

| File | Page URL | Markdown twin |
| --- | --- | --- |
| `docs/3.x/index.md` | `/3.x` (and `/`, the site's front page) | `/3.x/index.md` |
| `docs/3.x/get-started/installation.md` | `/3.x/get-started/installation` | `/3.x/get-started/installation.md` |
| `docs/3.x/ar/get-started/installation.md` | `/ar/3.x/get-started/installation` | `/ar/3.x/get-started/installation.md` |

With the default prefix, the same pages live under `/docs`: `/docs/3.x/get-started/installation`. Links between pages use file paths, not URLs: write `[installation](../get-started/installation.md)` and LaRecipe turns it into the right URL for the reader's version and language.

## Details

<AccordionGroup>

<Accordion title="Files that are not pages">

Every `.md` file under a version folder is a page, except:

- files and folders whose name starts with `_` (keep drafts and partials in `_drafts/` or `_partials.md`),
- files and folders whose name starts with `.`,
- `node_modules/`, `vendor/` and `skills/`,
- a `README.md` inside a subfolder, so a folder can carry a readme for GitHub without adding a page,
- the folders of other languages, which belong to their own language.

Name files in lowercase with hyphens. The file name is the URL, letter for letter.

</Accordion>

<Accordion title="More URL rules">

- **Index pages** take their folder's URL. `get-started/index.md` is the page `get-started`, at `/3.x/get-started`. A file named `get-started.md` next to the folder makes the same page, so use one or the other.
- **Versions.** A URL without a version redirects to the same page in the default version: `/get-started/installation` goes to `/3.x/get-started/installation`. On a set mounted at the domain root, `/` itself renders the default version's landing page.
- **Languages.** The default language has no segment. A page not yet translated still opens in every language, showing the original.
- **Markdown twins.** Add `.md` to any page URL for the Markdown version agents read, or request the page URL with `Accept: text/markdown`. See [agent surfaces](../agents/surfaces.md).
- **Assets.** An image the page links to, such as `![Diagram](./_assets/diagram.png)` in `3.x/`, is served from `/_assets/3.x/diagram.png` under the set's prefix. [Markdown](../writing/markdown.md) explains where LaRecipe looks for the file.
- **Skills.** `docs/skills/billing-integration/SKILL.md` is served at `/skills/billing-integration/SKILL.md`, with its `references/`, `scripts/` and `assets/` files next to it. Only repository sets serve them.

</Accordion>

<Accordion title="More about larecipe:make:page and the folder">

`larecipe:make:page` writes the page in the default version (`--docs-version` and `--locale` pick another). When the version has a `navigation.json`, add the page there too; [navigation](../writing/navigation.md) shows how.

Without `larecipe.json`, a set is named after its key and has no versions. A language folder may hold its own `navigation.json`; without it, the version's is used. A `skills/` folder at the root of the set holds hand-written skills for agents, one folder per skill with a `SKILL.md`.

Everything here applies to a [repository set](../sources/repository.md). A database set stores the same pages and files as rows, in the same shape. Set-wide settings never go in frontmatter: the theme, versions and languages belong in `larecipe.json`, and the sidebar in `navigation.json`.

</Accordion>

</AccordionGroup>

## Next

- [Markdown](../writing/markdown.md): what a page can hold.
- [Navigation](../writing/navigation.md): the sidebar, from folders or from `navigation.json`.
- [Versions](../writing/versions.md) and [languages](../writing/languages.md): the folders inside a set.
