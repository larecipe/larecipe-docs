---
title: Docs migrations
description: Ship content changes to a database doc set in the pull request, as migration files that php artisan migrate applies on deploy without ever overwriting a newer edit.
sources:
  - vendor/larecipe/larecipe/src/Migrations/DocsMigration.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ApplyDocsMigrationAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/RollbackDocsMigrationAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DocsMigrationWriter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DocsMigrationReader.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitBranch.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/DocsMigrationOperations.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/DocsMigrationRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/PageAddresses.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/Preconditions.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/NavigationPatcher.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/MakeDocsMigrationCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/PullCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/SourcesServiceProvider.php
  - vendor/larecipe/larecipe/stubs/docs-migration.stub
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Docs migrations

A [database doc set](database.md) keeps its content in your database, not in your repository, so a pull request cannot carry a docs change as Markdown files. A docs migration carries it instead: a standard Laravel migration file, reviewed in the pull request with the code, and applied by `php artisan migrate` on deploy. Each change records the content it was based on, so a docs migration never overwrites an edit someone made in production since.

You only need them for database sets. A [repository set](repository.md) ships its changes as Markdown files in the same pull request.

## What one looks like

This is the kind of file your coding agent writes when it documents a new feature on a database set:

```php
<?php

use LaRecipe\Migrations\DocsMigration;

return new class extends DocsMigration
{
    protected string $set = 'handbook';

    protected string $author = 'claude-code';

    protected string $ref = '';

    protected string $summary = 'Document CSV exports';

    public function up(): void
    {
        $this->createPage('3.x/billing/exports', frontmatter: [
            'title' => 'CSV exports',
            'description' => 'Export invoices as a CSV file from the billing page.',
            'sources' => ['app/Billing/Exports/**'],
        ], body: <<<'MD'
            # CSV exports

            Open **Billing**, then choose **Export as CSV**.
            MD);

        $this->updatePage('3.x/billing/invoices', basedOn: 'sha256:9f3a2c…', frontmatter: [
            'title' => 'Invoices',
            'description' => 'Find, download and export your invoices.',
            'sources' => ['app/Billing/**'],
        ], body: <<<'MD'
            # Invoices

            Every invoice is on the billing page. To get them all at once, export them as CSV.
            MD);

        $this->updateNavigation('3.x', basedOn: 'sha256:51be07…', patch: [
            [
                'add' => 'billing/exports',
                'group' => 'Billing',
                'after' => 'billing/invoices',
            ],
        ]);
    }
};
```

The hashes are shortened here; a real one is `sha256:` followed by 64 hexadecimal characters. Page bodies are nowdoc strings indented with the code, so the pull request diff reads as Markdown. PHP removes the closing marker's indentation from every line, so the page gets exactly the Markdown you see.

