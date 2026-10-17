---
title: Analytics and events
description: Add GA4, Plausible, Fathom, PostHog or your own script from larecipe.json, collect page feedback, and forward every request, agents included, to your own tools with Laravel events.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/AnalyticsTags.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/RequestClassifier.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/TrafficKind.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Events/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/FeedbackController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/Tools/SubmitFeedbackTool.php
  - vendor/larecipe/larecipe/ui/src/regions/PageFooter.tsx
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Analytics and events

LaRecipe has no dashboards and stores no traffic. It feeds the tools you already use, in two ways:

- **Script tags** for GA4, Plausible, Fathom, PostHog or a script of your own, configured in `larecipe.json`. They see readers whose browsers run JavaScript.
- **Laravel events** for every page, Markdown twin, `llms.txt`, skill and MCP call LaRecipe serves, each with the kind of request that made it: a person, a crawler or an agent. Agents never run your analytics script, so events are the only way to see them.

This site runs no analytics integration. Its `larecipe.json` has no `integrations` key.

## Analytics integrations

Add the tool under `integrations.analytics` in `larecipe.json`. LaRecipe prints its tags into the head of every page of the set:

```json title="docs/larecipe.json"
{
  "integrations": {
    "analytics": {
      "plausible": { "domain": "docs.acme.dev" }
    }
  }
}
```

| Tool | Keys | Notes |
| --- | --- | --- |
| `ga4` | `measurementId` | `G-` followed by 4 to 20 capital letters or digits. Loads Google's `gtag.js`. |
| `plausible` | `domain`, `src` | `domain` may list several, separated by commas. `src` defaults to `https://plausible.io/js/script.js`. |
| `fathom` | `siteId`, `src` | `siteId` is 4 to 20 capital letters or digits. `src` defaults to `https://cdn.usefathom.com/script.js`. |
| `posthog` | `apiKey`, `host` | `apiKey` starts with `phc_`. `host` defaults to `https://us.i.posthog.com`. |
| `custom` | `src`, `defer`, `async`, `attributes` | Any script, by `https` URL. Deferred unless you set `defer` to `false`. `attributes` takes `data-*` attributes only. |

Every value comes from a content file, so LaRecipe checks it against the shape its provider uses before it reaches the page, and drops a tag whose values do not fit. Script sources must be `https` URLs, and nothing in `larecipe.json` can add inline code to the page. `php artisan larecipe:validate` reports values that break the schema as `manifest.schema` errors. When your app sets a Content Security Policy nonce with `LaRecipe::useCspNonce()`, every tag carries it.

## Page feedback

Every page ends with "Was this page helpful?", a Yes and a No, and an optional comment of up to 2,000 characters. A vote left without pressing Send is still sent when the reader moves on. The widget needs JavaScript.

Each answer posts to the set's feedback route:

<Route name="larecipe.docs.feedback" />

The route runs in the set's middleware group (`web` by default), so your session and CSRF protection apply, and it is rate limited per user or IP by `feedback.rate_limit` (20 a minute by default). It answers `204` and stores nothing. Feedback on a page the caller may not see is a `404`, like the page itself.

What it does instead:

1. **Dispatches `FeedbackSubmitted`** with the page, the answer, the comment and the request kind, with `source` set to `reader`. The MCP `submit_feedback` tool dispatches the same event with `source` set to `agent`, so feedback from agents arrives the same way.
2. **Reports to your analytics**, when an integration is configured. The widget fires a `larecipe:feedback` browser event, and LaRecipe's tags forward it: a `larecipe_feedback` event in GA4 and PostHog, a `Docs Feedback` event in Plausible, and `docs feedback: helpful` or `docs feedback: not helpful` in Fathom. Analytics get the page, version, locale and the answer. The comment goes only to your Laravel listeners.

Turn the widget off with `feedback.enabled` set to `false` in `config/larecipe.php`. That removes the widget, the route and the `submit_feedback` tool.

## Events

LaRecipe fires a Laravel event for each kind of request it serves. They live in `LaRecipe\Domains\Delivery\Events`. Every one carries a `kind`, and all of them carry the doc set except `SkillServed` for the cross-set skills index.

