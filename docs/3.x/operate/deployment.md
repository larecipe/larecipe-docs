---
title: Deployment
description: Deploy a LaRecipe site like any Laravel app with a four-line script, plus the extra steps for database sets, custom components and UI assets served by your web server.
sources:
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/OptimizeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PackageAssetController.php
  - vendor/larecipe/larecipe/src/Domains/Sources/SourcesServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/ProvenanceCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/CacheWarmCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Listeners/WarmChangedSet.php
  - vendor/larecipe/larecipe/src/Domains/Export/Commands/ExportCommand.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - package.json
  - vite.config.js
---

# Deployment

A LaRecipe site deploys like any Laravel app, because it is one. The docs ride in the same deploy as your code, with no separate docs project and no Node on the server. For docs that live in the repository, this is the whole script:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan larecipe:provenance
php artisan optimize
```

Add `php artisan larecipe:cache:warm` after `migrate` when a doc set uses the database, and `npm ci && npm run build` when you have custom components.

## What each step does

<Steps>

<Step title="Install dependencies">

LaRecipe is an ordinary Composer dependency. Its reading UI ships prebuilt inside the package, so there is nothing to compile for it.

</Step>

<Step title="Run migrations">

`migrate` creates the tables of a [database set](../sources/database.md) and applies the [docs migrations](../sources/docs-migrations.md) each pull request carries. An app whose docs all live in the repository gets no LaRecipe migrations.

</Step>

<Step title="Compile provenance">

The "Updated" line under each title comes from git. `larecipe:provenance` reads the history once and writes it to `bootstrap/cache/larecipe/`, so requests never run git. Run it where the full history is checked out. See [the repository source](../sources/repository.md) for what it records.

</Step>

<Step title="Optimize">

LaRecipe registers `larecipe:optimize` with Laravel's `optimize`, so this one command also renders every page of every repository set into the cache store, with the page lists, navigation and search records. Readers get warm pages from the first request.

</Step>

<Step title="Warm database sets">

```bash
php artisan larecipe:cache:warm
```

Only for database sets, after `migrate` applies new docs migrations. It warms every database set, or one with `--set=handbook`. With no database sets it says so and does nothing.

</Step>

<Step title="Build custom components">

```bash
npm ci
npm run build
```

Only when your app has [custom React components](../customize/custom-components.md). `@larecipe/vite` writes them to `public/build/larecipe/`, and LaRecipe loads that build on every docs page by itself. Node is needed for this build only, on your machine or in CI.

</Step>

</Steps>

## The environment

- **A persistent cache store.** Rendered pages, navigation and search records live in your cache store (`larecipe.cache.store`, or your default store). Any persistent store works: `redis`, `database`, `file`, `memcached`. A database set must have one, and `larecipe:validate` reports an error when it runs on `array` or `null`.
- **A queue worker for database sets.** When a page is published, LaRecipe queues a job that warms the changed set after the database transaction commits. Without a worker, the next reader pays for the cold page.

## Publish the UI assets

By default LaRecipe serves its UI files from `vendor` through PHP, at `/larecipe-assets/{build}/{file}`, with content-hashed names and a year-long immutable cache header. To let your web server or CDN serve them instead, publish them after every `composer install`:

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```

The files land in `public/vendor/larecipe`. LaRecipe points pages at them only while the published build matches the installed package, so a stale copy after `composer update` is ignored until you publish again.

## After the deploy

Check what agents get from the live app:

```bash
php artisan larecipe:doctor
```

It requests your docs through the app and grades Markdown twins, `llms.txt`, 404s, headers, redirects, `skill.md` and the MCP endpoint, with a fix for every finding. See [Validation and doctor](validation.md#larecipedoctor).

## Details

<AccordionGroup>

<Accordion title="Provenance in CI and shallow clones">

A shallow clone gives every page the same last commit. Without a compiled file, a page falls back to its file's modification time, which after a fresh checkout is the time of the deploy. If you build an artifact in CI and ship it, run `larecipe:provenance` in CI and ship `bootstrap/cache/larecipe/` with it.

`php artisan optimize` does not compile provenance, so keep its own step in the deploy. `php artisan optimize:clear` leaves the compiled file alone.

</Accordion>

<Accordion title="Optimize: routes, the cache store and zero-downtime deploys">

- **Route caching** includes LaRecipe's routes. Run `optimize` again after you change a set's `route.prefix` or `route.domain`.
- **Run it against the production cache store.** `larecipe:optimize` writes into the store your app uses for LaRecipe. On a build machine with a different store, the compiled pages land where production never reads them.
- **Zero-downtime deploys** are safe: cache keys come from page content, so compiling a new release never disturbs the release that is still serving.
- **After upgrading LaRecipe**, the cache starts cold: every cache key carries a format version, and a new release never reads what an older one wrote. This step warms it again before the first reader.

[Caching](caching.md) explains what is cached and why the keys never need purging.

</Accordion>

<Accordion title="Proxies, APP_URL and sessions">

- **Your real host and scheme.** Agent Markdown, `llms.txt`, the sitemap and canonical links carry absolute URLs, built from the request the way Laravel's `url()` builds them. Behind a load balancer or a proxy that ends TLS, trust it (`$middleware->trustProxies(at: '*')` in `bootstrap/app.php`, or the proxies you run), so those URLs say `https` and your domain.
- **`APP_URL`** is the URL console commands use, since they have no request: `larecipe:export` without `--base-url`, and `larecipe:doctor`, which requests your docs through the app.
- **Sessions on the docs routes.** Each set runs in the `web` middleware group by default (`route.middleware`), which page feedback and any form in a custom component rely on for the session and CSRF protection.

</Accordion>

</AccordionGroup>

## Next

- [Validation and doctor](validation.md): check the docs before they ship and the site after.
- [Caching](caching.md): go deeper into what is cached and when it changes.
- [Security](security.md): what LaRecipe does to keep content and private docs safe.
