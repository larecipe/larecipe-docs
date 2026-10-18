---
title: Events
description: Every Laravel event LaRecipe dispatches, when each one fires, its payload read from the running app, and how the request kind is decided.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Events/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Events/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/RequestClassifier.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/TrafficKind.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/FeedbackController.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Controllers/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ToolContext.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/Tools/**
  - vendor/larecipe/larecipe/src/Domains/Ask/Controllers/AskController.php
  - vendor/larecipe/larecipe/src/Domains/Search/Controllers/SearchController.php
  - vendor/larecipe/larecipe/src/Domains/Search/Controllers/SearchReportController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RenderCache.php
  - vendor/larecipe/larecipe/ui/src/palette/report.ts
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ChangeEvents.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/**
  - config/larecipe.php
---

# Events

LaRecipe stores no traffic and no feedback. It dispatches ordinary Laravel events, and your app listens and forwards them to whatever it already uses. Traffic events matter most for agents: they read Markdown, `llms.txt` and MCP, and never run client-side analytics.

```php
use Illuminate\Support\Facades\Event;
use LaRecipe\Domains\Delivery\Events\PageViewed;

Event::listen(function (PageViewed $event) {
    // $event->set, $event->page->path, $event->format, $event->kind
});
```

There are two groups: traffic events in `LaRecipe\Domains\Delivery\Events`, and content events in `LaRecipe\Domains\Sources\Events`. The payload table under each event below is read from this app by an [`<Event>` live reference](../writing/live-references.md), with the listeners registered in this app. This site adds none of its own, so only `ContentChanged` lists one: LaRecipe's cache warmer. For what to do with the events, see [analytics](../operate/analytics.md).

## The request kind

Traffic events carry a `kind`: `human`, `crawler` or `agent`. It is decided from the request, in this order:

1. A user agent that names a crawler (`ClaudeBot`, `GPTBot`, `PerplexityBot`, `Googlebot`, `Bingbot`, `Applebot`, `CCBot` and others) is `crawler`.
2. A user agent that names an agent or an HTTP client (`Claude-User`, `Claude-Code`, `ChatGPT-User`, `Perplexity-User`, `OAI-SearchBot`, `Cursor`, `codex`, `curl`, `python-requests`, `node-fetch`, `axios` and others) is `agent`.
3. A request for Markdown (a `.md` URL, or `Accept: text/markdown`) is `agent`.
4. Anything else is `human`.

`llms.txt`, `skill.md`, the skills index and the `/ask` endpoint exist for agents, so a fetch of them that does not accept `text/html` counts as `agent` unless its user agent names a crawler. MCP calls are always `agent`. The kind feeds events only: it never changes what is served.

## Traffic events

### PageViewed

Fires for every page served as HTML, as Markdown or as the JSON the reader UI fetches when it navigates (`format` is `html`, `markdown` or `json`), after access checks pass. A missing page, or one the reader may not see, fires nothing.

<Event name="PageViewed" />

### MarkdownServed

Fires next to `PageViewed` when the page's Markdown twin is served. `explicit` is true for a `.md` URL and false for `Accept: text/markdown`.

<Event name="MarkdownServed" />

### LlmsTxtServed

Fires when an `llms.txt` index or an `llms-full.txt` file is served. `file` says which, and `group` is set for a file split by navigation group.

<Event name="LlmsTxtServed" />

### SkillServed

Fires when `skill.md`, a file of a hand-written skill or `/.well-known/agent-skills/index.json` is served. `set` and `skill` are null for the index, which covers every set.

<Event name="SkillServed" />

### McpToolCalled

Fires for every tool call on a set's web MCP endpoint or on the local server (`transport` is `web` or `local`). `arguments` holds the scalar arguments, with values longer than 200 characters shortened. Ask AI's own tool calls do not fire it.

<Event name="McpToolCalled" />

### SearchPerformed

Fires for every search, with its `source`:

| `source` | The search |
| --- | --- |
| `mcp` | The MCP `search_docs` tool. |
| `endpoint` | The `/ask` endpoint for agents. |
| `assistant` | Ask AI's retrieval. |
| `server` | The `/_search` endpoint: the palette on the `server` driver or a custom driver, and scripts. |
| `palette` | A search the palette answered in the browser, from the client index, reported afterwards. |

The palette reports its own searches in small batches to `POST {prefix}/_search/report` (with the locale and version in front for other scopes). Only a settled query counts: one the reader stopped typing, not each keystroke. The route is rate limited to 30 reports a minute per reader, stores nothing and only dispatches the events. A static export leaves the reporting out. The counts come from the reader's browser, so treat them as analytics, never as a security signal.

`zeroResults` marks the searches your docs could not answer.

<Event name="SearchPerformed" />

### FeedbackSubmitted

Fires when a reader answers "Was this page helpful?" (`source` is `reader`), when a reader gives an Ask AI answer a thumbs up or down (`ask`, with the `question` the answer was for, and the page the reader asked from), or when an agent calls the MCP `submit_feedback` tool (`agent`). LaRecipe keeps nothing: forward it.

<Event name="FeedbackSubmitted" />

### AskAiAnswered

Fires when Ask AI finishes an answer. `covered` is false when the assistant said the docs do not cover the question, `citations` lists the page URLs it cited, and the token counts come from the provider.

<Event name="AskAiAnswered" />

### PageRendered

Fires when LaRecipe renders a page, an extension point for work that should follow a render, such as warming another cache. `format` is `html` when the article was rendered because the render cache did not have it (a changed page, a cold cache, or `larecipe:optimize` compiling it): a request answered from the cache does not fire it. `format` is `markdown` on every request for a page's Markdown twin, which is not cached. It carries the page and its content hash, never anything about the reader; count readers with `PageViewed` and `MarkdownServed`. The MCP `read_page` tool and `llms-full.txt` do not fire it.

<Event name="PageRendered" />

## Content events

Content events fire when a change goes through LaRecipe's write path: the MCP author tools, the admin UI, `larecipe:sync`, a docs migration, `larecipe:source:import` or `larecipe:source:move`. Editing a Markdown file in your editor fires nothing; git records that change.

### PageWritten

Fires when a page is written, moved or deleted, live or as a draft, and when a docs migration's change to a page is held as a conflict. The revision says which, by whom and through which channel.

<Event name="PageWritten" />

### DraftCreated

Fires when a change waits for review: under `review: required` for a change an agent made outside a pull request, or when a draft was asked for.

<Event name="DraftCreated" />

### DraftPublished

Fires when a reviewer publishes a draft or resolves a conflict by taking the migration, at [the pass](../sources/database.md#the-pass-the-review-queue) in the admin UI.

<Event name="DraftPublished" />

### DraftRejected

Fires when a reviewer rejects a draft, with their reason, or resolves a conflict by keeping production, at the pass in the admin UI.

<Event name="DraftRejected" />

### ContentChanged

Fires when a set's live content changes: a published change to a page, the navigation or the manifest (docs migrations included), an asset upload or deletion, an import, a source move or a docs migration rollback. `pages` lists the pages that changed, when known. LaRecipe listens to it itself, with a queued listener that warms the set's cache.

<Event name="ContentChanged" />

### DocsMigrationConflicted

Fires when a docs migration finds that production changed since the migration was written. It does not overwrite: its version waits as a conflict draft next to production's, and the deploy carries on.

<Event name="DocsMigrationConflicted" />

## Next

- [Analytics](../operate/analytics.md): forward these events to your own tools.
- [Agent surfaces](../agents/surfaces.md): the files agents fetch.
