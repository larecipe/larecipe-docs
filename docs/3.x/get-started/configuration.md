---
title: Configuration
description: What goes in config/larecipe.php and what goes next to your docs, plus a tour of the settings most apps change, from doc sets and routes to access, search, MCP and AI.
sources:
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/src/Domains/Content/ContentServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Laravel/RouteRegistrar.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
  - config/larecipe.php
---

# Configuration

LaRecipe keeps two kinds of settings in two places. Infrastructure, meaning how the app serves the docs, lives in `config/larecipe.php` with the rest of your Laravel config. Content and presentation, meaning what the docs say and how they look, live next to the docs in `larecipe.json`, `navigation.json` and each page's frontmatter.

This page explains that split, then tours the settings in `config/larecipe.php` that most apps touch. The [configuration reference](../reference/configuration.md) lists every key.

## The two-files rule

| File | Holds | Lives |
| --- | --- | --- |
| `config/larecipe.php` | Doc sets, routes and domains, the source driver, access, review mode, cache, search, MCP, agent surfaces, AI | With your app's config, deployed with your code |
| `larecipe.json` | The set's name, logo, theme, versions, languages, navbar, redirects, insert points, analytics, agent descriptions | At the root of the docs (`docs/larecipe.json`) |
| `navigation.json` | The sidebar of one version | In each version folder |
| Frontmatter | One page's title, description, sources, order, visibility and the like | At the top of each page |

The line is simple: if it changes how the app serves the docs, it is config; if it changes what readers see, it travels with the content. That is what lets your coding agent edit the docs without touching your config, and what lets a doc set [move between the repository and the database](../sources/switching.md) without a config change.

Secrets go in neither. A provider key belongs in `.env`, read by your config; the docs files are read by agents and served to readers, so they never hold one.

The [manifest reference](../reference/manifest.md), [navigation](../writing/navigation.md) and the [frontmatter reference](../reference/frontmatter.md) cover the content side.

## Publish the config

The installer publishes `config/larecipe.php` only when you pick something other than the defaults. To publish it yourself:

```bash
php artisan vendor:publish --tag=larecipe-config
```

Laravel merges your file over the package's defaults one top-level key at a time, so a top-level key you leave out keeps its default.

## Doc sets

A doc set is one docs site inside your app, with its own route, its own content and its own agent surfaces. Most apps have one, called `docs`, and never think about sets again. This site has one too. Here is its entry, from this app's own `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'route' => [
            'prefix' => '',   // larecipe.saleem.dev opens on the docs: the set owns the domain root
            'domain' => null,
            'middleware' => ['web'],
        ],
        'source' => [
            'driver' => 'repository',
            'path' => base_path('docs'),
        ],
        'access' => [
            'mode' => 'public',
        ],
    ],
],
```

Each set has three parts of its own: `route`, `source` and `access`. Most other keys in the file are defaults for every set, which a set may override (see [per-set overrides](#per-set-overrides)).

Add a second set when a different audience needs a different site, such as internal runbooks next to public docs:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // ...
    ],

    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
        'mcp' => ['web' => ['enabled' => false]],
    ],
],
```

`default` names the set that artisan commands and the local MCP server use when you do not pass `--set` (`LARECIPE_DEFAULT_SET`, `docs` by default).

## Routes and domains

`route.prefix` is the path the set is served under (`LARECIPE_PREFIX`, `docs` by default), `route.domain` pins it to one domain, and `route.middleware` is the middleware group around every docs route (`['web']`). A set with `'domain' => 'docs.example.com'` answers only on that host, and its MCP endpoint does too.

Every docs URL hangs off the prefix: pages, their `.md` twins, `llms.txt`, `skill.md`, `sitemap.xml`, assets and the MCP endpoint. All of them run through the middleware group except the MCP endpoint, so MCP clients can post without a session or CSRF token. [Project structure](project-structure.md) shows how files become URLs.

### Mount the docs at the domain root

