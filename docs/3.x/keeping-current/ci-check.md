---
title: The optional CI check
description: An optional GitHub Actions check that validates the docs on every pull request and fails or comments when the change leaves pages stale, without running an AI agent or writing anything.
sources:
  - vendor/larecipe/larecipe/stubs/larecipe-docs-check.yml
  - vendor/larecipe/larecipe/stubs/ci/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteCiCheckAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Enums/CiMode.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AffectedCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/AffectedFormatter.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/AppFacts.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/PullCommand.php
---

# The optional CI check

The CI check is an optional safety net for pushes made without a coding agent, or by an agent without hooks. On every pull request it validates the docs and lists the pages whose code changed while the page did not. Add it with the installer, in one of two modes:

```bash
php artisan larecipe:install --ci=fail
php artisan larecipe:install --ci=comment
```

- **`fail`** makes a failing check, so you can require it before merging. Add the `skip-docs` label to a pull request that needs no docs change, and the check passes without a new push.
- **`comment`** posts the list of stale pages on the pull request and never fails because of them.

Either way, a validation error fails the check. It only checks: it runs no AI agent, never writes, commits or pushes, and needs no secrets for docs in the repository, so it is safe on pull requests from forks. Your coding agent updates the docs before it pushes; this confirms that it did.

## What it runs

The installer writes `.github/workflows/larecipe-docs-check.yml`. Its two LaRecipe steps are:

```bash
php artisan larecipe:validate --json
php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=github --fail-on-stale --no-drift
```

