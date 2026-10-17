---
title: Deployment
description: Deploy a LaRecipe site like any Laravel app, with the few steps that make the docs fast and correct from the first request.
sources:
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/OptimizeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
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

A LaRecipe site deploys like any Laravel app, because it is one. The docs ride along in the same deploy as your code: no separate docs project, no second server, and no Node on the server. A few steps make the first request fast and the "Updated" lines right.

## The deploy script

For an app whose docs live in the repository:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan larecipe:provenance
php artisan optimize
```

Add `php artisan larecipe:cache:warm` after `migrate` when a doc set uses the database, and `npm ci && npm run build` when you have custom components. Each step is below.

<Steps>

<Step title="Install dependencies">

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

LaRecipe is an ordinary Composer dependency. Its reading UI ships prebuilt inside the package, so there is nothing to compile for it.

</Step>

<Step title="Run migrations">

```bash
php artisan migrate --force
```

A doc set on the database source keeps its pages in tables that LaRecipe's migrations create, and the [docs migrations](../sources/docs-migrations.md) in `database/migrations/docs/` carry the content changes from each pull request. `migrate` applies both. An app whose docs all live in the repository gets no LaRecipe migrations at all.

</Step>

<Step title="Compile provenance where git history exists">

```bash
php artisan larecipe:provenance
```

The "Updated" line under each title, with its author, agent and pull request, comes from git. This command reads the history once and writes it to `bootstrap/cache/larecipe/provenance-{set}.php`, so requests never run git.

Run it where the full history is. A shallow clone gives every page the same last commit, and without a compiled file a page falls back to its file's modification time, which after a fresh checkout is the time of the deploy. If you build an artifact in CI and ship it, run the command in CI and ship `bootstrap/cache/larecipe/` with it.

`php artisan optimize` does not compile provenance, and `php artisan optimize:clear` deletes it, so run this step after any `optimize:clear`.

</Step>

<Step title="Optimize">

```bash
php artisan optimize
```

LaRecipe registers `larecipe:optimize` with Laravel's `optimize`, so this one command caches your config, routes, views and events, and also renders every page of every repository set into the cache store, with the page lists, navigation and search records. See [Caching and optimize](caching.md) for what it compiles and why the keys never need purging.

- **Route caching** includes LaRecipe's routes. Run `optimize` again after you change a set's `route.prefix` or `route.domain`.
- **Run it against the production cache store.** `larecipe:optimize` writes into the store your app uses for LaRecipe (`larecipe.cache.store`, or your default store). On a build machine with a different store, the compiled pages land where production never reads them.
- **Zero-downtime deploys** are safe: cache keys come from page content, so compiling a new release never disturbs the release that is still serving.

</Step>

<Step title="Warm database sets">

```bash
php artisan larecipe:cache:warm
```

After `migrate` applies new docs migrations, this warms every database set, so the first readers get cached pages. Use `--set=handbook` for one set. With no database sets it says so and does nothing.

</Step>

<Step title="Build custom components">

```bash
npm ci
npm run build
```

Only when your app has [custom React components](../customize/custom-components.md). `@larecipe/vite` writes them to `public/build/larecipe/`, and LaRecipe finds that build and loads it on every docs page by itself. Node is needed for this build only, on your machine or in CI; the server only serves the files.

</Step>

</Steps>

## The environment

- **A persistent cache store.** Rendered pages, navigation and search records live in your cache store. Any persistent store works: `redis`, `database`, `file`, `memcached`. A database set must have one, and `larecipe:validate` reports an error when it runs on `array` or `null`. See [Caching and optimize](caching.md).
- **A queue worker for database sets.** When a page is published, LaRecipe queues a job that warms the changed set after the database transaction commits. Without a worker, the next reader pays for the cold page instead.
- **Your real host and scheme.** Agent Markdown, `llms.txt`, the sitemap and canonical links carry absolute URLs, built from the request the way Laravel's `url()` builds them. Behind a load balancer or a proxy that ends TLS, trust it (`$middleware->trustProxies(at: '*')` in `bootstrap/app.php`, or the proxies you run), so those URLs say `https` and your domain.
- **`APP_URL`** is the URL console commands use, since they have no request: `larecipe:export` without `--base-url`, and `larecipe:doctor`, which requests your docs through the app.
- **Sessions on the docs routes.** Each set runs in the `web` middleware group by default (`route.middleware`), which page feedback and any form in a custom component rely on for the session and CSRF protection.

## UI assets

By default LaRecipe serves its UI files from `vendor` through PHP, at `/larecipe-assets/{build}/{file}`, with content-hashed names and a year-long immutable cache header. To let your web server or CDN serve them instead, publish them after every `composer install`:

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```

LaRecipe uses the published copy only while it matches the installed package, so a stale copy is ignored rather than served. See [Caching and optimize](caching.md).

## After the deploy

Check what agents get from the live app:

```bash
php artisan larecipe:doctor
```

It requests your docs through the app and grades Markdown twins, `llms.txt`, 404s, headers, redirects, `skill.md` and the MCP endpoint, with a fix for every finding. See [Validation and doctor](validation.md).

## Next

- [Caching and optimize](caching.md): what is cached, and when it changes.
- [Validation and doctor](validation.md): check the docs before they ship and the site after.
- [Security](security.md): what LaRecipe does to keep content and private docs safe.
