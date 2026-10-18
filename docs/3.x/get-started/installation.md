---
title: Installation
description: Add LaRecipe to a Laravel app with two commands, and get a working docs site plus the agent setup that keeps it current.
sources:
  - vendor/larecipe/larecipe/composer.json
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/**
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/IntroductionPage.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/ProjectFacts.php
  - vendor/larecipe/larecipe/stubs/docs-migration.stub
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallSkillsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/LinkClaudeMdAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteInstructionsBlockAction.php
---

# Installation

You need PHP 8.3 or newer, Laravel 12.62 or newer (or Laravel 13), and `git` on developer machines and in CI. Production servers do not need `git`.

<Steps>

<Step title="Require the package">

```bash
composer require larecipe/larecipe
```

While LaRecipe 3 is in pre-release, ask for it by name: `composer require larecipe/larecipe:^3.0@alpha`.

</Step>

<Step title="Run the installer">

```bash
php artisan larecipe:install
```

It asks a few questions. The defaults suit most apps, so you can press Enter through them.

</Step>

<Step title="Open your docs">

Visit `/docs`. The first page is a short Introduction written from your app: its name, its Laravel version, its first-party packages and the opening of your README.

</Step>

</Steps>

That is a working site. The installer also set up your coding agents, so the docs stay current from the first push. Commit what it wrote, except the file in `bootstrap/cache`.

Next, let your coding agent write real starter pages from your code: [Your first docs](your-first-docs.md).

## What the installer asks

Every question has a flag, so you can answer up front or run the installer in a script.

| Question | Flag | Default |
| --- | --- | --- |
| Where should the docs live? | `--driver=repository` or `--driver=database` | `repository` |
| The docs folder of a repository set | `--path=docs` | `docs` |
| Which coding agents to set up | `--agents=claude,codex,cursor,gemini,copilot`, or `--agents=none` | The agents it detects |
| Hold an agent's push once when it leaves pages stale | `--no-hooks` to skip | Yes |
| Also check pushes people make without an agent | `--git-hook=warn` or `--git-hook=strict` | No |
| Add the optional no-AI docs check for pull requests | `--ci=fail` or `--ci=comment` | No |
| Review mode for docs changes | `--review=required` or `--review=auto` | `required` |
| Refresh the agent setup after every `composer update` | `--composer-script` | Asked |
| Add `@AGENTS.md` to an existing `CLAUDE.md` | `--claude-md` | Asked |
| Generate starter docs now | `--onboard=product,api,package,internal` and `--onboard-agent=claude` | Asked when an agent is installed |

`--dry-run` shows every file the installer would change without writing anything, and `--no-index` leaves the docs index out of `AGENTS.md`. Run the installer again at any time: it merges into files you already have and never overwrites them.

## What it writes

The docs folder with a manifest and an Introduction page, and the setup your agents read: a LaRecipe block in `AGENTS.md`, four skills, the local MCP server in each agent's config, and the pre-push hook for Claude Code and Codex. A git hook and a CI workflow are written only when you ask for them.

<Accordion title="Every file the installer writes">

| File | What it is |
| --- | --- |
| `docs/larecipe.json` | The doc set's [manifest](../reference/manifest.md): the name from `APP_NAME` and the `$schema` pointer. |
| `docs/index.md` | The Introduction page, with `sources:` pointing at `README.md` and `composer.json`, so it shows up as stale when they change. Skipped when `docs/index.md`, `docs/README.md` or `docs/introduction.md` exists. |
| `database/migrations/docs/{timestamp}_docs_introduction.php` | With `--driver=database` only, in place of the two files above: a docs migration that gives the set its name and the same Introduction page. |
| `config/larecipe.php` | Published only when you pick something other than the defaults (the database driver, another folder, or `auto` review). |
| `AGENTS.md` | A marked LaRecipe block: what to do with the docs before every push, and a compact index of your pages. See [agent instructions](../keeping-current/agent-instructions.md). |
| `CLAUDE.md` | `@AGENTS.md`, added only with your consent, so Claude Code reads the same instructions. |
| `.claude/skills/`, `.agents/skills/` | The four LaRecipe skills. See [skills](../keeping-current/skills.md). |
| `.mcp.json`, `.codex/config.toml`, `.cursor/mcp.json`, `.gemini/settings.json`, `.vscode/mcp.json` | The local `larecipe` MCP server (`php artisan larecipe:mcp`), in the file each detected agent reads. |
| `.claude/settings.json`, `.codex/hooks.json` | The pre-push and SessionStart hooks, for Claude Code and Codex. See [the pre-push hook](../keeping-current/pre-push-hook.md). |
| `.git/hooks/pre-push` | Only with `--git-hook`. It respects `core.hooksPath`. |
| `.github/workflows/larecipe-docs-check.yml` | Only with `--ci`. See [the optional CI check](../keeping-current/ci-check.md). |
| `composer.json` | `@php artisan larecipe:agents --ansi` in `post-update-cmd`, only with your consent. |
| `bootstrap/cache/larecipe/hooks.php` | The compiled push patterns the hook reads, so ordinary shell commands never boot Laravel. Not committed. |

The installer merges into files you already have. Other MCP servers and hooks stay where they are, an existing `larecipe` entry is left alone, and a file it cannot parse is skipped with a note.

</Accordion>

## Details

<AccordionGroup>

<Accordion title="Run it without questions">

In a script, in CI or in a dev container, pass `--no-interaction` with the answers you want:

```bash
php artisan larecipe:install --no-interaction \
    --driver=repository \
    --agents=claude,codex \
    --review=required \
    --claude-md \
    --composer-script
```

Without a terminal to ask, the installer uses the defaults in the table above: the repository driver, the agents it detects, the agent hooks, no git hook, no CI check, `required` review, and no change to `composer.json` or `CLAUDE.md` unless you pass the flag. Onboarding never runs without `--onboard`.

</Accordion>

<Accordion title="How it finds your agents">

The installer detects an agent in two ways: its CLI on your `PATH` (`claude`, `codex`, `cursor-agent`, `gemini`, `copilot`), or its files in the project (`.claude`, `CLAUDE.md`, `.codex`, `.cursor`, `.gemini`, `GEMINI.md`, `.github/copilot-instructions.md`). It only writes config for agents you use.

</Accordion>

<Accordion title="Where the docs folder may live">

The docs folder must be inside your app. The installer refuses a `--path` that is the app's root folder itself, or a folder outside it.

</Accordion>

<Accordion title="With Laravel Boost">

When `laravel/boost` is installed, Boost ships LaRecipe's instructions and skills, so LaRecipe writes no `AGENTS.md` block and no skill copies of its own. Run `php artisan boost:install` (or `boost:update`) and select `larecipe/larecipe`. `larecipe:install` still writes the MCP config and the hooks. See [agent instructions](../keeping-current/agent-instructions.md#with-laravel-boost).

</Accordion>

<Accordion title="With the database driver">

`--driver=database` keeps the docs in your database, with an admin UI and docs migrations for changes made in pull requests. The installer writes no `docs/` folder in this case. It writes a starter [docs migration](../sources/docs-migrations.md) instead, `database/migrations/docs/{timestamp}_docs_introduction.php`, with the set's name and the same Introduction page. Running the installer again never writes a second one. Then:

1. Run `php artisan migrate`. It creates LaRecipe's tables, which load from the package (publish them with `php artisan vendor:publish --tag=larecipe-migrations` to customize them), and applies the starter migration, so `/docs` opens on the Introduction right away.
2. Use a persistent cache store. A database set always runs behind a cache, and the installer warns when the store is `array` or `null`. Set `LARECIPE_CACHE_STORE` to `redis`, `database` or `file`.

Read [the database source](../sources/database.md) before you choose it. Most teams start with the repository.

</Accordion>

<Accordion title="Codex and Ask AI">

Two steps the installer prints when they apply:

- **Codex**: trust the project in `~/.codex/config.toml` and approve the LaRecipe hooks with `/hooks`, so Codex loads them.
- **Ask AI** needs `laravel/ai` and a provider key of your own: `composer require laravel/ai`. Everything else works without it. See [Ask AI](../agents/ask-ai.md).

</Accordion>

<Accordion title="Every option of larecipe:install">

Read live from this app's command registry:

<Artisan command="larecipe:install" />

</Accordion>

</AccordionGroup>

## Next

- [Your first docs](your-first-docs.md): let your coding agent write starter pages from your app.
- [Configuration basics](configuration.md): the few settings most apps change.
- [Project structure](project-structure.md): what goes where in `docs/`.
