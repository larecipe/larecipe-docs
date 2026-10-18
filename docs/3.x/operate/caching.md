---
title: Caching
description: How LaRecipe caches rendered pages, page lists, navigation and search records by content, so nothing is ever purged by hand, and what larecipe:optimize and larecipe:clear do.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RenderCache.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PageCatalog.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/CodeHighlighter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/References/Actions/ReferencesFingerprintAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/SitemapController.php
  - vendor/larecipe/larecipe/src/Support/CacheFormat.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Caching

LaRecipe caches by content, not by time. A rendered page is stored under a key made from the page's content and everything that shapes its HTML, so a changed page gets a new key and nothing is ever purged by hand. You do not configure any of it: [deployment](deployment.md) has the two commands that warm the cache on deploy.

LaRecipe uses your app's default cache store unless you name another one:

```php title="config/larecipe.php"
'cache' => [
    'store' => env('LARECIPE_CACHE_STORE'),
],
```

Any persistent store works: `database`, `file`, `redis`, `memcached`. A repository set reads its files from disk, so a cold cache costs render time, not correctness. A [database set](../sources/database.md) always runs behind this store, and `larecipe:validate` reports an error when a database set uses the `array` or `null` store outside tests. Everything LaRecipe caches is plain data (strings and arrays), so it works with Laravel 13's default `cache.serializable_classes => false`.

## What is cached

| What | Changes when |
| --- | --- |
| Rendered articles (HTML, table of contents, components) | The page, LaRecipe, a component definition, a Markdown extension or the code theme changes |
| Highlighted code blocks, each on its own | The block's code, language or line marks change |
| Page lists and navigation | Any file in the version changes |
| Search records | Any file in the version changes |
| The sitemap | Any file in the set changes |

Renders with errors (an unknown component, a missing snippet, a reference that no longer resolves) are never cached, so a fix shows up on the next request. Warnings, such as a video placeholder, do not block caching.

<Accordion title="The keys in detail">

| What | Key |
| --- | --- |
| Rendered articles | The renderer version, the page (set, version, locale, path), its content hash, the Markdown environment version and the variant (the set's mount, plus the audience when gate blocks apply) |
| Highlighted code | The code theme version, the language, the diff and highlight marks, and the code |
| Page lists and navigation | The set, version and locale, plus the source's fingerprint |
| Search records | The set, version and locale, plus the source's fingerprint |
| The sitemap | The fingerprints of every version and the set's URL, for your app's own host (`APP_URL`) or the set's `route.domain` only |

The Markdown environment version covers the component registry, so registering or changing a component with `LaRecipe::component()` renders every page again on the next request. A repository set's fingerprint comes from its files; see [the repository source](../sources/repository.md). A database set's fingerprint lives in the cache store and moves with every publish; see [the database source](../sources/database.md).

</Accordion>

## What sits outside a page's content

Two kinds of dependencies are not in a page's own text, and LaRecipe tracks both:

- **Snippet includes.** A cached render remembers the hash of every file it included with `<<<`. When one of those files changes, the page renders again.
- **Live references.** A page that uses `<Route>`, `<Artisan>`, `<Model>` or `<Event>` is cached under the fingerprint of the part of the app it reads. Rename a route and exactly the pages that show it render again. When the routes are cached with `php artisan route:cache`, the route fingerprint is the hash of the route cache file. See [live references](../writing/live-references.md).

## Compile and clear

`larecipe:optimize` renders every page of every repository set into the cache store, builds the page lists, the navigation and the search records, and measures each guest search index against the budget. It is registered with Laravel's `optimize`, so `php artisan optimize` runs it. Database sets are skipped: they are cached as they publish.

`larecipe:clear` forgets what was compiled, and `php artisan optimize:clear` runs it.

<AccordionGroup>

<Accordion title="What optimize writes, with this site's run">

This is this site's own run:

```text
docs 3.x en ................... 46 pages, 542 search records, 127 KB index
docs 3.x ar ...................... 2 pages, 17 search records, 6 KB index
docs 2.x en .................... 17 pages, 76 search records, 10 KB index

INFO LaRecipe docs compiled.
```

The list of what it compiled is written to `bootstrap/cache/larecipe/optimized.php`. `larecipe:optimize` does not compile provenance; see [the repository source](../sources/repository.md#provenance-from-git).

<Artisan command="larecipe:optimize" />

</Accordion>

<Accordion title="What clear removes, and what it keeps">

`larecipe:clear` forgets the rendered pages and search records listed in `optimized.php`, then that list. It deletes `bootstrap/cache/larecipe/optimized.php` and nothing else in that folder, so the compiled provenance index, the live reference snapshot and the compiled hook patterns survive `optimize:clear`.

<Artisan command="larecipe:clear" />

</Accordion>

<Accordion title="After you upgrade LaRecipe">

Every cache key LaRecipe writes starts with a format version (`larecipe:v2:`), and rendered pages also carry the renderer's version. A release that changes what LaRecipe stores, or how it renders, moves them, so entries an older release wrote are never read: right after you upgrade LaRecipe, the cache starts cold and pages render again. Run `php artisan optimize` on that deploy, as you already do, and readers get warm pages from the first request. An entry in a shape LaRecipe does not expect is read as a miss, never an error.

</Accordion>

<Accordion title="Following renders with PageRendered">

`PageRendered` fires whenever an article is rendered because the cache did not have it, and on every request for a Markdown twin. See the [events reference](../reference/events.md#pagerendered).

</Accordion>

</AccordionGroup>

## Responses and shared caches

Each response also says how a browser, a proxy or a CDN may keep it: a public page served to a guest is cacheable for everyone, and a response that only one reader may see never reaches a shared cache. [Security](security.md#responses-and-shared-caches) has the header for every case. LaRecipe's own UI files are served with content-hashed names and a year-long immutable header; [deployment](deployment.md) shows how to publish them so your web server serves them instead.

## Next

- [Deployment](deployment.md): `optimize`, `larecipe:provenance` and `larecipe:cache:warm` in a deploy script.
- [Security](security.md): response headers and access on every surface.
- [Validation and doctor](validation.md): check the docs before they ship.
