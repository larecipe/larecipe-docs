---
title: The repository source
description: The default source. A doc set as Markdown files in your app's own repository, changed in pull requests, with each page's last change read from git.
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

# The repository source

The repository source keeps a doc set as plain files in your app's own repository: Markdown pages, `larecipe.json` and `navigation.json`. It is the default, and the source we recommend. If you installed LaRecipe without `--driver=database`, you already use it and there is nothing to set up.

Docs change in the same pull request as the code, review happens where code review already happens, and every page shows who changed it last, read from git. The folder layout and how files become URLs are on [project structure](../get-started/project-structure.md).

This site uses it, with its docs in `docs/`:

```php title="config/larecipe.php"
'source' => [
    'driver' => 'repository',
    'path' => base_path('docs'),
],
```

Without `source.path`, a set reads the folder named after its key at the root of your app, so a `handbook` set reads `handbook/`.

## Changes are files

Every change to a repository set is a change to a file in your working tree. Whether your coding agent edits the Markdown or writes through the local MCP author tools, the change becomes part of your next commit and your pull request. There are no drafts and no publish step: the pull request is the review. The [review mode](../keeping-current/review-modes.md) only decides whether the agent shows you the diff before it commits.

The web MCP endpoint never writes to a repository set, even with `mcp.web.author_tools` on. Its changes belong in git.

<Accordion title="How writes stay safe">

- A write can never leave the set's folder. A path that tries is refused.
- A new page whose path is already a folder becomes that folder's `index.md`, so relative links inside the folder keep working.
- `navigation.json` keeps its `$schema` pointer, and JSON is written with two-space indentation and a final newline, the way people format it.
- The local author tools are `write_page`, `move_page`, `delete_page`, `update_navigation` and `update_manifest`. See [the MCP server](../agents/mcp.md).

</Accordion>

## Provenance from git

Every page can show its last change, such as "Updated 3 days ago by Claude", with the pull request or commit it came in. For a repository set, that comes from git, compiled once in your build:

```bash
php artisan larecipe:provenance
```

It runs one `git log` over the set's folder, keeps each file's newest commit, and writes the result to `bootstrap/cache/larecipe/provenance-{set}.php`, so serving a page never runs git. Run it where the full history is checked out: a shallow clone gives every page the same last commit. Production servers do not need git. [Deployment](../operate/deployment.md) shows where the step goes.

To hide the last-change line on every page, set `"provenance": false` in `larecipe.json`.

<AccordionGroup>

<Accordion title="What it reads from each commit">

- **When**: the commit date.
- **Who**: the commit's author.
- **Which agent**: a `Co-Authored-By` trailer that names a coding agent (Claude, Codex, Copilot, Cursor, Gemini or Jules, Devin, Aider, Windsurf) marks the change as written by that agent, under the name in the trailer, and the commit's author is recorded as the person who approved it. An agent's own bot account as the commit author counts too.
- **Which pull request**: `(#482)` at the end of the commit subject, as a squash merge writes it, or `Merge pull request #482`. With `repository.url` in `larecipe.json` (or `repository.docsUrl`, when the docs live in their own repository), the reference becomes the pull request's URL. Without a pull request, the commit hash is the reference.

For example, a squash-merged commit like this one:

```text
Document the --agents flag on larecipe:install (#482)

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
```

gives every page it touched an agent change by `Claude Opus 5.5`, approved by the commit's author, from pull request 482. This is why coding agents should keep their `Co-Authored-By` lines: they are what tells your readers an agent wrote the change.

</Accordion>

<Accordion title="Without git">

LaRecipe falls back to file modification times when git is not installed, when the folder is not inside a git work tree, and for files that were never committed. A page then shows when it changed, with no name. A set with no compiled file at all does the same, from each page's file.

</Accordion>

<Accordion title="Every option">

Without `--set`, the command compiles every repository set; `--clear` removes the compiled files. `larecipe:optimize` does not compile provenance, so a deploy runs both.

<Artisan command="larecipe:provenance" />

</Accordion>

</AccordionGroup>

## Details

<AccordionGroup>

<Accordion title="Fingerprints: why there is nothing to clear after a deploy">

Each version of a repository set has a fingerprint: a short hash of the path, modification time and size of every `.md`, `.json` and `.css` file in the version folder, plus the modification time of `larecipe.json`. Cached page lists, navigation and search records are stored under it, so changing any file rebuilds them on the next request. Rendered pages are keyed by their own content on top of that. See [caching](../operate/caching.md).

</Accordion>

<Accordion title="Several sets in different folders">

Each set points at its own folder, with its own `larecipe.json`, versions, navigation and provenance file. A set serves all its versions from one source. Commands that take `--set` work on one set, and `larecipe:provenance` without it compiles them all. [Configuration basics](../get-started/configuration.md) shows how to add a second set.

</Accordion>

</AccordionGroup>

## Next

- [Project structure](../get-started/project-structure.md): the folder layout and how files become URLs.
- [Deployment](../operate/deployment.md): where `larecipe:provenance` runs.
- [The database source](database.md): go deeper, for teams that manage docs in an admin UI.
