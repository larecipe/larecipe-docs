---
title: Agent instructions
description: The LaRecipe block in AGENTS.md that tells every AI coding session to update the affected docs before it pushes, how it is generated from your config, and the docs index under it.
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

The core of keeping docs current is a few lines of plain text in `AGENTS.md`, at the root of your repository. Codex, Cursor, Copilot, Gemini CLI and most other coding agents read that file at the start of every session, so every session learns the same rule: before you push, update the docs pages your change affects.

`php artisan larecipe:install` writes LaRecipe's section into `AGENTS.md`, between two markers. It is generated from your config, so it always names the right docs folder, branch and review mode. Under the instructions sits a compact index of your pages, so the agent knows what the docs cover without searching first.

## This site's block

This is the LaRecipe section of this site's own `AGENTS.md`, exactly as `larecipe:agents` wrote it:

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

Search and read the docs with the `larecipe` MCP server. Never write configuration or environment values into docs.

### Docs index: LaRecipe

1 page. One line per page: path, title, description. Read a page with the `read_page` tool of the `larecipe` MCP server, or open its file in `docs/`. Refresh this index with `php artisan larecipe:agents-md`.

- `index`: Introduction. Documentation for LaRecipe.
<!-- larecipe:end -->
```

The index at the bottom was written when the site had one page. It is exactly the kind of drift the [pre-push hook](pre-push-hook.md) catches: once pages are added, removed or retitled, the hook's message asks the agent to run `php artisan larecipe:agents-md` and bring the index up to date.

## What it says

- **Where the docs live**, and that they ship in the same push as the code.
- **Four steps before every push**: list the affected pages with [`larecipe:affected`](how-it-works.md#larecipeaffected), update them with the `larecipe-update-docs` [skill](skills.md), run `larecipe:validate`, and commit the docs with the code.
- **The review mode**: in `required`, show the user the docs diff and wait for approval before committing. In `auto`, the line reads "Review mode is `auto`: update the docs and push without asking." See [review modes](review-modes.md).
- **Where to read the docs**: the `larecipe` MCP server, and a reminder that configuration and environment values never go into docs.

The text is short on purpose. It works for agents LaRecipe has never heard of, costs nothing, and keeps the docs change in your own session, where you can see and steer it.

## Generated from your config

`larecipe:install` and `larecipe:agents` build the block from facts every time. Nobody writes it by hand, so it cannot fall behind your config.

| In the block | Comes from |
| --- | --- |
| Where the docs live | Each set's driver and folder in `config/larecipe.php` (`sets.*.source`). The default set comes first. |
| The branch in step 1 | `instructions.default_branch` (`LARECIPE_DEFAULT_BRANCH`) when set; otherwise the branch `origin/HEAD` points to; otherwise the first of `main`, `master`, `trunk` or `develop` that exists; otherwise `main`. |
| Step 2 and step 4 | The driver: Markdown files for a repository set, a docs migration through the MCP author tools for a database set. |
| The review line | `review`. The block says `auto` only when every set is `auto`. |

For a database set, the block tells the agent to write through the MCP tools, never rows or SQL, and to commit the docs migration:

```markdown
This app's docs live in the database (doc set `handbook`) and are served by LaRecipe. Docs ship in the same push as the code they describe, as docs migrations in `database/migrations/docs/`.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill, through the LaRecipe MCP tools; they write a docs migration in `database/migrations/docs/`; commit it. Never edit docs rows or write SQL by hand. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs migration with the code and push them together.
```

With several sets, the first line names each one ("from 2 doc sets: `docs` in `docs/` and `handbook` in the database"), and step 2 says which sets live in the database. See [docs migrations](../sources/docs-migrations.md).

## Your text stays yours

`AGENTS.md` belongs to your team. LaRecipe only ever replaces what sits between `<!-- larecipe:start -->` and `<!-- larecipe:end -->`: text above and below the block survives every regeneration byte for byte. When the file has no block yet, LaRecipe appends one after a blank line. When there is no `AGENTS.md`, it creates one.

The file name is `instructions.file` in `config/larecipe.php` (`AGENTS.md` by default).

## The docs index

Under the instructions, the same block carries a compact map of your docs: one line per page, with its path, title and description. An agent always has the map, and fetches a page with the `read_page` MCP tool, or opens its file, when it needs it. For an app's internal docs (architecture notes, runbooks, a domain glossary) this is often the only way a session learns they exist.

- It indexes the default set's default version and language. `--set=handbook` indexes another set.
- Hidden pages are left out. The landing page comes first, then the top-level pages, then each folder's pages, in alphabetical order.
- Descriptions are cut to 120 characters.
- It stays within `instructions.index_max_bytes` (8192 by default, never less than 1024). When the pages do not fit, descriptions are dropped first, then the list is cut with a last line that points the agent at `list_pages` for the rest.

Write or refresh the index with:

```bash
php artisan larecipe:agents-md
```

`--print` shows the instructions and the index without writing them, and `--dry-run` shows which files would change. `larecipe:agents` also refreshes the index whenever it regenerates the block, unless the index is turned off.

To leave the index out, set `instructions.index` to `false`, or pass `--no-index` to `larecipe:install` or `larecipe:agents`.

<Artisan command="larecipe:agents-md" />

## Claude Code and CLAUDE.md

Claude Code reads `CLAUDE.md`. When your project has one and it does not import `AGENTS.md` yet, LaRecipe asks before adding the import line, in its own marked block at the end of the file:

```markdown
<!-- larecipe:start -->
@AGENTS.md
<!-- larecipe:end -->
```

It never adds the line without your consent. Answer the installer's question, pass `--claude-md` to `larecipe:install` or `larecipe:agents-md` to say yes up front, or add the line yourself. Without a terminal to ask and without the flag, LaRecipe prints a warning and leaves `CLAUDE.md` alone. It never creates a `CLAUDE.md`.

The SessionStart hook also gives Claude Code the before-you-push rule at the start of every session. See [the pre-push hook](pre-push-hook.md).

## With Laravel Boost

When `laravel/boost` is installed (in `vendor/laravel/boost`, or required in `composer.json`), Boost manages the guidelines every agent reads. LaRecipe then writes no block of its own, so the instructions never appear twice. It ships them as a Boost guideline instead, in `resources/boost/guidelines/core.blade.php`:

- The guideline renders the same text the `AGENTS.md` block would hold, from the same config, docs index included. If rendering fails for any reason, it falls back to a generic version of the same four steps rather than leaving the agent with nothing.
- It names the four skills and when to use each.
- It adds scoped rules: for files under `docs/**`, the frontmatter, live reference and component rules; for `database/migrations/docs/**`, that docs migrations come from the MCP author tools and are never written by hand.

Run `php artisan boost:install` (or `boost:update` after a change) and select `larecipe/larecipe`. With Boost installed, `larecipe:agents-md` writes nothing and tells you to run `boost:update`.

## Regenerate after a change

After a package update or a config change (a new set, another branch, a new review mode), regenerate everything LaRecipe gives your agents:

```bash
php artisan larecipe:agents
```

It rewrites the `AGENTS.md` block, re-syncs the [skills](skills.md) with references generated from your app, and compiles the push patterns the [pre-push hook](pre-push-hook.md) reads. It never touches text outside the markers. With Boost installed, it leaves the block and the skills to Boost and only refreshes the generated references in the skill copies Boost wrote.

The installer offers to run it after every `composer update`, by adding `@php artisan larecipe:agents --ansi` to your `post-update-cmd` script (`--composer-script` says yes up front).

<Artisan command="larecipe:agents" />

## Next

- [The pre-push hook](pre-push-hook.md): what happens when an agent forgets these instructions.
- [Skills and the Claude Code plugin](skills.md): the workflows the block points to.
- [Review modes](review-modes.md): `required` and `auto`.
