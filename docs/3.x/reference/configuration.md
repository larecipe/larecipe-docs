---
title: Configuration
description: Every key in config/larecipe.php with its type, default and meaning, which keys a doc set may override, and the environment variables the file reads.
sources:
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DatabaseSourceFactory.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Ask/**
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SyncSettings.php
  - vendor/larecipe/larecipe/src/Domains/References/ReferencesServiceProvider.php
---

# Configuration

`config/larecipe.php` holds the infrastructure of your docs: where each doc set is mounted, where its content lives, who may read it, and how caching, search, AI, MCP and the agent surfaces behave. Presentation (name, theme, versions, languages, navigation) lives next to the docs in [larecipe.json](manifest.md), never here.

`larecipe:install` publishes the file when you choose anything other than the defaults. To publish it yourself:

```bash
php artisan vendor:publish --tag=larecipe-config
```

The tables follow the order of the file. "Per set" says whether a doc set can give the key its own value; see [Override a key per set](#override-a-key-per-set).

## Default doc set

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `default` | string | `docs` (`LARECIPE_DEFAULT_SET`) | no | The set artisan commands and the local MCP server use when none is given. |

## Doc sets

`sets` maps a set key to its settings. Each set is its own docs site in the app, with its own route, source, `larecipe.json` and agent surfaces. The package ships one set, `docs`.

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `sets.{key}.route.prefix` | string | `docs` (`LARECIPE_PREFIX`) | The URL prefix. `''` mounts the set at the domain root. Without the key, the set key is used. |
| `sets.{key}.route.domain` | string or null | `null` | Serve the set only on this domain. |
| `sets.{key}.route.middleware` | list of strings | `['web']` | Middleware for the set's pages. |
| `sets.{key}.source.driver` | string | `repository` | `repository` (Markdown files in this repo) or `database`. |
| `sets.{key}.source.path` | string | `base_path('docs')` | The docs folder of a repository set. Without the key, `base_path('{key}')`. |
| `sets.{key}.source.connection` | string or null | not set | Database sets: the database connection. `null` uses the default connection. |
| `sets.{key}.source.cache` | string or null | not set | Database sets: the cache store in front of the database. Falls back to `cache.store`, then the app's default store. `array` and `null` stores are refused outside tests. |
| `sets.{key}.source.disk` | string or null | not set | Database sets: the filesystem disk for assets. `null` uses the default disk. |
| `sets.{key}.access.mode` | string | `public` | `public`: anyone. `auth`: any signed-in user. `gate`: every page is checked by the `viewLarecipe` gate. Private versions and pages need `viewLarecipe` in every mode. |
| `sets.{key}.integrations.analytics` | object | not set | GA4, Plausible, Fathom, PostHog or a custom `https` script on every page of the set, with the same keys as in [larecipe.json](manifest.md#integrationsanalytics). The only place a database set reads them from, and the only place a self-hosted Plausible, Fathom or PostHog host counts. For a repository set it wins per tool over `larecipe.json`. See [analytics](../operate/analytics.md). |

## Defaults for every set

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `review` | string | `required` (`LARECIPE_REVIEW`) | yes | `required`: changes an agent makes outside a pull request, such as through the web MCP author tools, are drafts until a person publishes them. `auto`: they publish at once, still revisioned. Also written into the `AGENTS.md` block. |
| `cache.store` | string or null | `null` (`LARECIPE_CACHE_STORE`) | database source only | The cache store for rendered pages, page lists, navigation and search. `null` uses the app's default store. |
| `search.driver` | string | `client` | yes | `client`: MiniSearch in the browser. `server`: the PHP ranker. Any other value names a driver registered with `LaRecipe::extendSearch()`. |
| `search.budget_kb` | integer | `300` | yes | A client index larger than this, gzipped, is reported. |
| `snippets.root` | string or null | `null` | yes | The folder snippet includes (`<<< @/...`) resolve in. `null` means the app's base path for repository sets; database sets include nothing unless they set their own. Dotfiles, keys, `storage/` and `auth.json` are never included. |
| `migrations.path` | string | `database_path('migrations/docs')` | no | Where docs migrations are written and loaded from. |
| `migrations.skip_in_tests` | boolean | `false` | no | Skip docs migrations when the app runs its tests. |

## Database source models

The Eloquent models behind a database set's tables. Point a key at your own subclass to add scopes, tenancy or observers.

| Key | Type | Default |
| --- | --- | --- |
| `models.page` | class name | `LaRecipe\Domains\Sources\Models\Page` |
| `models.navigation` | class name | `LaRecipe\Domains\Sources\Models\Navigation` |
| `models.manifest` | class name | `LaRecipe\Domains\Sources\Models\Manifest` |
| `models.revision` | class name | `LaRecipe\Domains\Sources\Models\Revision` |
| `models.asset` | class name | `LaRecipe\Domains\Sources\Models\Asset` |
| `models.docs_migration` | class name | `LaRecipe\Domains\Sources\Models\DocsMigrationRecord` |

These keys are read from the top level only.

## AI

Ask AI and `larecipe:sync` need `laravel/ai` installed. Without it, the `ai.ask` keys have no effect and `larecipe:sync` stops with the install steps.

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `ai.ask.enabled` | boolean | `false` (`LARECIPE_ASK`) | yes | Turns on Ask AI. When the app defines an `askLarecipe` gate, only readers it allows may ask; without one, every reader may. |
| `ai.ask.provider` | string, list or null | `null` (`LARECIPE_AI_PROVIDER`) | yes | The `laravel/ai` provider. `null` uses its default. A list is a failover order, tried when a provider fails: `['anthropic', 'openai']`, or `['anthropic' => '{model}', 'openai' => '{model}']`. |
| `ai.ask.model` | string or null | `null` (`LARECIPE_AI_MODEL`) | yes | The model. `null` uses the provider's default. Applies to a single provider only. |
| `ai.ask.max_steps` | integer | `6` | yes | Steps the assistant may take for one answer. |
| `ai.ask.rate_limit` | string | `'10,1'` | no | `requests,minutes` per reader (user, else IP), counted separately for each set. |
| `ai.ask.daily_budget` | integer or null | `500` | yes | Answers per set per day. `null` means no cap. |
| `ai.ask.max_messages` | integer | `20` | yes | Messages one conversation may hold. |
| `ai.ask.max_question_length` | integer | `2000` | yes | The longest question, in characters, this one and the earlier ones. |
| `ai.ask.max_history_length` | integer | `20000` | yes | The most characters the earlier turns of a conversation may hold, all together. |
| `ai.sync.provider` | string or null | `null` (`LARECIPE_AI_PROVIDER`) | no | The `laravel/ai` provider for [`larecipe:sync`](../keeping-current/sync.md). `null` uses `laravel/ai`'s default. `--provider` overrides it for one run. |
| `ai.sync.model` | string or null | `null` (`LARECIPE_AI_MODEL`) | no | The model. `null` uses the provider's default. `--model` overrides it for one run. |
| `ai.sync.max_pages` | integer | `20` | no | Pages one run may change. `--max-pages` overrides it for one run. |
| `ai.sync.max_steps` | integer | `30` | no | Steps one run may take. `--max-steps` overrides it for one run. |
| `ai.sync.timeout` | integer | `120` | no | Seconds per provider request. |
| `ai.sync.remote_token` | string or null | `null` (`LARECIPE_SYNC_TOKEN`) | no | The token a `--remote` run sends to a production set's MCP endpoint, for a user its `manageLarecipe` gate allows. |
| `ai.sync.deny` | list of strings | `[]` | no | Paths or globs the docs agent may never read, added to the built-in list (dotfiles, `config/`, `storage/`, `vendor/`, keys and the rest). |
| `ai.sync.queue` | string or null | `null` (`LARECIPE_SYNC_QUEUE`) | no | The queue for `--queue` runs. `null` uses the default queue. |

## MCP

One web endpoint per set, plus one local stdio server for every set. See [MCP tools](mcp-tools.md).

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `mcp.web.enabled` | boolean | `true` | yes | Serve the set's MCP endpoint. |
| `mcp.web.path` | string | `mcp` | yes | The endpoint's path under the set's prefix: `/docs/mcp`, or `/mcp` for a root-mounted set. |
| `mcp.web.author_tools` | boolean | `false` | yes | Expose the author tools on the web. Only for a database set, a signed-in caller and the `manageLarecipe` gate. |
| `mcp.web.middleware` | list of strings | `[]` | yes | Middleware that authenticates private sets: `['auth:sanctum']`, or `['auth:api']` with `Mcp::oauthRoutes()`. Access is checked again on every tool call. |
| `mcp.web.rate_limit` | string | `'60,1'` | yes | `requests,minutes` per caller (user, else IP). |
| `mcp.local` | boolean | `true` | no | Register the local stdio server that `php artisan larecipe:mcp` starts. |

## Agent surfaces

See [agent surfaces](../agents/surfaces.md).

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `agents.markdown` | boolean | `true` | not read yet | Present in the file, but Markdown twins are always served in this release. |
| `agents.llms_txt` | boolean | `true` | yes | `llms.txt` and its path-scoped files. |
| `agents.llms_full` | boolean | `true` | yes | `llms-full.txt`. |
| `agents.skill_md` | boolean | `true` | yes | `skill.md` and the hand-written `skills/` files. |
| `agents.well_known_skills` | boolean | `true` | yes | `/.well-known/agent-skills/index.json`, listed when at least one set has both skill keys on. |
| `agents.sitemap` | boolean | `true` | yes | `sitemap.xml`. |
| `agents.ask_endpoint` | boolean | `false` | yes | The `GET /ask?q=` search for agents without MCP. |
| `agents.llms_txt_limit` | integer | `50000` | yes | Characters per `llms.txt` file. A larger version splits into one file per navigation group. |
| `agents.llms_full_limit` | integer | `400000` | yes | Characters per `llms-full.txt` file before it splits by group. |
| `agents.markdown_budget` | integer | `50000` | yes | `larecipe:validate` warns (`markdown.size`) when a page's agent Markdown is larger. |

## Feedback

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `feedback.enabled` | boolean | `true` | yes | The "Was this page helpful?" widget, its endpoint and the `submit_feedback` MCP tool. Both fire `FeedbackSubmitted`; nothing is stored. |
| `feedback.rate_limit` | string | `'20,1'` | no | `requests,minutes` per reader for the widget's endpoint. |

## Keeping docs in sync

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `hooks.enabled` | boolean | `true` unless `LARECIPE_HOOKS=off` | no | The pre-push docs check in agent hooks. |
| `hooks.push_commands` | list of strings | `git push`, `gh pr create`, `glab mr create`, `hub pull-request` | no | Shell commands that trigger the check. `larecipe:agents` compiles them to `bootstrap/cache/larecipe/hooks.php`. |

## Instructions for coding agents

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `instructions.file` | string | `AGENTS.md` | no | The file that holds the LaRecipe block. |
| `instructions.index` | boolean | `true` | no | Include the docs index in the block. |
| `instructions.index_max_bytes` | integer | `8192` | no | The index's size limit (at least 1024). |
| `instructions.default_branch` | string or null | `null` (`LARECIPE_DEFAULT_BRANCH`) | no | The branch changes are compared with. `null` detects it from `origin/HEAD`. |

## Live references

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `references.routes.allow` | list of strings or null | `null` | no | Route names or URIs (with `*`) `<Route>` may describe. `null` means every route your app defines, leaving out the routes packages in `vendor/` register (LaRecipe's own stay). |
| `references.models.allow` | list of strings | `['App\\Models\\*']` | no | Namespaces (with `*`) or exact classes `<Model>` may describe. Nothing else. |
| `references.events.namespaces` | list of strings | `['App\\Events']` | no | Where `<Event>` looks up short names. |

See [live references](../writing/live-references.md).

## UI

| Key | Type | Default | Per set | Meaning |
| --- | --- | --- | --- | --- |
| `ui.dev_server` | string or null | `null` (`LARECIPE_UI_DEV`) | no | Load the reader UI from a Vite dev server at this URL, for working on the UI itself. |

## Override a key per set

A set's own settings sit next to `route`, `source` and `access`. LaRecipe merges the top-level `review`, `cache`, `search`, `ai`, `mcp`, `agents`, `references` and `migrations` groups into every set, and the set's own value wins. `feedback.enabled` and `snippets.root` are also read from the set first.

```php
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
        'review' => 'auto',
        'search' => ['driver' => 'server'],
        'agents' => ['sitemap' => false, 'llms_full' => false],
        'mcp' => ['web' => ['middleware' => ['auth:sanctum']]],
    ],
],
```

Some keys inside the merged groups are still read from the top level only, so a set's value for them has no effect: `ai.ask.rate_limit`, `mcp.local`, `references.*`, `migrations.*`, and `cache.store` for everything except a database set's own source cache. The "Per set" column in each table says which.

## Environment variables

The file reads these variables. Their values belong in `.env`, never in the docs.

- `LARECIPE_DEFAULT_SET`
- `LARECIPE_PREFIX`
- `LARECIPE_REVIEW`
- `LARECIPE_CACHE_STORE`
- `LARECIPE_ASK`
- `LARECIPE_AI_PROVIDER`
- `LARECIPE_AI_MODEL`
- `LARECIPE_SYNC_TOKEN`
- `LARECIPE_SYNC_QUEUE`
- `LARECIPE_HOOKS` (also read by `larecipe:hook` directly, so `LARECIPE_HOOKS=off` turns the check off for one session)
- `LARECIPE_DEFAULT_BRANCH`
- `LARECIPE_UI_DEV`