| Event | Fires when |
| --- | --- |
| `PageViewed` | A page is served as HTML, as Markdown, or as JSON for client-side navigation |
| `MarkdownServed` | A page's Markdown twin is served (a `.md` URL or `Accept: text/markdown`), next to `PageViewed` |
| `LlmsTxtServed` | `llms.txt` or `llms-full.txt` is served |
| `SkillServed` | `skill.md`, a hand-written skill file or `/.well-known/agent-skills/index.json` is served |
| `McpToolCalled` | An MCP tool is called, on the web endpoint or the local server |
| `SearchPerformed` | A server-side search runs: MCP `search_docs`, Ask AI or `/ask` |
| `AskAiAnswered` | Ask AI finishes an answer |
| `FeedbackSubmitted` | A reader or an agent sends page feedback |

The [events reference](../reference/events.md) lists every field. Here is `PageViewed`, read from the running app:

<Event name="PageViewed" />

`format` is `html`, `markdown` or `json`, and `page` is a `PageRef` with the set, version, locale and path.

## Request kinds

`kind` is `human`, `crawler` or `agent`. LaRecipe decides it from the request, in this order:

1. **Crawler** when the `User-Agent` names one: `ClaudeBot`, `GPTBot`, `PerplexityBot`, `Googlebot`, `Bingbot`, `Applebot`, `CCBot`, `Bytespider`, `Amazonbot`, `DuckDuckBot`, `YandexBot` and link-preview bots such as `Slackbot`.
2. **Agent** when it names an agent (`Claude-User`, `Claude-Code`, `ChatGPT-User`, `Perplexity-User`, `OAI-SearchBot`, `Cursor`, `codex`, `GoogleAgent`, `MistralAI-User`) or a common HTTP client (`curl`, `python-requests`, `axios`, `node-fetch`, `undici`, `Go-http-client`).
3. **Agent** when the request asks for Markdown: a `.md` URL or `Accept: text/markdown`.
4. **Human** otherwise.

Files that exist for agents (`llms.txt`, `llms-full.txt`, `skill.md`, the skills index and `/ask`) count every fetch that does not ask for HTML as an agent, unless its `User-Agent` says crawler. MCP calls are always `agent`.

The kind feeds events only. It never changes what LaRecipe serves: a crawler, an agent and a person get the same content.

## Forward events to your tools

Listen to the events like any Laravel event. This listener sends agent and crawler page views, the traffic your analytics script never sees, to your logs:

```php title="app/Listeners/ForwardDocsTraffic.php"
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use LaRecipe\Domains\Delivery\Events\PageViewed;

class ForwardDocsTraffic implements ShouldQueue
{
    public function handle(PageViewed $event): void
    {
        // People already reach your client-side analytics. Skip LaRecipe's own export and doctor runs too.
        if ($event->kind === 'human' || str_starts_with((string) $event->userAgent, 'LaRecipe-')) {
            return;
        }

        Log::info('Docs page viewed', [
            'set' => $event->set,
            'version' => $event->page->version,
            'locale' => $event->page->locale,
            'path' => $event->page->path,
            'format' => $event->format,
            'kind' => $event->kind,
            'user_agent' => $event->userAgent,
        ]);
    }
}
```

- Laravel discovers listeners in `app/Listeners` on its own. To register it by hand, add `Event::listen(PageViewed::class, ForwardDocsTraffic::class)` to a service provider's `boot()`.
- `ShouldQueue` moves the work onto your queue, so a page never waits for your analytics. Run a queue worker, or drop the interface to run it during the request.
- Replace the log line with your analytics tool's server-side API, a row in your own table, or anything else. LaRecipe has no opinion.
- `larecipe:export` and `larecipe:doctor` request pages through your app as `LaRecipe-Export/3 (static export)` and `LaRecipe-Doctor/1.0`, and those requests fire events too. The check on `LaRecipe-` above keeps them out.

## Next

- [Events reference](../reference/events.md): every event and its fields.
- [Search](search.md): find questions your docs do not answer with `SearchPerformed`.
- [Agent surfaces](../agents/surfaces.md): the files agents read, and the events each one fires.
