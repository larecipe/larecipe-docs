---
title: Repository (recommended)
description: Keep a doc set as Markdown files in your app's own repository, changed in the working tree, reviewed in pull requests, with each page's last change read from git.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/SourceManager.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/ProvenanceIndexPath.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/GitLogParser.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/MtimeProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ProvenanceIndexWriter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/CompileProvenanceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/ProvenanceCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/AuthorSession.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - config/larecipe.php
---

# Repository (recommended)

The repository source keeps a doc set as plain files in your app's own repository: Markdown pages with YAML frontmatter, plus `larecipe.json` and `navigation.json`. It is the default, and the source we recommend. Docs change in the same pull request as the code, review happens where code review already happens, and every page can show who changed it last, read from git.

This site uses it. Its doc set lives in `docs/`:

```php
// config/larecipe.php
'sets' => [
    'docs' => [
        'route' => [
            'prefix' => '',
            'domain' => null,
            'middleware' => ['web'],
        ],
        'source' => [
            'driver' => 'repository',
            'path' => base_path('docs'),
        ],
        'access' => [
            'mode' => 'public',
        ],
    ],
],
```

`source.path` is the set's folder. Without it, a set reads the folder named after its key at the root of your app, so a `handbook` set reads `handbook/`.

## The layout

<FileTree>

- docs/
  - larecipe.json
  - skills/
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
    - _assets/
      - checkout.png
    - ar/
      - index.md
  - 2.x/
    - index.md

</FileTree>

- **`larecipe.json`** at the root is the set's [manifest](../reference/manifest.md). Its `versions` decide the version folders. A set without versions keeps its pages directly in the root.
- **A version folder** holds that version's pages. A page's path is its file path without `.md`: `3.x/get-started/installation.md` is the page `get-started/installation`, and `billing/index.md` is the page `billing`. The version's landing page is `index.md`, or `README.md`.
- **`navigation.json`** is optional. Without it, navigation comes from the folders. See [navigation](../writing/navigation.md).
- **Other languages** sit in a folder per locale inside the version, such as `3.x/ar/`. See [languages](../writing/languages.md).
- **`_assets/`** holds images and other files, in a version or at the root for every version.
- **`skills/`** holds hand-written skills for agents. See [agent surfaces](../agents/surfaces.md).

Files and folders whose names start with `_` or `.` are never pages, and neither are `README.md` files below the version root. `node_modules`, `vendor` and `skills` folders are skipped. See also [project structure](../get-started/project-structure.md).

## Writes go to the working tree

Every change to a repository set is a change to a file. When your coding agent writes a page, by editing the Markdown or through the local MCP author tools (`write_page`, `move_page`, `delete_page`, `update_navigation`, `update_manifest`), the file changes in your working tree and becomes part of your next commit and your pull request.

There are no drafts and no publish step: the pull request is the review. The [review mode](../keeping-current/review-modes.md) only decides whether the agent shows you the diff before it commits (`required`) or commits with the code (`auto`).

A few rules keep writes safe:

- A write can never leave the set's folder. A path that tries is refused.
- A new page whose path is already a folder becomes that folder's `index.md`, so relative links inside the folder keep working.
- `navigation.json` keeps its `$schema` pointer, and JSON is written with two-space indentation and a final newline, the way people format it.
- The web MCP endpoint never writes to a repository set, even with `mcp.web.author_tools` on. Its changes belong in git. See [the MCP server](../agents/mcp.md).

## Provenance from git

Every page can show its last change: "Updated 3 days ago by Claude", with the pull request or commit it came in. For a repository set, that comes from git, compiled once at build time:

<Artisan command="larecipe:provenance" />

It runs one `git log` over the set's folder and keeps, for each file, its newest commit:

- **When**: the commit date.
- **Who**: the commit's author.
- **Which agent**: a `Co-Authored-By` trailer that names a coding agent (Claude, Codex, Copilot, Cursor, Gemini or Jules, Devin, Aider, Windsurf) marks the change as written by that agent, under the name in the trailer, and the commit's author is recorded as the person who approved it. An agent's own bot account as the commit author counts too.
- **Which pull request**: `(#482)` at the end of the commit subject, as a squash merge writes it, or `Merge pull request #482`. With `repository.url` in `larecipe.json`, the reference becomes the pull request's URL. Without a pull request, the commit hash is the reference.

For example, a squash-merged commit like this one:

```text
Document the --agents flag on larecipe:install (#482)

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
```

gives every page it touched an agent change by `Claude Opus 5.5`, approved by the commit's author, from pull request 482. This is why coding agents should keep their `Co-Authored-By` lines: they are what tells your readers an agent wrote the change.

The result is written to `bootstrap/cache/larecipe/provenance-{set}.php`, a PHP file that OPcache keeps in memory, so serving a page never runs git. Without `--set`, the command compiles every repository set; `--clear` removes the compiled files.

### Run it where the history is

Run `larecipe:provenance` in your build step, where the full git history is checked out. A shallow clone gives every page the same last commit. Production servers do not need git at all: they only read the compiled file. `larecipe:optimize` does not compile provenance, so a deploy runs both:

```bash
php artisan larecipe:provenance
php artisan optimize
```

### Without git

LaRecipe falls back to file modification times when git is not installed, when the folder is not inside a git work tree, and for files that were never committed. A page then shows when it changed, with no name. A set with no compiled file at all does the same, from each page's file.

To hide the last-change line on every page, set `"provenance": false` in `larecipe.json`.

## Fingerprints

Each version of a repository set has a fingerprint: a short hash of the path, modification time and size of every `.md`, `.json` and `.css` file in the version folder, plus the modification time of `larecipe.json`. Cached page lists, navigation and search records are stored under it, so changing any file rebuilds them on the next request, and there is nothing to clear after a deploy. Rendered pages are keyed by their own content on top of that. See [caching](../operate/caching.md).

## Several sets in different folders

Each set points at its own folder, with its own `larecipe.json`, versions, navigation and provenance file:

```php
// config/larecipe.php
'sets' => [
    'docs' => [
        'route' => ['prefix' => 'docs'],
        'source' => ['driver' => 'repository', 'path' => base_path('docs')],
    ],
    'handbook' => [
        'route' => ['prefix' => 'handbook'],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'auth'],
    ],
],
```

A set serves all its versions from one source. Commands that take `--set` work on one set, and `larecipe:provenance` without it compiles them all.

## Next

- [Database and the admin UI](database.md): the other source, for teams that manage docs in an admin UI.
- [Switching sources](switching.md): move a set between the two at any time.
- [Caching and optimize](../operate/caching.md): what to run on deploy.
