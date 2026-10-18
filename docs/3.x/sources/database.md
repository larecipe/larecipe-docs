---
title: Database and the admin UI
description: Keep a doc set in your app's database, always behind a persistent cache, with a revision for every change, review for agent changes made outside a pull request, and an admin UI to manage it.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DatabaseSource.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DatabaseSourceFactory.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/SourceModels.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/CachedSource.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Models/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Listeners/WarmChangedSet.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/CacheWarmCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Events/**
  - vendor/larecipe/larecipe/src/Domains/Sources/SourcesServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Access/Laravel/GateAuthorizer.php
  - vendor/larecipe/larecipe/src/Domains/Admin/**
  - vendor/larecipe/larecipe/ui/src/admin/**
  - vendor/larecipe/larecipe/database/migrations/2026_10_17_000000_create_larecipe_tables.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Database and the admin UI

The database source keeps a doc set's pages, navigation and manifest in your app's database instead of in files. Choose it when people who do not work in the repository manage the docs in an admin UI, when you generate pages from data, or when docs content must stay out of the repository.

It keeps the same format as the [repository source](repository.md) (Markdown with frontmatter, `larecipe.json`, `navigation.json`), so you can [switch](switching.md) at any time. It always runs behind a persistent cache, every change is a revision with provenance, and changes made in a pull request travel as [docs migrations](docs-migrations.md). Most teams start with the repository.

## Set it up

Point a set's source at the database:

```php
// config/larecipe.php
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook'],
        'source' => [
            'driver' => 'database',
            'connection' => null,
            'cache' => 'redis',
            'disk' => 's3',
        ],
        'access' => ['mode' => 'auth'],
    ],
],
```

| Key | Controls |
| --- | --- |
| `source.connection` | The database connection. `null` uses the default one. |
| `source.cache` | The cache store the set runs behind. Without it, `larecipe.cache.store`, then your default store. |
| `source.disk` | The filesystem disk for assets. `null` uses the default disk. |

Then:

<Steps>
<Step title="Create the tables">

```bash
php artisan migrate
```

When any set uses the database driver, LaRecipe loads its tables migration from the package, together with the docs migrations folder. To change the tables, publish the migration first with `php artisan vendor:publish --tag=larecipe-migrations`; once a `*_create_larecipe_tables.php` file exists in `database/migrations`, the package copy is no longer loaded.

</Step>
<Step title="Use a persistent cache store">

`redis`, `database`, `file` or `memcached`. See [always behind a cache](#always-behind-a-cache).

</Step>
<Step title="Fill it">

Move an existing repository set with `larecipe:source:move repository database`, load a bundle with `larecipe:source:import`, write pages in [the admin UI](#the-admin-ui), or let your coding agent write them through the local MCP author tools. See [switching sources](switching.md).

</Step>
</Steps>

`php artisan larecipe:install --driver=database` sets this up for a new app, with a starter docs migration (`database/migrations/docs/{timestamp}_docs_introduction.php`) that gives the set its name and an Introduction page, so `/docs` works right after `php artisan migrate`. See [installation](../get-started/installation.md).

## The tables

Every row carries its set's key, so one connection can hold many doc sets.

| Table | Holds |
| --- | --- |
| `larecipe_pages` | The live pages: set, version, locale, path, title, description, frontmatter, body, content hash and the revision that is live. A deleted page keeps its row, with `deleted_at` set. |
| `larecipe_navigation` | One navigation tree per set, version and locale. |
| `larecipe_manifest` | One `larecipe.json` per set. |
| `larecipe_revisions` | Every change, live or not: the operation, the full content, its hash and the hash it was based on, the status, who made it (a person, an agent or a migration, with a name and a reference), where it came from, a summary, and who reviewed it. |
| `larecipe_assets` | Each asset's path, disk, path on the disk, type, size and hash. |
| `larecipe_docs_migrations` | What each docs migration changed, so a rollback can put it back. |

A revision is `draft`, `published`, `superseded`, `rejected`, `reverted` or `conflict`. At most one revision per page, navigation or manifest is `published` at a time.

## Always behind a cache

A database set never reads the database to serve a cached page. LaRecipe wraps every database set in a cache layer, `CachedSource`, and it cannot be turned off.

- **The store must keep data.** The `array` and `null` stores forget everything between requests, so a database set refuses them outside tests: the set fails to load with an error that names the store, `larecipe:validate` reports `cache.store`, and `larecipe:doctor` flags it.
- **One fingerprint per set.** It comes from the highest published revision, the number of publishes and the state of the assets table, and it lives in the cache store. Every write, publish, reject and revert reads it again from the database.
- **Everything else is cached under it, with no expiry:** the manifest, the page lists, each page, the navigation and assets up to 1 MB. A new fingerprint makes every old entry unreachable at once, so nothing is ever purged by hand. Larger assets are read from their disk.
- **Review data is never cached.** Drafts and revision history are read only to review them, and must always be current.

When live content changes (a publish, a revert, a docs migration, a source move), LaRecipe dispatches `ContentChanged`. Its own listener warms the set's cache on your queue, after the database transaction commits, so run a queue worker:

<Event name="ContentChanged" />

On deploy, warm the cache after the docs migrations run, so the first readers get warm pages:

```bash
php artisan migrate --force
php artisan larecipe:cache:warm
```

`larecipe:cache:warm` reads the fingerprint from the database, then caches the manifest and, for every version and locale, the page list, each page and the navigation. Without `--set`, it warms every database set. See [caching and optimize](../operate/caching.md).

## Your own models

LaRecipe reads and writes these tables through Eloquent models you can replace, the way Sanctum and Cashier allow. Extend a model and point its key at your class, to add global scopes, tenancy or observers:

```php
// config/larecipe.php
'models' => [
    'page' => App\Models\DocsPage::class,
],
```

```php
namespace App\Models;

use App\Observers\DocsPageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use LaRecipe\Domains\Sources\Models\Page;

#[ObservedBy(DocsPageObserver::class)]
class DocsPage extends Page
{
}
```

The keys are `page`, `navigation`, `manifest`, `revision`, `asset` and `docs_migration`. Extend LaRecipe's own model rather than writing a new one, so the table and casts stay the same.

## Revisions and review

Every change to a database set is a revision, with who made it and where it came from. Whether it goes live at once depends on where it came from:

| The change came from | It goes live |
| --- | --- |
| A docs migration, applied by `php artisan migrate` on deploy | At once: the pull request was the review. |
| Your coding agent's local MCP session | At once in your local database, and it is written into the branch's docs migration. |
| A person in the admin UI | At once, unless they save it as a draft. |
| An agent through the web MCP author tools, outside a pull request | With `review: required`, as a draft for a person to publish. With `review: auto`, at once. |

The review mode is `review` in `config/larecipe.php` (`LARECIPE_REVIEW`), `required` by default, and each set can set its own. See [review modes](../keeping-current/review-modes.md).

Two checks apply to every writer:

- **Validation.** A page with validation errors is refused, and nothing is written.
- **No silent overwrites.** A writer can say which version of a page it read (its content hash). If the page changed since, the write is refused and the writer is told to read it again. A docs migration keeps its version as a `conflict` draft instead; see [conflict safety](docs-migrations.md).

Nothing is lost. Publishing a draft marks the previous revision `superseded`. A revert restores an earlier revision as a new published one and marks the one it replaced `reverted`, so a revert can itself be reverted. A deleted page stays in the history and can be restored.

Each step fires an event for your own tools: `PageWritten`, `DraftCreated`, `DraftPublished`, `DraftRejected` and `ContentChanged`. See the [events reference](../reference/events.md).

## Assets

Images and other files of a database set live on a Laravel filesystem disk, `source.disk`, under `larecipe/{set}/{version}/`. A row in `larecipe_assets` records each one. Assets are not revisioned; the pages that use them are. Docs migrations do not carry assets: upload them in the admin UI, or reference images by URL. A source move or a bundle import copies them.

## Snippet includes

Pages of a database set may be written by people outside the codebase, so `<<< @/...` includes resolve nowhere by default. To allow them, give the set a folder of its own in `sets.{key}.snippets.root`.

## The admin UI

Every doc set gets an admin area at `/{prefix}/_admin`: for a set at `/handbook` that is `/handbook/_admin`. A database set gets the full editor. A repository set gets a read-only overview, because its pages are edited in git.

The admin is its own small app, built from the same design system as the reading UI, in light or dark. Its code loads only there, never on reader pages, and its pages are never cached or indexed. It needs JavaScript; the docs themselves do not.

The `manageLarecipe` gate decides who gets in, with the set's key as its argument. Everyone else gets a 403: the admin is a tool, not content, so it does not pretend to be missing. Until you define the gate, only signed-in users in a local environment pass. Define it for production:

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Gate::define('manageLarecipe', function (User $user, string $set) {
    return $user->is_admin;
});
```

The same gate decides who may use the web MCP author tools and `validate_docs` on the web. Every change made in the admin goes through the same write path as an agent's or a docs migration's: it is validated, saved as a revision and attributed to the signed-in person, with the `admin_ui` channel.

The version and language pickers in the header set the scope of every screen.

### Overview

The first screen says what state the docs are in: the number of pages, the conflicts and drafts waiting for review, the review mode and the version. Under that are the validation report, the same one `larecipe:validate` prints, and an agent readiness card: the [`larecipe:doctor`](../operate/validation.md#larecipedoctor) report, graded, with a fix for each finding. There are no traffic dashboards: analytics go to your own tools.

### Pages and navigation

The page tree is the version's navigation, for one language at a time. Drag pages and groups to reorder them or to move a page into another group, or focus a handle and press Alt with the arrow keys. Create pages and groups, rename or remove a group (its pages stay), take a page out of the navigation, or delete it. Pages the navigation does not list wait under the tree, ready to be listed, and new pages proposed as drafts link to their review. A JSON view edits the same navigation as raw `navigation.json`.

A version without `navigation.json` starts from the tree its pages imply. There, hiding a page sets `hidden: true` in its frontmatter, and saving a new order writes the tree as the version's `navigation.json`.

### The editor

Pages open in a Markdown editor (CodeMirror) with a live preview rendered by LaRecipe's own server renderer, so the preview is exactly what readers will see, components included. A component palette lists every component the server can render (the built-ins, live references, gate blocks and your own) and inserts one at the cursor with its props filled in. Validation issues show as you type, marked on the line they point at and listed under the editor. The frontmatter has a form generated from its JSON Schema, with a switch to edit the raw YAML.

Saving publishes at once, or saves a draft for review instead. Cmd+S or Ctrl+S publishes. Each save is written against the version of the page the editor loaded. If someone changed the page in the meantime, nothing is overwritten: the editor shows their version against yours, and you load theirs or keep yours. A page can also be moved to a new path or deleted, and a deleted page stays in its history.

### The pass: the review queue

The review queue is called the pass, after the counter in a restaurant kitchen where every plate is checked before it leaves. Each change waiting there is a ticket, and the sidebar shows how many wait.

Docs migration conflicts come first: a deploy's change, held back because production changed after the migration was written. A conflict shows both sides, rendered by the server or as source, with three ways to resolve it: keep production, take the migration, or edit a merge.

Then drafts: changes from agents that worked outside a pull request, and drafts people saved. Each shows its provenance (who or which agent, and its summary), a rendered diff and a source diff. Publish it, reject it with a reason, which the agent that wrote it can read, or edit it and publish your own version. With `review: auto`, agents publish on their own, so the pass also lists what they published lately, each one click from a revert.

### History and activity

Every page, each version's navigation and the settings have a history: every revision, newest first, each with a diff against the one it replaced, including the revisions each docs migration created. A revision readers once saw can be restored. The restore is a new revision, so it can be undone too.

The activity screen lists every change to the set, newest first. Changes made in production, in the admin or by agents outside a pull request, live only in the database. Select them there and export them as one docs migration: the admin shows the file, to copy or download, and you commit it with a pull request. Where those changes are already live, each of its operations does nothing; anywhere that drifted, it becomes a conflict at the pass instead of overwriting.

### Assets

Drop images and files on the assets screen to upload them to the set's disk. Each asset shows the path to paste into Markdown and the pages that use it. Deleting an asset that a page still uses asks you to confirm, and names those pages.

### Settings

The settings screen edits the set's `larecipe.json`: a form generated from its JSON Schema for the name, logo, theme tokens, versions, locales, navbar links, redirects and the description agents read, with a switch to raw JSON for the rest. A live theme preview shows your tokens on LaRecipe's own components while you change them. Infrastructure stays in `config/larecipe.php`: routes, the source, the cache, AI and analytics. The settings have a history too.

### Repository sets

A repository set gets a read-only overview at the same path: the validation report, agent readiness and the stale pages, whose code changed after them. Its pages are edited in git, in a pull request.

### Bringing admin edits back to git

Edits in the admin UI are direct database changes, saved as revisions. They create no migration files on their own. To carry chosen changes to every environment through a pull request, export them from the activity screen as a docs migration. To move the whole set into git, `php artisan larecipe:source:export` writes it out as files, and `larecipe:source:move database repository` moves it back for good. See [switching sources](switching.md).

## Next

- [Docs migrations](docs-migrations.md): how changes made in a pull request reach a database set.
- [Switching sources](switching.md): move between the repository and the database.
- [Review modes](../keeping-current/review-modes.md): `required` and `auto`.
