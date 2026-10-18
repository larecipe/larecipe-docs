---
title: Database and the admin UI
description: Keep a doc set in your app's database, always behind a persistent cache, with a revision for every change and an admin UI to manage it.
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
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageSanitizer.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/StructureValidator.php
  - vendor/larecipe/larecipe/src/Domains/Admin/**
  - vendor/larecipe/larecipe/ui/src/admin/**
  - vendor/larecipe/larecipe/database/migrations/2026_10_17_000000_create_larecipe_tables.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Database and the admin UI

The database source keeps a doc set's pages, navigation and manifest in your app's database instead of in files. Choose it when people who do not work in the repository manage the docs in an admin UI, when you generate pages from data, or when docs content must stay out of the repository. Most teams start with the [repository source](repository.md), and you can [switch](switching.md) at any time: both keep the same format.

Point a set's source at the database:

```php title="config/larecipe.php"
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook'],
        'source' => [
            'driver' => 'database',
            'cache' => 'redis',
        ],
        'access' => ['mode' => 'auth'],
    ],
],
```

Then create the tables, and give the set a persistent cache store (`redis`, `database`, `file` or `memcached`):

```bash
php artisan migrate
```

Fill it by [moving an existing set](switching.md), in [the admin UI](#the-admin-ui), or by letting your coding agent write pages through the local MCP author tools. A new app can start here with `php artisan larecipe:install --driver=database`, which also writes a starter [docs migration](docs-migrations.md) with an Introduction page.

<Accordion title="Every source key">

| Key | Controls |
| --- | --- |
| `source.connection` | The database connection. `null` uses the default one. |
| `source.cache` | The cache store the set runs behind. Without it, `larecipe.cache.store`, then your default store. |
| `source.disk` | The filesystem disk for assets. `null` uses the default disk. |

When any set uses the database driver, LaRecipe loads its tables migration from the package, together with the docs migrations folder. To change the tables, publish the migration first with `php artisan vendor:publish --tag=larecipe-migrations`; once a `*_create_larecipe_tables.php` file exists in `database/migrations`, the package copy is no longer loaded.

</Accordion>

## How changes go live

Every change to a database set is a revision, with who made it and where it came from. Whether it goes live at once depends on where it came from:

| The change came from | It goes live |
| --- | --- |
| A docs migration, applied by `php artisan migrate` on deploy | At once: the pull request was the review. |
| Your coding agent's local MCP session | At once in your local database, and it is written into the branch's [docs migration](docs-migrations.md). |
| A person in the admin UI | At once, unless they save it as a draft. |
| An agent through the web MCP author tools, outside a pull request | As a draft under `review: required`, at once under `review: auto`. |

Every writer is validated (a page with errors is refused, and nothing is written) and can never silently overwrite a newer edit. Nothing is lost: a revert restores an earlier revision as a new one, and a deleted page can be restored. See [review modes](../keeping-current/review-modes.md).

<Accordion title="Revisions, conflicts and events">

A writer can say which version of a page it read (its content hash). If the page changed since, the write is refused and the writer is told to read it again. A docs migration keeps its version as a `conflict` draft instead; see [docs migrations](docs-migrations.md).

A revision is `draft`, `published`, `superseded`, `rejected`, `reverted` or `conflict`. At most one revision per page, navigation or manifest is `published` at a time. Publishing a draft marks the previous revision `superseded`. A revert marks the one it replaced `reverted`, so a revert can itself be reverted.

Each step fires an event for your own tools: `PageWritten`, `DraftCreated`, `DraftPublished`, `DraftRejected` and `ContentChanged`. See the [events reference](../reference/events.md).

</Accordion>

## Always behind a cache

A database set never reads the database to serve a cached page. Every database set runs behind a cache layer that cannot be turned off, and the store must keep data: the `array` and `null` stores are refused outside tests, `larecipe:validate` reports them as `cache.store`, and `larecipe:doctor` flags them.

When live content changes, LaRecipe warms the set's cache on your queue, so run a queue worker. On deploy, run `php artisan larecipe:cache:warm` after `migrate`; [deployment](../operate/deployment.md) has the full script.

<Accordion title="How the cache works">

- **One fingerprint per set.** It comes from the highest published revision, the number of publishes and the state of the assets table, and it lives in the cache store. Every write, publish, reject and revert reads it again from the database.
- **Everything else is cached under it, with no expiry:** the manifest, the page lists, each page, the navigation and assets up to 1 MB. A new fingerprint makes every old entry unreachable at once, so nothing is ever purged by hand. Larger assets are read from their disk.
- **Review data is never cached.** Drafts and revision history are read only to review them, and must always be current.
- **Warming.** When live content changes (a publish, a revert, a docs migration, a source move), LaRecipe dispatches `ContentChanged`. Its own queued listener warms the set after the database transaction commits. `larecipe:cache:warm` reads the fingerprint from the database, then caches the manifest and, for every version and locale, the page list, each page and the navigation. Without `--set`, it warms every database set.

<Event name="ContentChanged" />

</Accordion>

## Stricter content rules

People who edit a database set in the admin UI or over MCP may be less trusted than your codebase, so a database set holds its pages to tighter rules than a repository set:

- **Raw HTML is always escaped**, and every page the set renders also goes through an HTML sanitizer. `html_input` has no effect. See [Markdown](../writing/markdown.md#raw-html).
- **Every component keeps only the props it declares**, your own components included.
- **Analytics come from `config/larecipe.php` only** (`sets.{key}.integrations.analytics`). Integrations in the set's `larecipe.json` are ignored. See [analytics](../operate/analytics.md).
- **Links in navigation and `larecipe.json`** must be `http`, `https` or `mailto` URLs or paths on your site. A write with any other link is refused.
- **Snippet includes** (`<<< @/...`) resolve nowhere until you give the set a folder of its own in `sets.{key}.snippets.root`.

## The admin UI

Every doc set gets an admin area at `/{prefix}/_admin`: for a set at `/handbook` that is `/handbook/_admin`. A database set gets the full editor. A repository set gets a read-only overview (the validation report, agent readiness and the stale pages), because its pages are edited in git.

The `manageLarecipe` gate decides who gets in, with the set's key as its argument. Everyone else gets a 403. Until you define the gate, only signed-in users in a local environment pass, so define it for production:

```php title="app/Providers/AppServiceProvider.php"
Gate::define('manageLarecipe', fn (User $user, string $set) => $user->is_admin);
```

Every change made in the admin is validated, saved as a revision and attributed to the signed-in person.

### The pass: the review queue

The pass is where changes wait for a person, named after the counter in a restaurant kitchen where every plate is checked before it leaves. Docs migration conflicts come first, then drafts. Publish a change, reject it with a reason, or edit it and publish your own version.

<Accordion title="Conflicts and drafts at the pass">

Each change waiting there is a ticket, and the sidebar shows how many wait.

A conflict is a deploy's change held back because production changed after the migration was written. It shows both sides, rendered by the server or as source, with three ways to resolve it: keep production, take the migration, or edit a merge.

Drafts are changes from agents that worked outside a pull request, and drafts people saved. Each shows its provenance (who or which agent, and its summary), a rendered diff and a source diff. A rejection reason is readable by the agent that wrote the draft. With `review: auto`, agents publish on their own, so the pass also lists what they published lately, each one click from a revert.

</Accordion>

### History and activity

Every page, each version's navigation and the settings have a history you can restore from. The activity screen lists every change to the set, and is where you export admin edits as a docs migration to commit with a pull request.

<Accordion title="Restoring, and bringing admin edits back to git">

A history lists every revision, newest first, each with a diff against the one it replaced, including the revisions each docs migration created. A revision readers once saw can be restored. The restore is a new revision, so it can be undone too.

Edits in the admin UI are direct database changes, saved as revisions. They create no migration files on their own. To carry chosen changes to every environment through a pull request, select them on the activity screen and export them as one docs migration: the admin shows the file, to copy or download, and you commit it. Where those changes are already live, each of its operations does nothing; anywhere that drifted, it becomes a conflict at the pass instead of overwriting.

To move the whole set into git, `php artisan larecipe:source:export` writes it out as files, and `larecipe:source:move database repository` moves it back for good. See [switching sources](switching.md).

</Accordion>

### Other screens

<AccordionGroup>

<Accordion title="Overview">

The number of pages, the conflicts and drafts waiting for review, the review mode and the version. Under that are the validation report, the same one `larecipe:validate` prints, and an agent readiness card: the [`larecipe:doctor`](../operate/validation.md#larecipedoctor) report, graded, with a fix for each finding. There are no traffic dashboards: analytics go to your own tools.

</Accordion>

<Accordion title="Pages and navigation">

The page tree is the version's navigation, for one language at a time. Drag pages and groups to reorder them or to move a page into another group, or focus a handle and press Alt with the arrow keys. Create pages and groups, rename or remove a group (its pages stay), take a page out of the navigation, or delete it. Pages the navigation does not list wait under the tree, ready to be listed, and new pages proposed as drafts link to their review. A JSON view edits the same navigation as raw `navigation.json`.

A version without `navigation.json` starts from the tree its pages imply. There, hiding a page sets `hidden: true` in its frontmatter, and saving a new order writes the tree as the version's `navigation.json`.

</Accordion>

<Accordion title="The editor">

Pages open in a Markdown editor (CodeMirror) with a live preview rendered by LaRecipe's own server renderer, so the preview is exactly what readers will see, components included. A component palette lists every component the server can render (the built-ins, live references, gate blocks and your own) and inserts one at the cursor with its props filled in. Validation issues show as you type, marked on the line they point at and listed under the editor. The frontmatter has a form generated from its JSON Schema, with a switch to edit the raw YAML.

Saving publishes at once, or saves a draft for review instead. Cmd+S or Ctrl+S publishes. Each save is written against the version of the page the editor loaded. If someone changed the page in the meantime, nothing is overwritten: the editor shows their version against yours, and you load theirs or keep yours. A page can also be moved to a new path or deleted, and a deleted page stays in its history.

</Accordion>

<Accordion title="Assets">

Drop images and files on the assets screen to upload them to the set's disk, under `larecipe/{set}/{version}/`. Each asset shows the path to paste into Markdown and the pages that use it. Deleting an asset that a page still uses asks you to confirm, and names those pages.

Assets are not revisioned; the pages that use them are. Docs migrations do not carry assets: upload them here, or reference images by URL. A source move or a bundle import copies them.

</Accordion>

<Accordion title="Settings">

The settings screen edits the set's `larecipe.json`: a form generated from its JSON Schema for the name, logo, theme tokens, versions, locales, navbar links, redirects and the description agents read, with a switch to raw JSON for the rest. A live theme preview shows your tokens on LaRecipe's own components while you change them. Infrastructure stays in `config/larecipe.php`: routes, the source, the cache, AI and analytics. The settings have a history too.

</Accordion>

<Accordion title="About the admin app">

The admin is its own small app, built from the same design system as the reading UI, in light or dark. It needs JavaScript; the docs themselves do not. The version and language pickers in the header set the scope of every screen. Changes go through the same write path as an agent's. Its code loads only there, never on reader pages, and its pages are never cached or indexed. The admin is a tool, not content, so a refused user gets a 403 rather than a 404. The same `manageLarecipe` gate decides who may use the web MCP author tools and `validate_docs` on the web. Admin changes are recorded with the `admin_ui` channel.

</Accordion>

</AccordionGroup>

## Details

<AccordionGroup>

<Accordion title="The tables">

Every row carries its set's key, so one connection can hold many doc sets.

| Table | Holds |
| --- | --- |
| `larecipe_pages` | The live pages: set, version, locale, path, title, description, frontmatter, body, content hash and the revision that is live. A deleted page keeps its row, with `deleted_at` set. |
| `larecipe_navigation` | One navigation tree per set, version and locale. |
| `larecipe_manifest` | One `larecipe.json` per set. |
| `larecipe_revisions` | Every change, live or not: the operation, the full content, its hash and the hash it was based on, the status, who made it (a person, an agent or a migration, with a name and a reference), where it came from, a summary, and who reviewed it. |
| `larecipe_assets` | Each asset's path, disk, path on the disk, type, size and hash. |
| `larecipe_docs_migrations` | What each docs migration changed, so a rollback can put it back. |

</Accordion>

<Accordion title="Your own models">

LaRecipe reads and writes these tables through Eloquent models you can replace, the way Sanctum and Cashier allow. Extend a model and point its key at your class, to add global scopes, tenancy or observers:

```php title="config/larecipe.php"
'models' => [
    'page' => App\Models\DocsPage::class,
],
```

```php title="app/Models/DocsPage.php"
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

</Accordion>

</AccordionGroup>

## Next

- [Docs migrations](docs-migrations.md): how changes made in a pull request reach a database set.
- [Switching sources](switching.md): move between the repository and the database.
- [Review modes](../keeping-current/review-modes.md): `required` and `auto`.