[`larecipe:validate`](../operate/validation.md) checks links, navigation, schemas, components, snippets and live references. [`larecipe:affected`](how-it-works.md#larecipeaffected) compares the pull request with its base branch and judges this change only: drift from before the pull request is left out (`--no-drift`).

When a workflow with that name already exists and differs from what LaRecipe would write, the installer leaves it alone: delete it and run the installer again to get a fresh one.

## Details

<AccordionGroup>

<Accordion title="The full workflow, step by step">

This is the workflow `--ci=fail` writes for a repository set in an app that requires PHP `^8.3`. The PHP version comes from the `php` constraint in your `composer.json` (8.3 at the lowest, 8.4 when there is none).

```yaml
# LaRecipe docs check (optional, no AI).
#
# Written by php artisan larecipe:install. On every pull request it validates
# the docs and lists pages whose code changed while the page did not. It never
# writes, commits or pushes, runs no AI agent and needs no secrets for docs in
# the repository, so it is safe on pull requests from forks. Your coding agent
# updates the docs before it pushes; this only checks that it did.
#
# Mode: fail. Add the skip-docs label to a pull request that needs no docs change.

name: LaRecipe docs check

on:
  pull_request:
    types: [opened, synchronize, reopened, labeled, unlabeled]

permissions:
  contents: read

concurrency:
  group: larecipe-docs-check-${{ github.event.pull_request.number }}
  cancel-in-progress: true

jobs:
  docs-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v5
        with:
          fetch-depth: 0

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - run: composer install --no-interaction --prefer-dist --no-progress

      - name: Prepare the app
        run: |
          if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi
          php artisan key:generate --force --no-interaction || true

      - name: Validate the docs
        run: php artisan larecipe:validate --json

      - name: Check the docs changed with the code
        if: ${{ !contains(github.event.pull_request.labels.*.name, 'skip-docs') }}
        run: php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=github --fail-on-stale --no-drift
```

1. **Checkout with full history.** `fetch-depth: 0` gives git the base branch, so `larecipe:affected` can see what the pull request changed.
2. **Install and prepare the app.** Composer, an `.env` from `.env.example`, an app key. LaRecipe's commands run inside your app, so the app must boot.
3. **Validate the docs.** A validation error fails the check in either mode.
4. **Check the docs changed with the code.** It compares the pull request with its base branch, `origin/${{ github.base_ref }}`.

The workflow runs again when a label is added or removed, so adding `skip-docs` turns a red check green without a new push.

</Accordion>

<Accordion title="Fail mode output">

`--format=github --fail-on-stale` turns each stale page into an error annotation on the page's file, writes a Markdown report to the job summary, and exits with `1`:

```text
::error file=docs/billing/webhooks.md,title=Stale docs page: docs/billing/webhooks.md::This pull request changes code this page documents (app/Webhooks/Handler.php) but not the page. Update it, or add the skip-docs label when no docs change is needed.
LaRecipe: 1 docs page needs an update for this change.
```

Make the check required in your branch protection rules, and every pull request either updates its docs, or carries the `skip-docs` label as a visible decision that it needs none.

</Accordion>

<Accordion title="Comment mode workflow and comment">

`--ci=comment` gives the workflow permission to comment, and nothing else:

```yaml
permissions:
  contents: read
  pull-requests: write   # to post the comment, nothing else
```

and replaces the last step with these two:

```yaml
      - name: List docs pages this pull request leaves stale
        id: affected
        run: php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=markdown --no-drift > larecipe-docs-check.md

      - name: Comment on the pull request
        # Pull requests from forks get a read-only token, so the comment is skipped there.
        if: ${{ github.event.pull_request.head.repo.full_name == github.repository }}
        continue-on-error: true
        env:
          GH_TOKEN: ${{ github.token }}
        run: |
          if [ "${{ steps.affected.outputs.stale }}" != "0" ]; then
            gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md --edit-last \
              || gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md
          else
            gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md --edit-last || true
          fi
```

- `--format=markdown` writes the comment and sets the `stale` step output to the number of stale pages.
- When pages are stale, the workflow edits its last comment, or posts a new one. When none are, it updates an earlier comment to say so, and posts nothing new.
- The comment uses the workflow's own `github.token`, not a secret you add. On pull requests from forks that token is read-only, so the comment step is skipped there.

The comment reads like this:

```markdown
### LaRecipe docs check

This pull request changes code that 1 docs page describes, and the page was not updated:

| Page | Sources | Changed files |
| --- | --- | --- |
| `docs/billing/webhooks.md` | `app/Webhooks/**` | `app/Webhooks/Handler.php` |

Update them in this pull request (the `larecipe-update-docs` skill does it in your agent session), or add the `skip-docs` label when no docs change is needed.
```

</Accordion>

<Accordion title="Database sets">

When the app has a [database set](../sources/database.md), the workflow gets one more step after the app is prepared. It applies the pull request's [docs migrations](../sources/docs-migrations.md) to a throwaway SQLite database, so a broken docs migration fails the check:

```yaml
      # Database doc sets (handbook): apply this pull request's docs migrations to a
      # throwaway database, so a broken docs migration fails the check.
      - name: Apply docs migrations
        run: |
          mkdir -p database && touch database/database.sqlite
          php artisan migrate --force --no-interaction

      # To compute stale pages against what is live, refresh the CI database from
      # production first with a read-only token (optional):
      # - run: php artisan larecipe:pull --set=handbook
```

To map changes to pages, the check needs the set's pages, with their `sources:`, in the CI database. Without them, `larecipe:affected` says the set has no pages in the local database, and only the validation and the docs migrations are checked. To load them, export the set on production and pull the bundle in CI before the check: `larecipe:pull` needs `--from`, the path to that bundle.

```bash
php artisan larecipe:source:export --set=handbook --to=storage/handbook.tar.gz
php artisan larecipe:pull --set=handbook --from=storage/handbook.tar.gz
```

The first command runs on production; the second in CI, once the bundle is there. How the bundle reaches CI is up to you, and that is the one place a database set's check needs access to something outside the repository.

</Accordion>

<Accordion title="Why it never writes">

The check exists to catch a missing docs change, not to make one:

- **No AI agent.** It runs `larecipe:validate` and `larecipe:affected`, which read git and the docs and give the same answer every time.
- **No writes.** `contents: read` is the only repository permission. It commits nothing and pushes nothing. In comment mode it may post one comment, nothing else.
- **No secrets.** A repository set needs nothing beyond the code, so the check runs on pull requests from forks too.
- **No follow-up docs pull requests.** A docs pull request opened after a merge reaches a reviewer who has moved on, and the docs stay wrong in production until someone merges it. LaRecipe does not scaffold one.

The docs change belongs in the same pull request as the code, written by the agent that made the change. See [how it works](how-it-works.md).

</Accordion>

</AccordionGroup>

## Next

- [The pre-push hook](pre-push-hook.md): catch stale pages before the push, not after.
- [Validation and doctor](../operate/validation.md): what `larecipe:validate` checks.
