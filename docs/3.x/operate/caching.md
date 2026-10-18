---
title: Caching and optimize
description: How LaRecipe caches rendered pages, page lists and navigation, and what larecipe:optimize compiles on deploy.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RenderCache.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PageCatalog.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PackageAssetController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/ProvenanceCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/CacheWarmCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Listeners/WarmChangedSet.php
  - vendor/larecipe/larecipe/src/Domains/References/Actions/ReferencesFingerprintAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/SitemapController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Support/CacheFormat.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Caching and optimize

LaRecipe caches by content, not by time. A rendered page is stored under a key made from the page's content and everything that shapes its HTML, so a changed page gets a new key and nothing is ever purged by hand. On deploy, `php artisan optimize` compiles every page ahead of the first reader.

## What is cached

| What | Key | Changes when |
| --- | --- | --- |
| Rendered articles (HTML, table of contents, components) | the renderer version, the page (set, version, locale, path), its content hash, the Markdown environment version and the variant (the set's mount, plus the audience when gate blocks apply) | the page, LaRecipe, a component definition, a Markdown extension or the code theme changes |
| Page lists and navigation | the set, version and locale, plus the source's fingerprint | any file in the version changes |
| Search records | the set, version and locale, plus the source's fingerprint | any file in the version changes |
| The sitemap | the fingerprints of every version and the set's URL, for your app's own host (`APP_URL`) or the set's `route.domain` only | any file in the set changes |

The Markdown environment version covers the component registry, so registering or changing a component with `LaRecipe::component()` renders every page again on the next request.

Two kinds of dependencies sit outside a page's content hash, and LaRecipe tracks both:

- **Snippet includes.** A cached render remembers the hash of every file it included with `<<<`. When one of those files changes, the page renders again.
- **Live references.** A page that uses `<Route>`, `<Artisan>`, `<Model>` or `<Event>` is cached under the fingerprint of the part of the app it reads. Rename a route and exactly the pages that show it render again. When the routes are cached with `php artisan route:cache`, the route fingerprint is the hash of the route cache file. See [live references](../writing/live-references.md).

Renders with errors (an unknown component, a missing snippet, a reference that no longer resolves) are never cached, so a fix shows up on the next request. Warnings, such as a video placeholder, do not block caching.

Every cache key LaRecipe writes starts with a format version (`larecipe:v2:`), and rendered pages also carry the renderer's version. A release that changes what LaRecipe stores, or how it renders, moves them, so entries an older release wrote are never read: right after you upgrade LaRecipe, the cache starts cold and pages render again. Run `php artisan optimize` on that deploy, as you already do, and readers get warm pages from the first request. An entry in a shape LaRecipe does not expect is read as a miss, never an error.

`PageRendered` fires whenever an article is rendered because the cache did not have it, and on every request for a Markdown twin, if you want to follow what is rendered. See the [events reference](../reference/events.md#pagerendered).

## The cache store

LaRecipe uses your app's default cache store unless you name another one:

```php
// config/larecipe.php
'cache' => [
    'store' => env('LARECIPE_CACHE_STORE'),
],
```

Any persistent store works: `database`, `file`, `redis`, `memcached`. A repository set reads its files from disk, so a cold cache costs render time, not correctness. A database set always runs behind this store, and `larecipe:validate` reports an error when a database set uses the `array` or `null` store outside tests.

Everything LaRecipe caches is plain data (strings and arrays), so it works with Laravel 13's default `cache.serializable_classes => false`, with no cache configuration in your app.

## Compile on deploy

`larecipe:optimize` renders every page of every repository set into the cache store, builds the page lists, the navigation and the search records, and measures each guest search index against the budget. It is registered with Laravel's `optimize` command, so an existing deploy script that runs `php artisan optimize` compiles the docs too.

<Artisan command="larecipe:optimize" />

```bash
php artisan optimize
```

This is this site's own run:

```text
docs 3.x en ................... 46 pages, 542 search records, 127 KB index
docs 3.x ar ...................... 2 pages, 17 search records, 6 KB index
docs 2.x en .................... 17 pages, 76 search records, 10 KB index

INFO LaRecipe docs compiled.
```

The list of what it compiled is written to `bootstrap/cache/larecipe/optimized.php`. Database sets are skipped: they are cached as they publish.

`larecipe:clear` forgets what was compiled: the rendered pages and search records listed in `optimized.php`, then that list. `php artisan optimize:clear` runs it.

<Artisan command="larecipe:clear" />

It deletes `bootstrap/cache/larecipe/optimized.php` and nothing else in that folder, so the compiled provenance index, the live reference snapshot and the compiled hook patterns survive `optimize:clear`.

## Provenance

The "Updated" line under each title comes from git. `larecipe:provenance` reads the git history of every repository set once and compiles the last change of each page (when, by whom, the agent named in a `Co-Authored-By` trailer, the pull request number) into `bootstrap/cache/larecipe/provenance-{set}.php`. Requests read that file and never run git.

<Artisan command="larecipe:provenance" />

Run it where the full history is, usually in your build step: a shallow clone gives every page the same last commit. Without git, or without a compiled index, a page's "Updated" time falls back to its file's modification time. `larecipe:optimize` does not compile provenance, so run both:

```bash
php artisan larecipe:provenance
php artisan optimize
```

## Database sets

A database set never reads the database to serve a cached page. Its fingerprint lives in the cache store, every publish bumps it, and the `ContentChanged` event queues a listener that warms the changed set after the database transaction commits. Run a queue worker so that warming happens in the background.

After `php artisan migrate` applies new docs migrations on deploy, warm the cache so the first readers get warm pages:

<Artisan command="larecipe:cache:warm" />

Without `--set`, it warms every database set. See [the database source](../sources/database.md).

## Responses and shared caches

Each response says how a browser, a proxy or a CDN may keep it. A public page served to a guest is cacheable for everyone. A response for a signed-in reader is personal in every format (HTML, the JSON the UI fetches, and Markdown), because its navigation can list pages guests never see: it goes out `private`, with `Vary: Accept, Cookie` and an `ETag` of its own, so a shared cache never hands it to anyone else. [Gate blocks and private docs](../writing/gate-blocks.md#response-headers) lists every case.

## UI assets

The reading UI ships prebuilt inside the package, so the host needs no Node. LaRecipe serves it from `vendor` at `/larecipe-assets/{build}/{file}`. File names carry a content hash, so responses are `Cache-Control: public, max-age=31536000, immutable`, and precompressed `.br` and `.gz` files are used when the browser accepts them.

To let your web server or CDN serve the files instead of PHP, publish them:

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```

The files land in `public/vendor/larecipe`. LaRecipe points the page at them only while the published build matches the installed package, so a stale copy after `composer update` is ignored until you publish again. Add the command to your deploy script, after `composer install`.

## Next

- [Deployment](deployment.md): the full deploy checklist.
- [Validation and doctor](validation.md): check the docs before they ship.
