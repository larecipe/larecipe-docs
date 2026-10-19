---
title: Analytics and events
description: Add GA4, Plausible, Fathom, PostHog or your own script from your config, collect page and answer feedback, and forward every request, agents included, to your own tools with Laravel events.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/AnalyticsTags.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/RequestClassifier.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/TrafficKind.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Events/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/FeedbackController.php
  - vendor/larecipe/larecipe/src/Domains/Search/Controllers/SearchReportController.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/ManifestSchemaRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/Tools/SubmitFeedbackTool.php
  - vendor/larecipe/larecipe/ui/src/regions/PageFooter.tsx
  - vendor/larecipe/larecipe/ui/src/ask/AnswerFeedback.tsx
  - vendor/larecipe/larecipe/ui/src/palette/report.ts
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Analytics and events

LaRecipe has no dashboards and stores no traffic. It feeds the tools you already use. To add one, name it under `integrations.analytics` in the set's entry in `config/larecipe.php`, and LaRecipe prints its script tags into the head of every page of the set:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // route, source and access as usual
        'integrations' => ['analytics' => [
            'plausible' => ['domain' => 'docs.acme.dev'],
        ]],
    ],
],
```

Script tags see readers whose browsers run JavaScript. Agents never run your analytics script, so LaRecipe also fires a Laravel event for every page, Markdown twin, `llms.txt`, skill and MCP call it serves. Listen to those events to see all of your traffic.

This site runs GA4: its `config/larecipe.php` sets `ga4` under the `docs` set's `integrations.analytics`.

## Analytics integrations

| Tool | Keys | Notes |
| --- | --- | --- |
| `ga4` | `measurementId` | `G-` followed by 4 to 20 capital letters or digits. Loads Google's `gtag.js`. |
| `plausible` | `domain`, `src` | `domain` may list several, separated by commas. `src` defaults to `https://plausible.io/js/script.js`; another host only from config. |
| `fathom` | `siteId`, `src` | `siteId` is 4 to 20 capital letters or digits. `src` defaults to `https://cdn.usefathom.com/script.js`; another host only from config. |
| `posthog` | `apiKey`, `host` | `apiKey` starts with `phc_`. `host` defaults to `https://us.i.posthog.com`; a host of your own only from config. |
| `custom` | `src`, `defer`, `async`, `attributes` | Any script, by `https` URL. Deferred unless you set `defer` to `false`. `attributes` takes `data-*` attributes only. |

LaRecipe checks every value against the shape its provider uses before it reaches the page, and drops a tag whose values do not fit. Script sources must be `https` URLs, and no setting can add inline code to the page. When your app sets a Content Security Policy nonce with `LaRecipe::useCspNonce()`, every tag carries it.

A repository set may also declare integrations in its `larecipe.json`, with the same keys.

<Accordion title="config/larecipe.php or larecipe.json: which one counts">

These scripts run with every reader's session, so where they come from matters:

- **`config/larecipe.php` counts for every set, and wins per tool** over `larecipe.json`.
- **`larecipe.json` counts only for a repository set**, whose manifest is reviewed with your code. A database set's `larecipe.json` can be changed in the admin UI and over MCP, so a database set ignores integrations there, and `larecipe:validate` warns about them as `manifest.integrations`.
- **A self-hosted host or a proxy counts only from `config/larecipe.php`**: Plausible's and Fathom's `src`, and PostHog's `host`. From `larecipe.json`, they load from their official hosts only (`plausible.io`, `cdn.usefathom.com`, and PostHog's `us`, `eu` and `app` hosts), over `https`.

The same integration in `larecipe.json`:

```json title="docs/larecipe.json"
{
  "integrations": {
    "analytics": {
      "plausible": { "domain": "docs.acme.dev" }
    }
  }
}
```

`php artisan larecipe:validate` reports `larecipe.json` values that break the schema as `manifest.schema` errors.

</Accordion>

## Page feedback

Every page ends with "Was this page helpful?", a Yes and a No, and an optional comment of up to 2,000 characters. The widget needs JavaScript. LaRecipe stores no answer. Instead, each one:

