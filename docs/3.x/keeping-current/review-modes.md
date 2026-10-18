---
title: Review modes
description: Choose whether your agent asks before it commits docs changes (required, the default) or updates and pushes them on its own (auto), and what each mode means for changes made outside a pull request.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/Enums/ReviewMode.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/InstructionsRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/HookMessages.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Enums/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/WritePageAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/PublishDraftAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/RevertPageAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Models/Revision.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Review modes

The review mode decides whether your agent asks before it commits docs changes:

- **`required`** (the default): the agent shows you the docs diff and waits for your approval before it commits.
- **`auto`**: the agent updates the docs, commits and pushes without asking.

Set it with `LARECIPE_REVIEW=auto` in `.env`, or pick it in the installer:

```bash
php artisan larecipe:install --review=auto
```

Then run `php artisan larecipe:agents`, so the `AGENTS.md` block says so (with Laravel Boost, `php artisan boost:update`). The pre-push hook reads the config on every push and needs nothing.

Either way, the docs change still rides in your pull request, and every change is revisioned, attributed and one step from a revert.

## Where you see it

| | `required` (default) | `auto` |
| --- | --- | --- |
| The [`AGENTS.md` block](agent-instructions.md) | "Review mode is `required`: show the user the docs diff and wait for approval before committing it." | "Review mode is `auto`: update the docs and push without asking." |
| The [pre-push hook](pre-push-hook.md)'s message | "...run php artisan larecipe:validate, show the user the docs diff and wait for approval, then commit and push again." | "...run php artisan larecipe:validate, then commit and push again." |
| The [skills](skills.md) | Follow the mode in `AGENTS.md`: show the diff and wait. | Follow the mode in `AGENTS.md`: commit and push. |

The setting is `review` in `config/larecipe.php`. A set can choose its own mode with `'review' => 'auto'` in its entry under `sets`. Write the value in lowercase: anything that is not `auto` counts as `required`.

With several doc sets, the `AGENTS.md` block says `auto` only when every set is `auto`, and the hook's message asks for approval when any stale page belongs to a `required` set. When in doubt, the agent asks.

## In a pull request, the pull request is the review

A docs change that rides in a pull request is reviewed there, with the code, in both modes. `auto` never skips the pull request; it skips the question in your session.

- **A repository set:** the changes are Markdown files in the branch. Merging them is publishing them.
- **A database set:** the changes travel as the branch's [docs migration](../sources/docs-migrations.md). When `php artisan migrate` applies it on deploy, its changes publish. If a page changed in production after the migration was written, the migration never overwrites it: its version waits as a conflict draft for a person to resolve.

## Outside a pull request: drafts or published revisions

Only a database set takes changes from outside a pull request, for example from an agent on the web MCP server or [`larecipe:sync`](sync.md) on production. There the mode decides what goes live: under `required` an agent's change waits as a draft for a person to publish, under `auto` it publishes at once. Repository sets never take such changes: their changes belong in git.

<Accordion title="Who publishes what, and how to turn on the web author tools">

| Who makes the change | `required` | `auto` |
| --- | --- | --- |
| An agent on the web MCP server's author tools | A draft | Published |
| `larecipe:sync` writing straight to a database set | A draft | Published |
| A person in the [admin UI](../sources/database.md#the-admin-ui) | Published, or a draft if they choose | Same |
| A docs migration applied by `php artisan migrate` | Published | Same |
| Your local coding session, through the local MCP server | Applied to your local database and written into the branch's docs migration | Same |

The web MCP server refuses author tools for repository sets. On a database set they are off by default: turn them on with `mcp.web.author_tools`, and only a signed-in caller that the `manageLarecipe` gate allows can use them:

```php
// config/larecipe.php
'sets' => [
    'handbook' => [
        // route, source and access as usual
        'mcp' => ['web' => ['author_tools' => true]],
    ],
],
```

Each author tool says what happened in its `outcome`: `published`, `drafted`, `conflicted` (the page changed since the agent read it), `unchanged` or `rejected` (validation errors; nothing was written). It returns the revision and the validation report with it. See [the MCP server](../agents/mcp.md).

Drafts wait in a review queue until a person publishes or rejects them. Publishing validates the draft again first, because the rules may have changed since it was written. You work through the queue in the admin UI, at [the pass](../sources/database.md#the-pass-the-review-queue), where a reviewer can also edit a draft before publishing it. Under `auto`, the pass lists what agents published lately, each one click from a revert.

</Accordion>

## Details

<Accordion title="Revisioned, attributed and revertible, in every mode">

- **Revisioned.** In a repository set, every change is a commit. In a database set, every change is a row in `larecipe_revisions` holding the full state it leads to, never edited afterwards except for its review status.
- **Attributed.** In a repository set, LaRecipe reads the last change from git: see [provenance](../sources/repository.md). In a database set, each revision records whether a person or an agent made it (an agent is named after its MCP client), the channel it came through (`pull_request`, `admin_ui`, `remote_agent`, `sync` or `migration`), the docs migration and a one-line summary. Pages show this as provenance.
- **Revertible.** In a repository set, revert the commit. In a database set, a revert restores an earlier revision as a new published revision and marks the one it replaces as reverted, so the history shows exactly what happened and the revert can itself be reverted.

</Accordion>

## Next

- [The pre-push hook](pre-push-hook.md): where the mode shows up at push time.
- [The database source](../sources/database.md): go deeper with drafts, revisions and the admin UI.
