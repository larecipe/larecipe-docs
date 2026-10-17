---
title: "larecipe:sync (extra power)"
description: Run a docs agent inside your app on your own AI provider key, on demand, to update the pages a change affected, audit a whole doc set, or edit a production database set outside a pull request.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Enums/Channel.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/WritePageAction.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/composer.json
# TODO(verify): php artisan larecipe:sync exists and is registered as a console command.
# TODO(verify): the command needs laravel/ai and fails with a clear message when it is not installed.
# TODO(verify): the agent class is LaRecipe\Domains\Sync\Laravel\DocsMaintainer, a laravel/ai agent.
# TODO(verify): it reads ai.sync.provider, ai.sync.model, ai.sync.max_pages and ai.sync.max_steps from config/larecipe.php.
# TODO(verify): its tools are search_docs, read_page, list_pages, affected_pages, read_code, git_diff, read-only live reference introspection (routes, commands, model schemas, events) and the author tools.
# TODO(verify): read_code and git_diff are read-only and restricted to the repository; no tool returns configuration or environment values.
# TODO(verify): the author tools it uses go through WritePageAction and its siblings, so validation and the review mode apply.
# TODO(verify): runs are ordinary queued jobs, visible in Horizon or Nightwatch, testable with fakes.
# TODO(verify): with no input it updates the pages the current branch affects, compared with the default branch.
# TODO(verify): --base and --head set the diff range.
# TODO(verify): --page=path (repeatable) names the pages to update.
# TODO(verify): --audit reviews the whole set.
# TODO(verify): on a repository set it edits files in the working tree and leaves them uncommitted.
# TODO(verify): on a database set it writes the branch's docs migration in database/migrations/docs/.
# TODO(verify): --commit commits the docs changes to the current branch.
# TODO(verify): --remote=URL writes to a production database set through its authenticated remote author tools, as drafts under review required and published revisions under review auto.
# TODO(verify): --dry-run only reports what it would change and writes nothing.
# TODO(verify): --max-pages and --max-steps override ai.sync.max_pages and ai.sync.max_steps for one run.
# TODO(verify): --provider and --model override ai.sync.provider and ai.sync.model for one run.
---

# larecipe:sync (extra power)

`php artisan larecipe:sync` runs a docs agent inside your booted Laravel app, on your own AI provider key, when you ask for it. It does the same job your coding agent does before a push: it finds the pages a change affected and updates them. You reach for it when there is no coding agent in the loop, or when the job is bigger than one change.

It is extra power, not the default. The default writer is the coding agent you already run, with the full context of the change it made, on the subscription you already pay for. See [how it works](how-it-works.md).

## When to use it

- **An audit of a whole doc set.** `--audit` reviews every page, not only the ones a change touched.
- **A developer without a local coding agent.** The agent drafts the docs update for the branch they are on.
- **An edit to a production database set outside a pull request.** `--remote` writes straight to the live set, under its [review mode](review-modes.md).

## On demand only, never in CI

`larecipe:sync` runs when a person runs it: on a developer's machine, or as a queued job you dispatch. It is never a CI default. The [CI check](ci-check.md) LaRecipe scaffolds runs no AI agent, pushes nothing, commits nothing and needs no secrets, and it never calls `larecipe:sync`. The docs change belongs in the branch, written while the change is fresh, where a person can see and steer it.

Whatever it writes still follows the Same-PR rule: Markdown files for a repository set, a docs migration for a database set, in the branch with the code.

## What you need

`larecipe:sync` is built on `laravel/ai` and runs on your provider key. There is no LaRecipe service in between and no credit meter: a run costs the tokens your provider charges you.

```bash
composer require laravel/ai
```

Configure your provider key the way `laravel/ai` expects, then pick the provider and model in `config/larecipe.php`:

```php
// config/larecipe.php
'ai' => [
    'sync' => [
        'provider' => env('LARECIPE_AI_PROVIDER'),
        'model' => env('LARECIPE_AI_MODEL'),
        'max_pages' => 20,
        'max_steps' => 30,
    ],
],
```

`ai.sync.provider` and `ai.sync.model` read the same environment variables as [Ask AI](../agents/ask-ai.md), so one provider serves both unless you set them apart. `max_pages` caps how many pages one run may change, and `max_steps` caps how many tool calls the agent may make.

## The agent

The agent is `DocsMaintainer`, a `laravel/ai` agent. Because it runs inside your booted app, it writes from what the app actually does, not only from the text in the repository. Its tools:

| Tool | What it does |
| --- | --- |
| `search_docs`, `read_page`, `list_pages` | Search and read the docs, as any MCP client would. |
| `affected_pages` | The same check as `larecipe:affected`: the pages a change made stale. |
| `read_code`, `git_diff` | Read files and diffs in the repository, read-only. |
| Live reference introspection | Routes, artisan commands, model schemas and events, the way [live references](../writing/live-references.md) read them. Never configuration or environment values. |
| The author tools | `write_page` and its siblings, the same tools your coding agent uses, so validation, docs migrations and the review mode apply exactly as they do to every other writer. |

Runs are ordinary queued jobs: you can watch them in Horizon or Nightwatch, and test them with fakes like any other `laravel/ai` agent.

## Tell it what to work on

With no options, it updates the pages your branch affects, compared with the default branch:

```bash
php artisan larecipe:sync
```

Give it a range with `--base` and `--head`, the same way you would with `larecipe:affected`:

```bash
php artisan larecipe:sync --base=origin/main --head=HEAD
```

Name the pages yourself with `--page`, once per page:

```bash
php artisan larecipe:sync --page=billing/refunds --page=billing/webhooks
```

Or review the whole set with `--audit`:

```bash
php artisan larecipe:sync --audit
```

## Where its changes go

| Your set | What it writes |
| --- | --- |
| Repository | Edits to the Markdown files in your working tree, uncommitted, for you to review with `git diff`. |
| Database | The branch's docs migration in `database/migrations/docs/`, for you to review and commit with the code. |
| Database, with `--remote` | Changes to the production set, through its authenticated remote author tools. Drafts under `review: required`, published revisions under `review: auto`. |

Add `--commit` to commit the docs changes to the current branch for you:

```bash
php artisan larecipe:sync --commit
```

`--remote` works outside any pull request, so the set's review mode decides what goes live. The production set must have its web author tools turned on for the caller (see [review modes](review-modes.md#outside-a-pull-request-drafts-or-published-revisions)):

```bash
php artisan larecipe:sync --remote=https://acme.dev/handbook/mcp
```

## Budgets and dry runs

Every run is bounded. `ai.sync.max_pages` and `ai.sync.max_steps` set the defaults, and you can change them for one run:

```bash
php artisan larecipe:sync --audit --max-pages=5 --max-steps=40
```

`--provider` and `--model` pick another provider or model for one run, the same way.

`--dry-run` reports what the agent would change and writes nothing. Start an audit with it:

```bash
php artisan larecipe:sync --audit --dry-run
```

## Next

- [How it works](how-it-works.md): the layers `larecipe:sync` sits beside.
- [Review modes](review-modes.md): drafts and published revisions for changes outside a pull request.
- [Docs migrations](../sources/docs-migrations.md): how a database set's changes travel in the pull request.