1. **Dispatches `FeedbackSubmitted`** with the page, the answer, the comment and the request kind, with `source` set to `reader`. The MCP `submit_feedback` tool dispatches the same event with `source` set to `agent`, so feedback from agents arrives the same way.
2. **Reports to your analytics**, when an integration is configured. Analytics get the page, version, locale, the answer and the source. The comment goes only to your Laravel listeners.

Turn the widget off with `feedback.enabled` set to `false` in `config/larecipe.php`. That removes the widget, its route and the `submit_feedback` tool.

<Accordion title="The feedback route, and what each analytics tool receives">

A vote left without pressing Send is still sent when the reader moves on. Each answer posts to the set's feedback route:

<Route name="larecipe.docs.feedback" />

The route runs in the set's middleware group (`web` by default), so your session and CSRF protection apply, and it is rate limited per user or IP by `feedback.rate_limit` (20 a minute by default). It answers `204`. Feedback on a page the caller may not see is a `404`, like the page itself.

The widget fires a `larecipe:feedback` browser event, and LaRecipe's tags forward it: a `larecipe_feedback` event in GA4 and PostHog, a `Docs Feedback` event in Plausible, and `docs feedback: helpful` or `docs feedback: not helpful` in Fathom.

</Accordion>

### Answer feedback

When [Ask AI](../agents/ask-ai.md) is on, every finished answer has a thumbs up and a thumbs down. A vote posts to the same feedback route with the question the answer was for, so `FeedbackSubmitted` arrives with `source` set to `ask` and the `question` filled in, and the page is the one the reader asked from. Your analytics get the same `larecipe:feedback` event, with the source `ask`. The thumbs follow `feedback.enabled` too.

## Events

LaRecipe fires a Laravel event for each kind of request it serves. They live in `LaRecipe\Domains\Delivery\Events`.

| Event | Fires when |
| --- | --- |
| `PageViewed` | A page is served as HTML, as Markdown, or as JSON for client-side navigation |
| `MarkdownServed` | A page's Markdown twin is served (a `.md` URL or `Accept: text/markdown`), next to `PageViewed` |
| `LlmsTxtServed` | `llms.txt` or `llms-full.txt` is served |
| `SkillServed` | `skill.md`, a hand-written skill file or `/.well-known/agent-skills/index.json` is served |
| `McpToolCalled` | An MCP tool is called, on the web endpoint or the local server |
| `SearchPerformed` | A search runs on the server (MCP `search_docs`, Ask AI, `/ask` or the `/_search` endpoint), or the palette reports searches it answered in the browser |
| `AskAiAnswered` | Ask AI finishes an answer |
| `FeedbackSubmitted` | A reader or an agent sends page feedback, or a reader rates an Ask AI answer |

Every one of them carries a `kind`: `human`, `crawler` or `agent`, decided from the request's user agent and whether it asks for Markdown. The kind feeds events only; it never changes what LaRecipe serves. The [events reference](../reference/events.md) has the exact rules and every event's fields.

Listen to them like any Laravel event, and send them wherever you like: your analytics tool's server-side API, a row in your own table, or a log.

<AccordionGroup>

<Accordion title="Event fields: PageViewed, read from this app">

<Event name="PageViewed" />

`format` is `html`, `markdown` or `json`, and `page` is a `PageRef` with the set, version, locale and path. All events carry the doc set, except `SkillServed` for the cross-set skills index. `PageRendered` lives in the same namespace, but it is about rendering, not readers, and carries no `kind`.

</Accordion>

<Accordion title="A listener that forwards agent and crawler traffic">

This listener sends agent and crawler page views, the traffic your analytics script never sees, to your logs:

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
- `larecipe:export` and `larecipe:doctor` request pages through your app as `LaRecipe-Export/3 (static export)` and `LaRecipe-Doctor/1.0`, and those requests fire events too. The check on `LaRecipe-` above keeps them out.

</Accordion>

</AccordionGroup>

## Next

- [Search](search.md): find questions your docs do not answer yet with `SearchPerformed`.
- [Events reference](../reference/events.md): every event, its fields and how the request kind is decided.
- [Agent surfaces](../agents/surfaces.md): the files agents read, and the events each one fires.
