---
title: "larecipe:sync"
description: Run a docs agent inside your app on your own AI provider key, on demand, to update the pages a change affected, audit a whole doc set, or edit a production database set outside a pull request.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Enums/Channel.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/WritePageAction.php
  - vendor/larecipe/larecipe/src/Support/SecretFiles.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/composer.json
---

# larecipe:sync

`php artisan larecipe:sync` runs a docs agent inside your Laravel app, on your own AI provider key, when you ask for it. It does the job your coding agent does before a push: it finds the pages a change affected and updates them. Reach for it when:

- **you want an audit** of a whole doc set, not only the pages one change touched;
- **a developer has no local coding agent**, and wants a draft of the docs update for their branch;
- **you edit a production database set** outside a pull request.

It is not the default writer. Your coding agent is, because it has the full context of the change it made a moment ago. See [how it works](how-it-works.md).

## Run it

It is built on `laravel/ai`. Install that and add your provider key to `.env` the way `laravel/ai` expects:

```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
```

Then run it on your branch. It updates the pages your branch affects compared with the default branch, uncommitted work included:

```bash
php artisan larecipe:sync
```

It prints its plan first (the set, the task, the change it reads, the provider and model, the budget, where its writes land and the pages it will work on) and asks before it spends a token. On a repository set, the result is edited Markdown files in your working tree, uncommitted, for you to review with `git diff`.

There is no LaRecipe service in between and no credit meter: a run costs the tokens your provider charges you.

## Tell it what to work on

```bash
php artisan larecipe:sync --base=origin/main --head=HEAD
php artisan larecipe:sync --page=billing/refunds --page=billing/webhooks
php artisan larecipe:sync --audit --dry-run
```

