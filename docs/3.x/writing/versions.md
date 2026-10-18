---
title: Versions
description: Serve several versions of your docs side by side, each in its own folder, and start the next one by copying the current one with one command.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Version.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Content/Enums/VersionStatus.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/MakeVersionCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/MakeVersionAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/LlmsTxtBuilder.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
---

# Versions

A doc set can serve the docs for the release people use today next to the docs for the one before it. List the versions in the set's `larecipe.json`, and give each one a folder named after its id:

```json title="docs/larecipe.json"
{
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
  - 2.x/
    - navigation.json
    - index.md

</FileTree>

Each version gets its own URLs (`/docs/3.x/installation`), navigation, search index and `llms.txt`, and readers switch between them in the header. A set without versions works too: its pages sit directly in `docs/` until you add the first version.

This site is an example: it serves `3.x`, the current docs, and `2.x`, the LaRecipe 2 docs converted by the [v2 upgrade](../upgrade.md).

## Add a version

Copy the current docs into a new version, and make it the default:

```bash
php artisan larecipe:make:version 4.x --default
```

It copies the default version's folder and lists the new version in `larecipe.json`. Pass `--from=3.x` to copy another version, and `--dry-run` to see the files it would write. Then edit the pages that changed, and validate that version only:

```bash
php artisan larecipe:validate --docs-version=4.x
```

When the set has no versions yet, name the version its current pages belong to with `--current-as`. The command moves them into that folder before it copies:

```bash
php artisan larecipe:make:version 2.x --current-as=1.x --default
```

`larecipe.json`, `theme.css` and the `skills/` folder stay at the root: they belong to the set, not to a version. The command works on repository sets; a database set adds versions through its manifest, with the `update_manifest` MCP tool.

## Version keys

| Key | Default | Meaning |
| --- | --- | --- |
| `id` | required | The folder name and the URL segment, for example `3.x`. |
| `label` | the `id` | What the version switcher shows. |
| `status` | `current` | `current`, `maintained`, `deprecated` or `archived`. `llms.txt` describes each version by it, so agents know which one to prefer. |
| `default` | `false` | The version readers land on. When no version says `true`, the first one is the default. |
| `visibility` | `public` | `private` versions need the `viewLarecipe` gate on every surface. See [gate blocks and private docs](gate-blocks.md). |

A version can also be a plain string (`"versions": ["3.x", "2.x"]`) when it needs nothing but an id.

## Details

<AccordionGroup>

<Accordion title="URLs, old links and the switcher">

A versioned page lives at `/{prefix}/{version}/{path}`. On this site the set is mounted at the domain root, so the installation page is `/3.x/get-started/installation`, and its Markdown twin is the same URL with `.md`.

A URL without a version redirects to that page in the default version, so links written before your second version existed never break. The one exception is the root of a set mounted at the domain root: `/` renders the default version's landing page directly, which is how this site opens on the Introduction.

A [redirect](navigation.md#redirects) whose `from` has no version applies in every version, and a `to` without a version keeps the reader in theirs. Write the version, as in `/3.x/setup`, to redirect one version only.

Each page's canonical URL is its own URL in its own version: a 2.x page is canonical at its 2.x address.

The switcher in the header lists every version by its `label`. Choosing one keeps you on the same page when that page exists in the other version, and takes you to that version's landing page when it does not.

</Accordion>

<Accordion title="What agents get for each version">

- `/llms.txt` lists the versions with their status, then the default version's pages, so an agent reaches the current docs in one fetch.
- `/{version}/llms.txt` lists that version's pages, and `/{version}/llms-full.txt` holds their full text.
- The MCP tools `search_docs`, `read_page` and `list_pages` take a `version` argument, and use the default version without one.

On this site those are [the root index](/llms.txt), [the 3.x index](/3.x/llms.txt) and [the 2.x index](/2.x/llms.txt).

</Accordion>

<Accordion title="Every larecipe:make:version option">

`larecipe:make:version` reads both forms of the `versions` list, and writes it back the way you wrote it. Read live from this app:

<Artisan command="larecipe:make:version" />

</Accordion>

</AccordionGroup>

## Next

- [Languages](languages.md): the same page in more than one language, inside each version.
- [Navigation](navigation.md): one `navigation.json` per version.
- [larecipe.json reference](../reference/manifest.md): every manifest key.