There is no `down()` to write: the base class has one built in. See [rollback](#roll-back).

### The class

`LaRecipe\Migrations\DocsMigration` extends Laravel's `Migration`. Four properties describe the change:

| Property | Means |
| --- | --- |
| `$set` | The doc set to change. Empty means `larecipe.default`. |
| `$author` | Who wrote the change, a person or an agent such as `claude-code`. Readers see it as the page's last change. |
| `$ref` | Where the change came from, such as the pull request's URL. Empty means the migration's file name. |
| `$summary` | One line for the revision list. |

Inside `up()`, you call the operations. Each takes a page address: `3.x/billing/exports` on a versioned set, `3.x/ar/billing/exports` for a page in another language, `billing/exports` on an unversioned set.

| Operation | Does |
| --- | --- |
| `createPage(string $path, array $frontmatter = [], string $body = '')` | Creates a page. |
| `updatePage(string $path, ?string $basedOn = null, array $frontmatter = [], string $body = '')` | Replaces a page's frontmatter and body. |
| `movePage(string $from, string $to, ?string $basedOn = null)` | Moves or renames a page. |
| `deletePage(string $path, ?string $basedOn = null)` | Deletes a page. It stays in the revision history. |
| `updateNavigation(string $version = '', ?string $basedOn = null, ?array $tree = null, ?array $patch = null, ?string $locale = null)` | Replaces a version's navigation with a whole `tree`, or applies a `patch`: a list of `add`, `move`, `remove` and `rename` operations. An empty version means the default one. |
| `updateManifest(?string $basedOn = null, array $patch = [])` | Changes `larecipe.json` with a JSON merge patch. |

Each operation goes through the same write path as every other change to a database set, as a migration with your `$author` and `$ref`. It is validated: content with validation errors, or an address that is not in the set, fails the migration, so CI catches a broken one before a deploy does. It publishes at once: the pull request was the review, in both [review modes](../keeping-current/review-modes.md).

## Where they live

Docs migrations live in `database/migrations/docs/`, inside your app's migrations folder but apart from schema migrations, so a pull request shows the two kinds separately. Change the folder with `migrations.path`:

```php
// config/larecipe.php
'migrations' => [
    'path' => database_path('migrations/docs'),
    'skip_in_tests' => false,
],
```

When any set uses the database source, LaRecipe registers this folder with Laravel's migrator. Every deploy already runs `php artisan migrate`, so there is no new step: `migrate:status`, `migrate:rollback` and `migrate --path=database/migrations/docs` work as for any migration. A docs migration for a set that no longer uses the database source is skipped and left pending.

## How your coding agent writes them

Your coding agent never writes a docs migration by hand. On a database set, it changes the docs through the local MCP author tools (`write_page`, `move_page`, `delete_page`, `update_navigation`, `update_manifest`), and each call does two things:

1. It applies the change to your local database, so you can preview the docs at once.
2. It writes the change into the branch's docs migration, and the tool's result names the file in `docs_migration`.

There is one file per branch, named after it: on `feature/csv-exports`, `database/migrations/docs/2026_10_17_141502_docs_feature_csv_exports.php`. A set other than the default one adds its key: `..._docs_handbook_feature_csv_exports.php`. Outside a git branch, the name ends in `local`.

The file is rewritten as the session goes on, so the pull request carries one readable file instead of dozens. Changes to the same page fold together: a create then an update is one `createPage`, two updates are one `updatePage` that keeps the first `basedOn`, and a create then a delete leaves nothing (the file goes away when it is empty). The author is named after the MCP client, and the summary collects each call's one-line summary.

The file is logged in your local migrations table as already run, so a local `php artisan migrate` does not apply it twice, and a local `php artisan migrate:rollback` undoes the session.

Commit the docs migration with the code and push them together. The [`larecipe-write-docs` and `larecipe-update-docs` skills](../keeping-current/skills.md) tell agents so.

## Write one by hand

<Artisan command="larecipe:make:docs-migration" />

```bash
php artisan larecipe:make:docs-migration document_billing_exports --page=3.x/billing/exports
```

This creates `database/migrations/docs/{timestamp}_docs_document_billing_exports.php`, with the summary "Document billing exports" and one `updatePage` for each `--page`, filled with the page's current frontmatter, body and content hash. Edit the Markdown in place: the hash is already right, so the change stays conflict-safe. Without `--page`, the file holds a commented example. The command warns when the set uses the repository source, whose docs migrations never run.

To change the generated file, publish the stub with `php artisan vendor:publish --tag=larecipe-stubs` and edit `stubs/docs-migration.stub`.

## Conflict safety

`basedOn` is the content hash a change was written against. Your agent's tools fill it from your local database, and `larecipe:make:docs-migration` fills it from the set. On deploy, before each update, move, delete, navigation or manifest change, LaRecipe compares it with what is live. If production changed in between (an edit in the admin UI, or an agent writing in `auto` review mode), the migration does not overwrite it:

- The change is kept as a revision with the status `conflict`, holding the migration's version next to production's, at the top of the admin UI's [review queue](database.md#the-pass-the-review-queue), where a person keeps production, takes the migration or edits a merge.
- `DocsMigrationConflicted` is dispatched.
- `php artisan migrate` prints a warning and carries on, so a docs conflict never fails a deploy:

```text
WARN LaRecipe docs conflict: [3.x/billing/invoices] The page [billing/invoices] changed since this change was written (expected sha256:9f3a2c…, found sha256:e41b07…). Kept as a conflict draft for review.
```

A `createPage` on a path that already has a page is a conflict too. An operation without `basedOn` skips the check and overwrites, so leave it in place.

<Event name="DocsMigrationConflicted" />

## Roll back

`php artisan migrate:rollback` runs the built-in `down()`. It walks the migration's operations backwards and puts each one back as a new revision: a replaced page gets its earlier version back, a created page is removed, and a deleted page returns.

A rollback never clobbers a later edit either. If someone changed a page after the migration ran, the page keeps that edit, the rollback's version waits as a conflict draft, and the rollback reports it. A conflict the migration raised that nobody resolved is rejected.

## Start from what is live

Your local database can lag behind production. Changes written against old content become conflicts on deploy, so refresh it before a docs session. On production, export the set:

```bash
php artisan larecipe:source:export --set=handbook --to=storage/handbook.tar.gz
```

Copy the file to your machine, then load it:

```bash
php artisan larecipe:pull --set=handbook --from=storage/handbook.tar.gz
```

`larecipe:pull` imports the bundle into your local database set, checks every content hash, and refuses repository sets, which are already in git. It reads bundles only: pulling straight from a production MCP endpoint is not available yet, and a URL is refused with that explanation.

## In tests

In your app's test suite, docs migrations run like any other migration; they are small inserts. To skip them, set `migrations.skip_in_tests` to `true`: while tests run, docs migrations are then skipped and left pending.

## Next

- [Database and the admin UI](database.md): revisions, review and the review queue.
- [Switching sources](switching.md): bundles, and moving between sources.
- [The MCP server](../agents/mcp.md): the author tools.