- `--base` and `--head` give it a range, the same way as [`larecipe:affected`](how-it-works.md#larecipeaffected).
- `--page` names the pages yourself, once per page, as a page path or its file.
- `--audit` reviews the whole version: the pages whose sources changed after them first, then the ones without `sources:`, then the rest.
- `--dry-run` reports what the agent would change and writes nothing. Start an audit with it.
- `--set`, `--docs-version` and `--locale` pick another doc set, version or language. Without them, it works on the default set, version and language.
- `--yes` skips the question. Without a terminal to ask, it runs only with `--yes`.

## On demand only, never in CI

`larecipe:sync` runs when a person runs it: on a developer's machine, or as a queued job with `--queue`. It is never a CI default. The [CI check](ci-check.md) LaRecipe scaffolds runs no AI agent, pushes nothing, commits nothing, needs no secrets, and never calls `larecipe:sync`. Inside a repository, whatever it writes still rides in the branch with the code.

## Details

<AccordionGroup>

<Accordion title="Where its changes go, and --commit">

| Your set | What it writes |
| --- | --- |
| Repository | Edits to the Markdown files in your working tree, uncommitted, for you to review with `git diff`. |
| Database, inside a git repository | The change is applied to your local database and written into the branch's docs migration in `database/migrations/docs/`, for you to review and commit with the code. |
| Database, with no git repository around it (a queue worker on production) | Changes to the set itself, under its [review mode](review-modes.md): drafts under `review: required`, published revisions under `review: auto`. |
| Database, with `--remote` | Changes to the production set, through its authenticated remote author tools. Drafts under `review: required`, published revisions under `review: auto`. |

`--commit` commits the docs changes to the current branch for you:

```bash
php artisan larecipe:sync --commit
```

It commits only the files the agent wrote, with a message that lists each page and names the agent in a `Co-Authored-By` trailer. You still push, so the docs travel with the code. It refuses to start when those docs files already have uncommitted changes, it cannot be combined with `--dry-run`, and it only works where the change rides in the branch.

</Accordion>

<Accordion title="Edit a production database set with --remote">

```bash
php artisan larecipe:sync --remote=https://acme.dev/handbook/mcp
```

`--remote` works outside any pull request, so the set's review mode decides what goes live. It needs `ai.sync.remote_token` (`LARECIPE_SYNC_TOKEN`): a token the remote app accepts on its MCP endpoint, such as a Sanctum token, of a user its `manageLarecipe` gate allows. The production set must have its web author tools turned on (see [review modes](review-modes.md#outside-a-pull-request-drafts-or-published-revisions)). A repository set refuses `--remote`, because its changes belong in git.

</Accordion>

<Accordion title="Settings">

Pick the provider and model in `config/larecipe.php`:

```php
// config/larecipe.php
'ai' => [
    'sync' => [
        'provider' => env('LARECIPE_AI_PROVIDER'),
        'model' => env('LARECIPE_AI_MODEL'),
        'max_pages' => 20,
        'max_steps' => 30,
        'timeout' => 120,
        'remote_token' => env('LARECIPE_SYNC_TOKEN'),
        'deny' => [],
        'queue' => env('LARECIPE_SYNC_QUEUE'),
    ],
],
```

| Key | Controls |
| --- | --- |
| `provider` | A provider name from `config/ai.php`. Empty uses `laravel/ai`'s default provider. |
| `model` | The model. Empty uses the provider's default model. |
| `max_pages` | How many pages one run may change. |
| `max_steps` | How many steps the agent may take in one run. |
| `timeout` | Seconds per provider request. |
| `remote_token` | The token a `--remote` run sends to a production set's MCP endpoint. |
| `deny` | Paths or globs the agent may never read, on top of the built-in list. |
| `queue` | The queue for `--queue` runs. Empty uses your default queue. |

`ai.sync.provider` and `ai.sync.model` read the same environment variables as [Ask AI](../agents/ask-ai.md), so one provider serves both unless you set them apart.

Before it plans anything, the command checks that `laravel/ai` is installed, that the provider exists in `config/ai.php` and has a key, and, for `--remote`, that `remote_token` is set. When something is missing, it stops and says exactly what to install or set. Keys are only checked for presence; their values are never printed.

</Accordion>

<Accordion title="Budgets, providers and queued runs">

Every run is bounded. `ai.sync.max_pages` and `ai.sync.max_steps` set the defaults, and you can change them for one run. Pages over the budget are listed in the plan and in the report as not done. The page budget limits what an audit may change, not what it may read.

```bash
php artisan larecipe:sync --audit --max-pages=5 --max-steps=40
```

`--provider` and `--model` pick another provider or model for one run, the same way.

By default the run happens in your terminal, and it ends with what changed, the agent's summary and anything it could not do. With `--queue`, the run is an ordinary queued job instead, with one attempt: you can follow it in Horizon or Nightwatch, and its report goes to your log. In tests, fake it like any other `laravel/ai` agent, with `DocsMaintainer::fake()`.

</Accordion>

<Accordion title="The agent and its tools">

The agent is `DocsMaintainer`, a `laravel/ai` agent. Because it runs inside your booted app, it writes from what the app actually does, not only from the text in the repository. It follows the same workflow your coding agent does: the `larecipe-update-docs` skill, or `larecipe-audit-docs` for an audit.

| Tool | What it does |
| --- | --- |
| `search_docs`, `read_page`, `list_pages` | Search and read the docs, as any MCP client would. |
| `read_page_source` | A page's frontmatter and Markdown as written, with its content hash, so the agent edits the real source and never overwrites a newer edit. |
| `affected_pages` | The same check as `larecipe:affected`: the pages a change made stale. |
| `read_code`, `git_diff` | Read files and diffs in the project, read-only. |
| `app_reference` | Routes, artisan commands, model schemas and events, the way [live references](../writing/live-references.md) read them. Never configuration or environment values. |
| `validate_docs` | The `larecipe:validate` report, to fix what the agent wrote. |
| The author tools | `write_page`, `move_page`, `delete_page`, `update_navigation` and `update_manifest`, the same tools your coding agent uses, so validation, docs migrations and the review mode apply exactly as they do to every other writer. |

Every tool is bound to the run's doc set, version and language. `read_code` and `git_diff` never leave the project, and they never open the files that may hold configuration, environment values or secrets: the same list snippet includes refuse, described in [code blocks](../writing/code-blocks.md#what-can-be-included). `git_diff` names such a file without showing it. `ai.sync.deny` adds paths to the list; nothing can take paths off it.

The page budget and dry runs are enforced in code, in front of the author tools, so a confused model cannot write past them.

</Accordion>

</AccordionGroup>

## Next

- [Review modes](review-modes.md): drafts and published revisions for changes outside a pull request.
- [Docs migrations](../sources/docs-migrations.md): how a database set's changes travel in the pull request.
- [Artisan commands](../reference/commands.md#larecipesync): every option, read from the running app.
