---
title: Versions
description: Serve several versions of your docs side by side, each in its own folder, with URLs, navigation and agent files per version.
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

A doc set can serve several versions at once: the docs for the release people use today, and the docs for the one before it. Each version lives in its own folder, has its own navigation, and gets its own URLs, search index and `llms.txt`. A set without versions works too; you add the first one when you need it.

This site is an example. It serves `3.x`, the current docs, and `2.x`, the LaRecipe 2 docs converted by the [v2 upgrade](../upgrade.md).

## Declare versions in larecipe.json

List the versions in the set's [manifest](../reference/manifest.md):

```json
{
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```

| Key | Default | Meaning |
| --- | --- | --- |
| `id` | required | The folder name and the URL segment, for example `3.x`. |
| `label` | the `id` | What the version switcher shows. |
| `status` | `current` | `current`, `maintained`, `deprecated` or `archived`. `llms.txt` describes each version by it, so agents know which one to prefer. |
| `default` | `false` | The version readers land on. When no version says `true`, the first one is the default. |
| `visibility` | `public` | `private` versions need the `viewLarecipe` gate on every surface. See [gate blocks and private docs](gate-blocks.md). |

A version can also be written as a plain string (`"versions": ["3.x", "2.x"]`) when it needs nothing but an id.

> [!WARNING]
> `larecipe:make:version` reads only the object form. On a manifest that lists versions as strings it reports that the set has no versions yet, so write them as objects before you run it.

## One folder per version

Each version's pages, navigation and assets sit in a folder named after its id, next to `larecipe.json`:

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
    - _assets/
  - 2.x/
    - navigation.json
    - index.md
    - installation.md

</FileTree>

A set without versions keeps its pages directly in `docs/`. Its URLs have no version segment, and it never needs one until you add a second version.

## URLs

A versioned page lives at `/{prefix}/{version}/{path}`. On this site the set is mounted at the domain root, so the installation page is `/3.x/get-started/installation`, and its Markdown twin is the same URL with `.md`.

URLs without a version keep working. A request for `/{prefix}/{path}` redirects to that page in the default version, so links written before your second version existed never break. The one exception is the root of a set mounted at the domain root: `/` renders the default version's landing page directly, which is how this site opens on the Introduction.

> [!NOTE]
> On a versioned set, write the `from` of a [redirect](../reference/manifest.md) with its version segment, for example `/3.x/setup`. A path without one is sent to the default version before redirects are read, so it never reaches the redirect list.

Each page's canonical URL is its own URL in its own version: a 2.x page is canonical at its 2.x address.

## The version switcher

The switcher in the header lists every version by its `label`. Choosing one keeps you on the same page when that page exists in the other version, and takes you to that version's landing page when it does not.

## Add a version

`larecipe:make:version` copies an existing version's folder into a new one and lists it in `larecipe.json`. It reads the command from this app, so the options below are always the real ones:

<Artisan command="larecipe:make:version" />

<Steps>

<Step title="Copy the current docs">

Start the next version from the default one, and make it the default:

```bash
php artisan larecipe:make:version 4.x --default
```

Pass `--from=3.x` to copy a different version, and `--dry-run` to see the files it would write first.

</Step>

<Step title="Version a set that has none yet">

When the set has no versions, its pages sit in `docs/`. Name the version they belong to with `--current-as`, and the command moves them into that folder before it copies:

```bash
php artisan larecipe:make:version 2.x --current-as=1.x --default
```

`larecipe.json`, `theme.css` and the `skills/` folder stay at the root: they belong to the set, not to a version.

</Step>

<Step title="Update the new version and check it">

Edit the pages that changed, then run the validator on that version only:

```bash
php artisan larecipe:validate --docs-version=4.x
```

</Step>

</Steps>

The command works on repository sets. A database set adds versions through its manifest, with the `update_manifest` MCP tool.

## Agents get each version

Every version has its own agent files, and the set's root file points at all of them:

- `/llms.txt` lists the versions with their status, then the default version's pages, so an agent reaches the current docs in one fetch.
- `/{version}/llms.txt` lists that version's pages, and `/{version}/llms-full.txt` holds their full text.
- The MCP tools `search_docs`, `read_page` and `list_pages` take a `version` argument, and use the default version without one.

On this site that is [/llms.txt](/llms.txt), [/3.x/llms.txt](/3.x/llms.txt) and [/2.x/llms.txt](/2.x/llms.txt).

## Next

- [Languages](languages.md): the same page in more than one language, inside each version.
- [Navigation](navigation.md): one `navigation.json` per version.
- [larecipe.json reference](../reference/manifest.md): every manifest key.
