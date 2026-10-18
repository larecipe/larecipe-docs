---
title: Agent instructions
description: The LaRecipe block in AGENTS.md that tells every AI coding session to update the affected docs before it pushes, with a compact index of your pages, generated from your config.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/InstructionsRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildInstructionsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteInstructionsBlockAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildDocsIndexAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/DocsIndexRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/MarkedBlock.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/PushRange.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/LinkClaudeMdAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Laravel/BoostGuideline.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsMdCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SyncSettings.php
  - vendor/larecipe/larecipe/resources/boost/guidelines/core.blade.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - AGENTS.md
---

# Agent instructions

Codex, Cursor, Copilot, Gemini CLI and most other coding agents read `AGENTS.md`, at the root of your repository, at the start of every session. `php artisan larecipe:install` writes LaRecipe's section into it, between two markers, so every session learns one rule: before you push, update the docs pages your change affects.

The section is generated from your config, so it always names the right docs folder, branch and review mode. Under it sits a compact index of your pages, so the agent knows what the docs cover without searching first. You never edit it by hand. After a config change or a LaRecipe update, regenerate it:

```bash
php artisan larecipe:agents
```

## What it says

- **Where the docs live**, and that they ship in the same push as the code.
- **Four steps before every push**: list the affected pages with [`larecipe:affected`](how-it-works.md#larecipeaffected), update them with the `larecipe-update-docs` [skill](skills.md), run `larecipe:validate`, and commit the docs with the code.
- **The review mode**: in `required`, show the user the docs diff and wait for approval before committing. In `auto`, update the docs and push without asking. See [review modes](review-modes.md).
- **How to write**: people first, with the common case at the top of each page and the details after it.
- **Where to read the docs**: the `larecipe` MCP server, and a reminder that configuration and environment values never go into docs.

The text is short on purpose. It works for agents LaRecipe has never heard of, costs nothing, and keeps the docs change in your own session, where you can see and steer it.

<Accordion title="This site's block">

The LaRecipe section of this site's own `AGENTS.md`, as `larecipe:agents` wrote it, with the index cut to its first three of 46 lines:

```markdown
<!-- larecipe:start -->
## Documentation (LaRecipe)

This app's docs live in `docs/` and are served by LaRecipe. Docs ship in the same push as the code they describe.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs with the code and push them together.

Review mode is `required`: show the user the docs diff and wait for approval before committing it.

Write docs for people first: open each page with the common case in a few short lines, then put options, edge cases and internals after it (in an `<Accordion>` or on a linked page). Order pages from easy to advanced. Agents still read everything in the Markdown twin.

Search and read the docs with the `larecipe` MCP server. Never write configuration or environment values into docs.

### Docs index: LaRecipe (version 3.x)

46 pages. One line per page: path, title, description. Read a page with the `read_page` tool of the `larecipe` MCP server, or open its file in `docs/`. Refresh this index with `php artisan larecipe:agents-md`.

- `index`: Docs that keep up with your code. LaRecipe is a docs site that lives inside your Laravel app. Your coding agent writes the pages and updates them with…
- `upgrade`: Upgrade from v2. Move a LaRecipe 2 site to LaRecipe 3 with one command and a report, then finish the manual items with your coding agent…
- `agents/ask-ai`: Ask AI. Let readers ask a question and get a short answer written only from your docs, with every cited page checked, on your…
- ...
<!-- larecipe:end -->
```

</Accordion>

## Your text stays yours

`AGENTS.md` belongs to your team. LaRecipe only ever replaces what sits between `<!-- larecipe:start -->` and `<!-- larecipe:end -->`: text above and below the block survives every regeneration byte for byte. When the file has no block yet, LaRecipe appends one after a blank line. When there is no `AGENTS.md`, it creates one. The file name is `instructions.file` in `config/larecipe.php` (`AGENTS.md` by default).

## The docs index

Under the instructions, the block carries one line per page, with its path, title and description. An agent always has the map, and reads a page with the `read_page` MCP tool, or opens its file, when it needs it. For internal docs (architecture notes, runbooks, a domain glossary) this is often the only way a session learns they exist.

When pages are added, removed or retitled, refresh it:

```bash
php artisan larecipe:agents-md
```

You rarely have to remember: the [pre-push hook](pre-push-hook.md) asks the agent to run it when the index falls behind.

<Accordion title="How the index is built, and its options">

- It indexes the default set's default version and language. `--set=handbook` indexes another set.
- Hidden pages are left out. The landing page comes first, then the top-level pages, then each folder's pages, in alphabetical order.
- Descriptions are cut to 120 characters.
- It stays within `instructions.index_max_bytes` (8192 by default, never less than 1024). When the pages do not fit, descriptions are dropped first, then the list is cut with a last line that points the agent at `list_pages` for the rest.
- `--print` shows the instructions and the index without writing them, and `--dry-run` shows which files would change.
- `larecipe:agents` also refreshes the index whenever it regenerates the block.
- To leave the index out, set `instructions.index` to `false`, or pass `--no-index` to `larecipe:install` or `larecipe:agents`.

<Artisan command="larecipe:agents-md" />

</Accordion>

## Claude Code and CLAUDE.md

Claude Code reads `CLAUDE.md`. When your project has one that does not import `AGENTS.md` yet, LaRecipe asks before it adds the import. It never adds the line without your consent, and it never creates a `CLAUDE.md`.

<Accordion title="How the import is added">

The import sits in its own marked block at the end of the file:

```markdown
<!-- larecipe:start -->
@AGENTS.md
<!-- larecipe:end -->
```

Answer the installer's question, pass `--claude-md` to `larecipe:install` or `larecipe:agents-md` to say yes up front, or add the line yourself. Without a terminal to ask and without the flag, LaRecipe prints a warning and leaves `CLAUDE.md` alone.

The SessionStart hook also gives Claude Code the before-you-push rule at the start of every session. See [the pre-push hook](pre-push-hook.md).

</Accordion>

## With Laravel Boost

When `laravel/boost` is installed, Boost manages the guidelines every agent reads, so LaRecipe writes no block of its own and ships the same instructions as a Boost guideline. Run `php artisan boost:install` (or `boost:update` after a change) and select `larecipe/larecipe`.

<Accordion title="What the Boost guideline holds">

LaRecipe treats Boost as installed when `vendor/laravel/boost` exists or `composer.json` requires `laravel/boost`. The guideline lives in the package at `resources/boost/guidelines/core.blade.php`:

- It renders the same text the `AGENTS.md` block would hold, from the same config, docs index included. If rendering fails for any reason, it falls back to a generic version of the same four steps rather than leaving the agent with nothing.
- It names the four skills and when to use each.
- It adds scoped rules: for files under `docs/**`, the frontmatter, live reference and component rules; for `database/migrations/docs/**`, that docs migrations come from the MCP author tools and are never written by hand.

With Boost installed, `larecipe:agents-md` writes nothing and tells you to run `boost:update`.

</Accordion>

## Regenerate after a change

After a package update or a config change (a new set, another branch, a new review mode), regenerate everything LaRecipe gives your agents:

```bash
php artisan larecipe:agents
```

It rewrites the `AGENTS.md` block, re-syncs the [skills](skills.md) with references generated from your app, and compiles the push patterns the [pre-push hook](pre-push-hook.md) reads. It never touches text outside the markers. With Boost installed, it leaves the block and the skills to Boost and only refreshes the generated references in the skill copies Boost wrote.

The installer offers to run it after every `composer update`, by adding `@php artisan larecipe:agents --ansi` to your `post-update-cmd` script (`--composer-script` says yes up front).

<Accordion title="Every option">

<Artisan command="larecipe:agents" />

</Accordion>

## Details

<AccordionGroup>

<Accordion title="Where each part of the block comes from">

`larecipe:install` and `larecipe:agents` build the block from facts every time:

| In the block | Comes from |
| --- | --- |
| Where the docs live | Each set's driver and folder in `config/larecipe.php` (`sets.*.source`). The default set comes first. |
| The branch in step 1 | `instructions.default_branch` (`LARECIPE_DEFAULT_BRANCH`) when set; otherwise the branch `origin/HEAD` points to; otherwise the first of `main`, `master`, `trunk` or `develop` that exists; otherwise `main`. |
| Step 2 and step 4 | The driver: Markdown files for a repository set, a docs migration through the MCP author tools for a database set. |
| The review line | `review`. The block says `auto` only when every set is `auto`. |

With several sets, the first line names each one ("from 2 doc sets: `docs` in `docs/` and `handbook` in the database"), and step 2 says which sets live in the database.

</Accordion>

<Accordion title="The block for a database set">

For a database set, the block tells the agent to write through the MCP tools, never rows or SQL, and to commit the docs migration:

```markdown
This app's docs live in the database (doc set `handbook`) and are served by LaRecipe. Docs ship in the same push as the code they describe, as docs migrations in `database/migrations/docs/`.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill, through the LaRecipe MCP tools; they write a docs migration in `database/migrations/docs/`; commit it. Never edit docs rows or write SQL by hand. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs migration with the code and push them together.
```

See [docs migrations](../sources/docs-migrations.md).

</Accordion>

</AccordionGroup>

## Next

- [The pre-push hook](pre-push-hook.md): what happens when an agent forgets these instructions.
- [Skills](skills.md): the workflows the block points to.
- [Review modes](review-modes.md): `required` and `auto`.
