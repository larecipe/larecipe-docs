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

Every `larecipe:*` command, grouped by job. Each usage block and option table is read from the running app by an [`<Artisan>` live reference](../writing/live-references.md), so it matches `php artisan help`.

Commands that take a docs version use `--docs-version`: Symfony reserves `--version` for the application.

## Set up and write

### larecipe:install

Sets up a doc set with a first page, and the `AGENTS.md` instructions, skills, MCP config and hooks for the agents it finds. `--dry-run` previews every step, and every prompt has a flag. See [installation](../get-started/installation.md).

<Artisan command="larecipe:install" />

### larecipe:onboard

Writes starter docs with your own coding agent and the `larecipe-write-docs` skill. It shows the agent command and asks before it runs. Nothing is committed. See [your first docs](../get-started/your-first-docs.md).

<Artisan command="larecipe:onboard" />

### larecipe:upgrade

Moves a LaRecipe 2 app to LaRecipe 3: config, navigation and content. Without `--apply` it only writes the report. See [upgrade from v2](../upgrade.md).

<Artisan command="larecipe:upgrade" />

### larecipe:make:page

Creates a page with `title`, `description` and `sources` in its frontmatter. Repository sets only. It does not edit `navigation.json`; it tells you when the version has one.

<Artisan command="larecipe:make:page" />

### larecipe:make:version

Adds a version to a repository set by copying an existing one. On a set without versions, `--current-as` names the version its current pages belong to. See [versions](../writing/versions.md).

<Artisan command="larecipe:make:version" />

### larecipe:navigation:freeze

Writes the navigation LaRecipe derives from folders and frontmatter to `navigation.json`, so you can edit it by hand. Repository sets only.

<Artisan command="larecipe:navigation:freeze" />

## Keep current

### larecipe:affected

Lists the pages whose `sources:` match the files a change touched, and which of them it left stale. The pre-push hook and the CI check run it. See [how it works](../keeping-current/how-it-works.md) for ranges and formats.

<Artisan command="larecipe:affected" />

### larecipe:sources:suggest

Proposes `sources:` for pages that declare none, from the classes, commands and routes they mention. `--apply` writes them into those pages.

<Artisan command="larecipe:sources:suggest" />

### larecipe:validate

Checks links, navigation, schemas, components, snippets, live references, budgets and skills. Exits non-zero on an error; `--json` prints the report agents read. See [validation](../operate/validation.md).

<Artisan command="larecipe:validate" />

### larecipe:hook

Called by the agent hooks (`pre-push`, `session-start`) and the git hook (`git-pre-push`). You do not run it by hand.

<Artisan command="larecipe:hook" />

### larecipe:hooks:git

Installs or removes the git pre-push check, for people who push without an agent. It warns by default; `--strict` holds the push. It never replaces an existing hook.

<Artisan command="larecipe:hooks:git" />

### larecipe:sync

Runs a docs agent inside your app on your own `laravel/ai` provider key: for the pages a change left stale, the pages you name, or the whole set with `--audit`. It shows its plan and asks first. See [larecipe:sync](../keeping-current/sync.md).

<Artisan command="larecipe:sync" />

## Agents

### larecipe:agents

Regenerates the `AGENTS.md` block, the skills and the compiled hook patterns. Run it after updating LaRecipe or changing its config.

<Artisan command="larecipe:agents" />

### larecipe:agents-md

Writes the docs index into the LaRecipe block of `AGENTS.md`. Run it after adding, removing or renaming pages.

<Artisan command="larecipe:agents-md" />

### larecipe:mcp

Starts the local MCP server over stdio. Your agent's MCP config runs it. See [MCP tools](mcp-tools.md).

<Artisan command="larecipe:mcp" />

### larecipe:doctor

Grades what agents get from each doc set, with a fix for each finding. Exits non-zero when a check fails. See [validation and doctor](../operate/validation.md).

<Artisan command="larecipe:doctor" />

## Sources

### larecipe:source:export

Writes a doc set as a bundle of canonical files, to a folder or a `.tar.gz`, for `source:import` and `pull`.

<Artisan command="larecipe:source:export" />

### larecipe:source:import

Loads a bundle into a set's configured source, validated first. `--verify` compares every content hash afterwards.

<Artisan command="larecipe:source:import" />

### larecipe:source:move

Copies a set between the repository and the database, and prints the config change that switches serving. It never edits `config/larecipe.php`. See [switching sources](../sources/switching.md).

<Artisan command="larecipe:source:move" />

### larecipe:make:docs-migration

Scaffolds a docs migration by hand. Each `--page` is filled with its current content and content hash. See [docs migrations](../sources/docs-migrations.md).

<Artisan command="larecipe:make:docs-migration" />

### larecipe:pull

Refreshes a local or CI database set from a bundle made on production with `larecipe:source:export`.

<Artisan command="larecipe:pull" />

### larecipe:cache:warm

Warms the cache of database sets. Run it in your deploy after `php artisan migrate`.

<Artisan command="larecipe:cache:warm" />

## Operate

### larecipe:optimize

Renders every page of repository sets into the cache, with page lists, navigation and search records. `php artisan optimize` runs it.

<Artisan command="larecipe:optimize" />

### larecipe:clear

Forgets what `larecipe:optimize` compiled, and deletes `bootstrap/cache/larecipe/optimized.php`. The provenance index, the reference snapshot and the hook patterns stay. `php artisan optimize:clear` runs it.

<Artisan command="larecipe:clear" />

### larecipe:provenance

Compiles each page's last change from git, for repository sets. Run it where the full history is; `larecipe:optimize` does not run it. See [the repository source](../sources/repository.md).

<Artisan command="larecipe:provenance" />

### larecipe:references:snapshot

Writes what live references can read to `bootstrap/cache/larecipe/references.json`, for CI without a database. Used only when the app cannot answer.

<Artisan command="larecipe:references:snapshot" />

### larecipe:export

Writes public doc sets as a static site, search included. What needs the running app is left out and listed. See [static export](../operate/static-export.md).

<Artisan command="larecipe:export" />

### larecipe:share

Prints a temporary signed link that lets anyone read one page, private or not. See [gate blocks and private docs](../writing/gate-blocks.md).

<Artisan command="larecipe:share" />

## The UI files

There is no `larecipe:publish`. To let your web server serve the prebuilt UI files, use Laravel's own command (see [deployment](../operate/deployment.md)):

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```
