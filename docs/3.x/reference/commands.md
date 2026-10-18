---
title: Artisan commands
description: Every larecipe:* artisan command, grouped by job, with its usage and options read from the running app.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Upgrade/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Validation/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Export/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Access/Commands/**
  - vendor/larecipe/larecipe/src/Domains/References/Commands/**
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
---

# Artisan commands

Every LaRecipe command starts with `larecipe:`. Every usage block and option table on this page is read from the running app by an [`<Artisan>` live reference](../writing/live-references.md), so it matches `php artisan help` exactly.

Commands that take a docs version use `--docs-version`, never `--version`: Symfony reserves `--version` for the application itself.

## Set up and write

### larecipe:install

Run it once per app. It sets up the doc set with a first page, and writes the `AGENTS.md` instructions, the skills, the MCP config and the hooks for the agents it finds. Every step can be previewed with `--dry-run`, and every prompt has a flag for scripts.

<Artisan command="larecipe:install" />

### larecipe:onboard

Writes starter docs from your app with your own coding agent and the `larecipe-write-docs` skill. It shows the agent command and asks before running it. Nothing is committed: the pages wait for your review.

<Artisan command="larecipe:onboard" />

### larecipe:upgrade

Moves a LaRecipe 2 app to LaRecipe 3: config, navigation and content. Without `--apply` it only writes the report, so you can read the plan first.

<Artisan command="larecipe:upgrade" />

### larecipe:make:page

Creates a page with `title`, `description` and `sources` already in its frontmatter. It works on repository sets, and does not edit `navigation.json`: it tells you when the version has one, so you can add the page.

<Artisan command="larecipe:make:page" />

### larecipe:make:version

Adds a version to a repository set by copying the pages of an existing one. For a set that has no versions yet, `--current-as` names the version its current pages belong to.

<Artisan command="larecipe:make:version" />

### larecipe:navigation:freeze

Writes the navigation LaRecipe derives from folders and frontmatter `order` to `navigation.json`, when you want to control it by hand. Repository sets only.

<Artisan command="larecipe:navigation:freeze" />

## Keep current

### larecipe:affected

Lists the pages whose `sources:` match the files a change touched, and which of them the change left stale. It is the check the pre-push hook and the optional CI check run. Use `--format=github` or `--format=markdown` in CI.

<Artisan command="larecipe:affected" />

### larecipe:sources:suggest

Proposes `sources:` for pages that declare none, from the classes, commands and routes they mention. `--apply` writes them into those pages.

<Artisan command="larecipe:sources:suggest" />

### larecipe:validate

Checks links, navigation, schemas, components, snippets, live references, budgets and skills. It exits with a non-zero code when it finds an error; `--json` prints the report agents read.

<Artisan command="larecipe:validate" />

### larecipe:hook

The command the agent hooks and the git hook call. You do not run it by hand: `pre-push` and `session-start` come from Claude Code or Codex, and `git-pre-push` from the git hook.

<Artisan command="larecipe:hook" />

### larecipe:hooks:git

Installs or removes the git pre-push check, for people who push without an agent. It warns by default; `--strict` holds a push that leaves pages stale. It never replaces an existing hook.

<Artisan command="larecipe:hooks:git" />

### larecipe:sync

Runs a docs agent inside your app, on your own `laravel/ai` provider key, to update the pages a change left stale, the pages you name, or the whole set with `--audit`. It shows its plan and asks before it spends a token. On demand only, never in CI. See [larecipe:sync](../keeping-current/sync.md).

<Artisan command="larecipe:sync" />

## Agents

### larecipe:agents

Regenerates the `AGENTS.md` block, the LaRecipe skills and the compiled hook patterns. Run it after updating LaRecipe or changing its config; the installer can add it to Composer's `post-update-cmd`.

<Artisan command="larecipe:agents" />

### larecipe:agents-md

Writes the docs index, one line per page, into the LaRecipe block of `AGENTS.md`. Run it after adding, removing or renaming pages.

<Artisan command="larecipe:agents-md" />

### larecipe:mcp

Starts the local MCP server over stdio. Your agent's MCP config points at this command; you do not run it yourself. See [MCP tools](mcp-tools.md).

<Artisan command="larecipe:mcp" />

### larecipe:doctor

Grades how ready each doc set is for agents (Markdown twins, `llms.txt` and its links, `skill.md`, MCP, discovery, 404s, redirects and the cache store), with a fix for each finding. It exits with a non-zero code when a check fails.

<Artisan command="larecipe:doctor" />

## Sources

### larecipe:source:export

Writes a doc set as a bundle of canonical files, to a folder or a `.tar.gz`. The bundle is what `source:import` and `pull` read.

<Artisan command="larecipe:source:export" />

### larecipe:source:import

Loads a bundle into a set's configured source, validated first. `--verify` re-reads the set and compares every content hash.

<Artisan command="larecipe:source:import" />

### larecipe:source:move

Copies a set between the repository and the database without loss, and prints the one config change that switches serving to the new source. It never edits `config/larecipe.php` itself.

<Artisan command="larecipe:source:move" />

### larecipe:make:docs-migration

Scaffolds a docs migration for a database set by hand. Each `--page` is filled in with its current content and content hash, so the change stays conflict-safe.

<Artisan command="larecipe:make:docs-migration" />

### larecipe:pull

Refreshes a local or CI database set from a bundle made on production with `larecipe:source:export`, so new docs migrations record production's content hashes.

<Artisan command="larecipe:pull" />

### larecipe:cache:warm

Warms the pages, navigation and manifest of database sets. Run it in your deploy after `php artisan migrate`.

<Artisan command="larecipe:cache:warm" />

## Operate

### larecipe:optimize

Compiles page lists, navigation, every rendered page and the search records of repository sets into the cache. It runs as part of `php artisan optimize`.

<Artisan command="larecipe:optimize" />

### larecipe:clear

Forgets what `larecipe:optimize` compiled: the rendered pages and search records it listed, then its list, `bootstrap/cache/larecipe/optimized.php`. The provenance index, the reference snapshot and the hook patterns in the same folder stay. It runs as part of `php artisan optimize:clear`.

<Artisan command="larecipe:clear" />

### larecipe:provenance

Compiles each page's last change (when, who, the agent, the pull request) from git for repository sets. Run it in your build, where the full history is; `larecipe:optimize` does not run it.

<Artisan command="larecipe:provenance" />

### larecipe:references:snapshot

Writes what live references can read to `bootstrap/cache/larecipe/references.json`, for CI that has no database. Resolvers use it only when the app cannot answer.

<Artisan command="larecipe:references:snapshot" />

### larecipe:export

Writes public doc sets as a static site that works offline, search included. Anything that needs the running app is left out and listed.

<Artisan command="larecipe:export" />

### larecipe:share

Prints a temporary signed link that lets anyone read one page, private or not.

<Artisan command="larecipe:share" />

## Coming later

These commands are not in this release. Do not script against them yet.

| Command | Status | What it will do |
| --- | --- | --- |
| `larecipe:ui:eject` | Coming soon | Copy the reader UI's source, or one component of it, into your app. |
| `larecipe:ui:diff` | Coming soon | Show upstream UI changes since an eject. |

There is no `larecipe:publish`. To let your web server serve the prebuilt UI files, publish them with Laravel's own command (see [caching](../operate/caching.md)):

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```
