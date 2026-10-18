---
title: Switching sources
description: Move a doc set between the repository and the database at any time, with a dry run first and a hash-by-hash check after, or carry it anywhere as a bundle.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceMoveCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceExportCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceImportCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/MoveReportPrinter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/MoveSourceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ExportSourceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ImportBundleAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceCopier.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceComparer.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceInventory.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/BundleArchive.php
  - vendor/larecipe/larecipe/src/Domains/Sources/DTOs/Bundle.php
---

# Switching sources

A doc set can live in your [repository](repository.md) or your [database](database.md), and both keep the same format: Markdown pages with frontmatter, `larecipe.json` and `navigation.json`. So moving between them is a copy plus a proof. `larecipe:source:move` copies a set from one source to the other, checks every content hash when you ask it to, and prints the config change that switches serving. It never edits your config itself.

> [!IMPORTANT]
> Moving into the database needs LaRecipe's tables, which the package loads only once a set uses the database source. Publish and run them first: `php artisan vendor:publish --tag=larecipe-migrations`, then `php artisan migrate`. The command stops with this same advice when the tables are missing.

<Steps>
<Step title="See the plan">

```bash
php artisan larecipe:source:move repository database --set=docs --dry-run
```

A dry run copies the set into a temporary bundle, validates every page, and prints how many pages, navigation files and assets will move, with any warnings and validation errors. It writes nothing to the target.

</Step>
<Step title="Move and verify">

```bash
php artisan larecipe:source:move repository database --set=docs --verify
```

With `--verify`, the command reads everything back from the target and compares the content hash of every page, navigation tree, manifest and asset. It reports that everything matches, or lists each mismatch and exits with an error.

</Step>
<Step title="Switch serving">

The command ends with the change to make in `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'source' => ['driver' => 'database'],
    ],
],
```

Make the change, deploy it, and run `php artisan larecipe:cache:warm` for a database set.

</Step>
</Steps>

The two directions are the two argument orders: `repository database`, or `database repository`. Moving the other way prints `['driver' => 'repository', 'path' => base_path('docs')]` as the config change.

## Where to run it

The move writes to the target on the machine where you run it. To move production's docs into production's database, run it on production before you deploy the config change. To move a database set into your repository, refresh your local database first with [`larecipe:pull`](docs-migrations.md#start-from-what-is-live), run the move locally, and commit the files. From then on, provenance comes from git.

## Bundles: export and import

A bundle is a doc set written out as canonical files, to a directory or a `.tar.gz` file, with the same layout as a repository set's folder. Use it for a backup, to hand docs to another app, or to bring a whole set of admin UI edits back into git.

```bash
php artisan larecipe:source:export --set=docs --to=storage/docs.tar.gz
php artisan larecipe:source:import storage/docs.tar.gz --set=docs --verify
```

Import validates every page first and writes nothing if any has an error. Like a move, it adds and updates, and never deletes pages the set already has. To carry only some admin edits through a pull request, export them as a docs migration from the admin's [activity screen](database.md#history-and-activity) instead.

## Details

<AccordionGroup>

<Accordion title="Every option of source:move">

| Option | Means |
| --- | --- |
| `--set` | The doc set. Defaults to `larecipe.default`. |
| `--path` | The folder on the repository side, when it is not the configured one. Defaults to `docs/` for the default set, and to a folder named after the set otherwise. |
| `--connection` | The database connection on the database side. |
| `--dry-run` | Show the plan and the validation, and write nothing. |
| `--verify` | After writing, read the target back and compare every content hash. |

<Artisan command="larecipe:source:move" />

</Accordion>

<Accordion title="What moves, and in what order">

A move copies, in this order:

- **The manifest**, `larecipe.json`.
- **Every page** of every version and language, with its frontmatter and body. Deeper pages go first, so a folder's own page lands as `folder/index.md` in a repository and relative links keep working.
- **Each navigation**, per version. A language's navigation is copied only when it differs from the default language's, because both sources fall back to that one.
- **Assets**: files in `_assets/` and next to pages on the repository side, files on the set's disk on the database side.

Every page is validated before anything is written. If any page has a validation error, nothing is written at all. Content that is already identical in the target is skipped, so running the same move twice writes nothing the second time. Pages the target already holds that the source does not are kept, and the plan warns about them. Every change is recorded as made by `larecipe:source:move`.

</Accordion>

<Accordion title="What does not move">

- **Revision history.** Git history and the revisions table are not the same thing, so neither is converted into the other. The plan says so every time: the target starts its own history from the move.
- **Drafts and conflicts.** Only live content moves. Drafts and unresolved docs migration conflicts stay in the database's revisions table.
- **Hand-written skills.** A repository set's `skills/` folder is not part of a set's content, so it does not move.
- **Your config.** The command prints the change; you make it.

</Accordion>

<Accordion title="Bundle formats, with this site's export">

`--to` is a new or empty directory, or a file ending in `.tar.gz`. Archives are packed with PHP's own `PharData`, so the server needs no `tar` binary. An import is recorded as made by `larecipe:source:import`, and `--verify` compares every content hash afterwards, as a move does. [`larecipe:pull`](docs-migrations.md#start-from-what-is-live) uses the same bundles to refresh a local database set from production.

Exporting this site's own doc set, while this page was first written, printed:

```text
Manifest (larecipe.json) ........ yes
Pages ........................... 31
Navigation files ................ 2
Assets .......................... 0

INFO Exported the [docs] set to [storage/docs.tar.gz].
```

The archive holds the same paths as this site's `docs/` folder, for example:

```text
larecipe.json
2.x/index.md
2.x/navigation.json
3.x/index.md
3.x/navigation.json
3.x/agents/surfaces.md
3.x/ar/index.md
3.x/ar/get-started/installation.md
```

</Accordion>

</AccordionGroup>

## Next

- [The repository source](repository.md): docs as files, with provenance from git.
- [Database and the admin UI](database.md): docs in your database, always behind a cache.
- [Docs migrations](docs-migrations.md): content changes to a database set, in the pull request.