A prefix of `''` makes the set own the domain root. This site works that way: [larecipe.saleem.dev](https://larecipe.saleem.dev) opens on the Introduction page, with no separate homepage.

- `/` renders the landing page of the default version directly. Other URLs without a version redirect into the default version.
- Your own routes win. LaRecipe registers its routes after your app's, and a root-mounted set after every other set, so `/newsletter` on this site still reaches the app's own route. A docs page whose path matches one of your routes cannot be reached, so keep the paths apart.
- Remove Laravel's default `/` welcome route from `routes/web.php`, or it will answer instead of your docs.
- The catch-all leaves `/larecipe-assets/` and `/.well-known/` alone.

## The source

`source.driver` says where the pages live:

- `repository` (the default, recommended): Markdown files in your repo, at `source.path` (`base_path('docs')`). Changes ride in your pull requests as ordinary files. See [the repository source](../sources/repository.md).
- `database`: pages in your database, always behind a cache. It also takes `source.connection`, `source.cache` (a persistent store) and `source.disk` (for assets). Changes made in a pull request ship as [docs migrations](../sources/docs-migrations.md). See [the database source](../sources/database.md).

A set has one driver for all its versions and languages.

## Access

`access.mode` decides who may read a set at all:

| Mode | Who may read |
| --- | --- |
| `public` | Everyone, guests included. The default. |
| `auth` | Anyone signed in with your default guard. Guests get your app's usual unauthenticated response, usually a redirect to the login page. |
| `gate` | Readers your `viewLarecipe` gate allows, checked for the set and for every page. |

Private versions and private pages need the gate in any mode, and every surface (HTML, Markdown, search, `llms.txt`, MCP, Ask AI) follows the same rules. [Gate blocks and private docs](../writing/gate-blocks.md) has the gates, their arguments and the per-block `<Can>` component.

## Review

`review` is `required` (the default) or `auto` (`LARECIPE_REVIEW`). It tells coding agents, through the instructions in `AGENTS.md`, whether to show you the docs diff before committing, and it decides whether changes an agent makes outside a pull request wait as drafts or publish at once. See [review modes](../keeping-current/review-modes.md).

## Cache

`cache.store` names the cache store for rendered pages, page lists, navigation and search indexes (`LARECIPE_CACHE_STORE`; your default store when empty). See [caching](../operate/caching.md) for what is cached and how to warm it on deploy.

## Search

`search.driver` is `client` by default: readers search an index in the browser, with no service to run. `server` ranks results in PHP instead, and any other name is a custom driver you register with `LaRecipe::extendSearch()`. `search.budget_kb` (300) is the gzipped size past which a client index is reported as too big. See [search](../operate/search.md).

## MCP

Every set gets a read-only MCP endpoint for agents, at `/{prefix}/mcp` (on this site, `/mcp`), plus one local server for your own coding agent (`php artisan larecipe:mcp`).

| Key | Default | Controls |
| --- | --- | --- |
| `mcp.web.enabled` | `true` | The set's web endpoint |
| `mcp.web.path` | `mcp` | Its path inside the set |
| `mcp.web.middleware` | `[]` | Authentication for private sets, such as `['auth:sanctum']` |
| `mcp.web.rate_limit` | `60,1` | Requests and minutes, per caller: 60 a minute |
| `mcp.web.author_tools` | `false` | Remote write tools, for database sets only |
| `mcp.local` | `true` | The local stdio server |

See [the MCP server](../agents/mcp.md).

## Agent surfaces

The `agents` keys switch agent surfaces on or off. All are on by default except the search endpoint:

| Key | Controls |
| --- | --- |
| `agents.llms_txt` | `llms.txt` and its scoped files |
| `agents.llms_full` | `llms-full.txt` |
| `agents.skill_md` | `skill.md` and hand-written skills |
| `agents.well_known_skills` | `/.well-known/agent-skills/index.json` |
| `agents.sitemap` | `sitemap.xml` |
| `agents.ask_endpoint` | The `/ask?q=` search for agents without MCP (off) |

Every page's `.md` twin is always served; it is the same content as the HTML page. Three size limits sit next to the switches: `llms_txt_limit`, `llms_full_limit` and `markdown_budget`. [Agent surfaces](../agents/surfaces.md) explains each one.

## AI

Ask AI answers readers' questions from your docs, with citations. It is off by default and needs `laravel/ai` and a provider key of your own:

```php title="config/larecipe.php"
'ai' => [
    'ask' => [
        'enabled' => env('LARECIPE_ASK', false),
        'provider' => env('LARECIPE_AI_PROVIDER'),
        'model' => env('LARECIPE_AI_MODEL'),
        'max_steps' => 6,
        'rate_limit' => '10,1',
        'daily_budget' => 500,
        'max_messages' => 20,
        'max_question_length' => 2000,
        'max_history_length' => 20000,
    ],
],
```

`rate_limit` is requests and minutes for each reader (`10,1` allows ten questions a minute), and `daily_budget` caps answers per set per day (`null` for no cap). `provider` also takes a failover list, tried in order when a provider fails. See [Ask AI](../agents/ask-ai.md). The `ai.sync` keys configure [`larecipe:sync`](../keeping-current/sync.md), the docs agent you run on demand, on the same provider unless you set them apart.

## Everything else

The file holds a few more groups, each covered on its own page:

- `snippets.root`: where [snippet includes](../writing/code-blocks.md) may read from.
- `feedback`: the "Was this page helpful?" widget and its rate limit.
- `hooks` and `instructions`: the [pre-push check](../keeping-current/pre-push-hook.md) and the [`AGENTS.md` block](../keeping-current/agent-instructions.md).
- `references`: what [live references](../writing/live-references.md) may describe.
- `migrations` and `models`: [docs migrations](../sources/docs-migrations.md) and the tables of the database source.
- `ui.dev_server`: loads the reader UI from a Vite dev server, for work on LaRecipe itself.

## Per-set overrides

A set can override a default by repeating the key in its own entry. Its values merge over the top-level ones key by key, so you only write what differs:

```php title="config/larecipe.php"
'sets' => [
    'handbook' => [
        // route, source, access...
        'review' => 'auto',
        'search' => ['driver' => 'server'],
        'ai' => ['ask' => ['enabled' => true]],
        'agents' => ['llms_full' => false],
    ],
],
```

Sets read `review`, `search`, `ai`, `mcp`, `agents` and `migrations` this way, plus `feedback.enabled` and `snippets.root`. A set's `cache.store` picks the store for a database source only, the same as `source.cache`; rendered pages always use the top-level store. Ask AI's `rate_limit` and the `references` allowlists apply to the whole app.

## Environment variables

| Variable | Sets |
| --- | --- |
| `LARECIPE_DEFAULT_SET` | `default` |
| `LARECIPE_PREFIX` | The `docs` set's `route.prefix` |
| `LARECIPE_REVIEW` | `review` |
| `LARECIPE_CACHE_STORE` | `cache.store` |
| `LARECIPE_ASK` | `ai.ask.enabled` |
| `LARECIPE_AI_PROVIDER`, `LARECIPE_AI_MODEL` | The AI provider and model |
| `LARECIPE_SYNC_TOKEN`, `LARECIPE_SYNC_QUEUE` | The token for `larecipe:sync --remote`, and the queue for `--queue` runs |
| `LARECIPE_HOOKS` | `off` turns the pre-push check off for a session |
| `LARECIPE_DEFAULT_BRANCH` | The branch the agent instructions compare against (detected from `origin/HEAD` when empty) |

## Next

- [Project structure](project-structure.md): what lives in the docs folder.
- [Configuration reference](../reference/configuration.md): every key, with its type and default.
- [larecipe.json reference](../reference/manifest.md): the content side of the two-files rule.
