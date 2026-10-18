---
title: Configuration basics
description: Where LaRecipe's settings live, the few that most apps change (the URL, who may read, where pages are stored), and how to add a second doc set.
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

# Configuration basics

Most apps change nothing at first: the docs live in `docs/`, are served at `/docs`, and anyone may read them. When you want something else, publish the config file:

```bash
php artisan vendor:publish --tag=larecipe-config
```

Then edit your doc set's entry in `config/larecipe.php`. These are the three settings most apps touch:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'route' => ['prefix' => 'docs', 'domain' => null, 'middleware' => ['web']],
        'source' => ['driver' => 'repository', 'path' => base_path('docs')],
        'access' => ['mode' => 'public'],
    ],
],
```

| Setting | What it decides | Values |
| --- | --- | --- |
| `route.prefix` | The URL the docs live under | `docs` by default (`LARECIPE_PREFIX`). `''` puts the docs at the domain root. |
| `source.driver` | Where the pages are stored | `repository` (Markdown files in your repo, recommended) or `database` |
| `access.mode` | Who may read | `public` (everyone), `auth` (anyone signed in) or `gate` (your `viewLarecipe` gate) |

Laravel merges your file over the package's defaults one top-level key at a time, so a key you leave out keeps its default.

## Settings and content live apart

`config/larecipe.php` holds how your app serves the docs. What the docs say and how they look travels with the content, next to the pages:

| File | Holds |
| --- | --- |
| `config/larecipe.php` | Doc sets, routes, the source, access, review mode, cache, search, MCP, agent surfaces, AI |
| `docs/larecipe.json` | The name, logo, theme, versions, languages, navbar, redirects and analytics. See the [manifest reference](../reference/manifest.md). |
| `navigation.json` | One version's sidebar. See [navigation](../writing/navigation.md). |
| Frontmatter | One page's title, description, sources and the like. See [project structure](project-structure.md). |

So your coding agent can edit the docs without touching your config. Secrets go in neither: a provider key belongs in `.env`, read by your config.

## Add a second doc set

A doc set is one docs site inside your app, with its own URL, content and agent surfaces. Add another when a different audience needs a different site, such as internal runbooks next to public docs:

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

`default` names the set that artisan commands and the local MCP server use when you pass no `--set` (`docs` unless `LARECIPE_DEFAULT_SET` says otherwise). [Gate blocks and private docs](../writing/gate-blocks.md) explains the access modes and the gates.

## Everything else

Each other group in the file has its own page:

| Key | Controls | Read |
| --- | --- | --- |
| `review` | Whether your agent asks before it commits docs changes | [Review modes](../keeping-current/review-modes.md) |
| `search` | The search driver and the index budget | [Search](../operate/search.md) |
| `mcp` | The MCP endpoint for agents and the local server | [The MCP server](../agents/mcp.md) |
| `agents` | `llms.txt`, `llms-full.txt`, `skill.md` and the sitemap | [Agent surfaces](../agents/surfaces.md) |
| `ai` | Ask AI and `larecipe:sync`, on your own provider key | [Ask AI](../agents/ask-ai.md) |
| `cache` | The cache store for rendered pages | [Deployment](../operate/deployment.md) |
| `hooks`, `instructions` | The pre-push check and the `AGENTS.md` block | [The pre-push hook](../keeping-current/pre-push-hook.md) |

The [configuration reference](../reference/configuration.md) lists every key, its default and its environment variable.

## Details

<AccordionGroup>

<Accordion title="Mount the docs at the domain root">

A prefix of `''` makes the set own the domain root. This site works that way: [larecipe.saleem.dev](https://larecipe.saleem.dev) opens on the Introduction page, with no separate homepage. Here is its entry, from this app's own `config/larecipe.php`:

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

- `/` renders the landing page of the default version directly. Other URLs without a version redirect into the default version.
- Your own routes win. LaRecipe registers its routes after your app's, and a root-mounted set after every other set, so `/newsletter` on this site still reaches the app's own route. A docs page whose path matches one of your routes cannot be reached, so keep the paths apart.
- Remove Laravel's default `/` welcome route from `routes/web.php`, or it will answer instead of your docs.
- The catch-all leaves `/larecipe-assets/` and `/.well-known/` alone.

</Accordion>

<Accordion title="Routes, domains and middleware">

`route.domain` pins a set to one domain: a set with `'domain' => 'docs.example.com'` answers only on that host, and its MCP endpoint does too. `route.middleware` is the middleware group around every docs route (`['web']`).

Every docs URL hangs off the prefix: pages, their `.md` twins, `llms.txt`, `skill.md`, `sitemap.xml`, assets and the MCP endpoint. All of them run through the middleware group except the MCP endpoint, so MCP clients can post without a session or CSRF token.

</Accordion>

<Accordion title="The two sources">

- `repository` (the default, recommended): Markdown files in your repo, at `source.path` (`base_path('docs')`). Changes ride in your pull requests as ordinary files. See [the repository source](../sources/repository.md).
- `database`: pages in your database, always behind a cache. It also takes `source.connection`, `source.cache` (a persistent store) and `source.disk` (for assets). Changes made in a pull request ship as [docs migrations](../sources/docs-migrations.md). See [the database source](../sources/database.md).

A set has one driver for all its versions and languages, and you can [switch](../sources/switching.md) later.

</Accordion>

<Accordion title="Override a default for one set">

Most keys outside `sets` are defaults for every set. A set overrides one by repeating it in its own entry. Its values merge over the top-level ones key by key, so you only write what differs:

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

The [configuration reference](../reference/configuration.md) says, key by key, which ones a set may override.

</Accordion>

</AccordionGroup>

## Next

- [Project structure](project-structure.md): what lives in the docs folder.
- [Configuration reference](../reference/configuration.md): every key, with its type and default.
- [Gate blocks and private docs](../writing/gate-blocks.md): access modes and gates in full.
