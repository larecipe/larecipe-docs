---
title: Search
description: Search works with no service to run. What gets indexed, how to keep the browser index small, the server driver, and how to plug in your own engine.
sources:
  - vendor/larecipe/larecipe/src/Domains/Search/**
  - vendor/larecipe/larecipe/ui/src/palette/search.ts
  - vendor/larecipe/larecipe/ui/src/palette/report.ts
  - vendor/larecipe/larecipe/ui/src/overlays/Overlays.tsx
  - vendor/larecipe/larecipe/ui/src/app/shortcuts.ts
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/SearchBudgetRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/OptimizeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Events/SearchPerformed.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/Tools/SearchDocsTool.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Controllers/AskEndpointController.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Search

Search works the moment LaRecipe is installed, with no service to run and nothing to configure. Press `⌘K` (`Ctrl K`) or `/` on any page to try it.

Each version and language of a doc set gets a small JSON index, and the reader's browser searches it as they type. Agents search the same records through a ranker in PHP. Everyone gets the same access rules, so a private page never shows up in anyone's results unless they may open it.

LaRecipe splits every page into records: one for the page itself (its description and the text before the first H2), one per H2 and H3 with the text under it, and one per `<Can>` or `<Cannot>` block. Frontmatter `tags` are searchable, and pages with `hidden: true` stay out.

## Who searches, and how

| Who searches | How | Engine |
| --- | --- | --- |
| Readers | The command palette | MiniSearch, in the browser, over the client index |
| Agents | MCP `search_docs`, or `GET /ask?q=` when it is on | The PHP ranker |
| Readers who hit a missing page | Suggestions on the 404 | The PHP ranker |
| Sets on the `server` driver, scripts | `GET /_search?q=` | The PHP ranker, or your own driver |

Records are built once per change to the docs and cached. [`php artisan optimize`](deployment.md) builds them on deploy, before the first search.

## Keep the index small

A client index should stay small enough to download in a moment. The budget is set in `config/larecipe.php`, in KB of gzipped JSON:

```php
// config/larecipe.php
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```

- `php artisan larecipe:validate` estimates each version's index and reports a `search.budget` warning when it would be over budget, with the fix: switch the set to the `server` driver, or split the docs into more versions or sets.
- `php artisan larecipe:optimize` measures the real guest index of every version on deploy and prints its size, with a warning past the budget.

Indexes are already split per version and per language, so the budget applies to each one separately. A set can set its own `search` values in its entry under `sets`.

## The server driver

Set `search.driver` to `server` and the browser downloads no index at all. The palette sends each query to the set's search endpoint instead:

```bash
curl 'https://larecipe.saleem.dev/3.x/_search?q=cache+warm&limit=1'
```

The endpoint answers on every driver, so scripts can use it too. `limit` runs from 1 to 50 (default 10), and queries are cut at 200 characters. Each hit carries the page's `path`, `title`, `heading`, `anchor`, `snippet`, `score`, `description`, `url` and `markdown_url`.

`search.driver` also takes the name of a driver you register yourself, for Meilisearch, Typesense or a full-text index in your database: see [a custom search driver](#details) below.

## Details

<AccordionGroup>

<Accordion title="The client index file">

Every page's boot data tells the palette where its index lives. This site's 3.x pages carried this when this page was written:

```json
"search": {
    "driver": "client",
    "index_url": "/3.x/_search/e83bc2e9096f.a8aa52d86b61.json",
    "endpoint": "/3.x/_search",
    "report_url": "/3.x/_search/report"
}
```

`report_url` is where the palette reports the searches it answers in the browser (see searches as events, below). The file name is `{audience}.{fingerprint}.json`, under the set's prefix, locale and version:

- **The fingerprint** changes whenever the version's content changes, so the URL is immutable. Guests on a public set get it with `Cache-Control: public, max-age=31536000, immutable`, and a CDN can keep it forever.
- **The audience** is a short hash of the pages a reader may see and the gate abilities they hold. Every guest shares one public file. A signed-in reader gets the file for what they may see, served `private`, and it holds only those records.
- **Anyone asking for another audience's file**, or an old fingerprint, is redirected to their own current index. Nobody can download records meant for someone else.

The file holds the MiniSearch options and the records:

```json
{
    "fingerprint": "fed1749a3fe4",
    "audience": "4d40f12a0a16",
    "fields": ["title", "heading", "tags", "text", "terms"],
    "storeFields": ["title", "heading", "path", "url", "kind"],
    "searchOptions": {
        "boost": { "title": 4, "heading": 3, "tags": 2, "terms": 1.5, "text": 1 },
        "prefix": true,
        "fuzzy": 0.2,
        "combineWith": "AND"
    },
    "records": [
        {
            "id": "writing/live-references#drift-detection",
            "title": "Live references",
            "heading": "Drift detection",
            "tags": "",
            "text": "Live references name their targets, so larecipe:sources:suggest reads them along with the classes, commands and route names in your inline code when it proposes sources: for a page. See how keeping docs current works.",
            "terms": "larecipe sources suggest writing live references",
            "path": "writing/live-references",
            "url": "/3.x/writing/live-references#drift-detection",
            "kind": "section"
        }
    ]
}
```

That is one record of 522 in the file, which weighed about 123 KB gzipped. Records keep up to 1,000 characters of text, code blocks included, plus component titles and labels such as a step's title. Deeper headings and their text belong to the section above them, and a heading inside a gated block never labels text outside it.

</Accordion>

<Accordion title="When the index loads">

Nothing about search loads with the page. The palette and the index arrive the first time the reader opens search, or earlier, when the browser is idle after the reader's first click, key press, scroll or touch, so `⌘K` usually opens with results ready. The browser then builds the MiniSearch index from the records, which takes a few milliseconds for a typical set.

The palette matches prefixes and tolerates typos. Every word must match; when nothing matches all of them, it shows results that match any. If the index cannot load, the palette falls back to the server endpoint, and without one, to page titles from the navigation.

</Accordion>

<Accordion title="Code identifiers">

Docs are full of names like `Route::middleware`, `$request->user()`, `orders:prune`, `snake_case` and `hasMany`. The tokenizer keeps each identifier whole and also splits it into its parts, so `middleware` finds `Route::middleware`, and on the server a query for `Route::middleware` gives exact matches a bonus.

In the client index, the parts of every identifier in a record go into the low-weight `terms` field. In the record shown in the client index file above, `larecipe:sources:suggest` became `larecipe sources suggest`, so a search for `suggest` finds the section.

</Accordion>

<Accordion title="How the PHP ranker scores">

The PHP ranker serves the `/_search` endpoint, MCP `search_docs`, the `/ask` endpoint, Ask AI and the suggestions on a Markdown 404. A response from the endpoint looks like this:

```json
{
    "query": "cache warm",
    "version": "3.x",
    "locale": "en",
    "hits": [
        {
            "path": "operate/caching",
            "title": "Caching and optimize",
            "heading": "Database sets",
            "anchor": "database-sets",
            "snippet": "…set never reads the database to serve a cached page. Its fingerprint lives in the cache store, every publish bumps it, and the ContentChanged event queues a listener that warms the…",
            "score": 10.3787,
            "description": "How LaRecipe caches rendered pages, page lists and navigation, and what larecipe:optimize compiles on deploy.",
            "url": "https://larecipe.saleem.dev/3.x/operate/caching#database-sets",
            "markdown_url": "https://larecipe.saleem.dev/3.x/operate/caching.md"
        }
    ]
}
```

It scores the records with BM25 over weighted fields (title, then heading, tags, the page path and the text), matches prefixes (`migr` finds `migrations`), and allows one typo in words of four to seven letters and two from eight letters, but only for a word that matches nothing as written. Records that match more of the query rank higher, a query that is exactly a page title or a heading gets a boost, and one page contributes at most two sections to a result list.

Guests on a public set get endpoint answers that caches may keep for a minute; everyone else gets `private, no-store`. The browser and the server rank with different code, so their order can differ a little. What they can find, and who may find it, is the same.

</Accordion>

<Accordion title="A custom search driver">

For an engine of your own, implement LaRecipe's `SearchDriver` contract:

```php title="vendor/larecipe/larecipe/src/Domains/Search/Contracts/SearchDriver.php"
<?php

namespace LaRecipe\Domains\Search\Contracts;

use LaRecipe\Domains\Search\DTOs\SearchQuery;
use LaRecipe\Domains\Search\DTOs\SearchRecord;
use LaRecipe\Domains\Search\DTOs\SearchResults;

/**
 * A custom search engine for a doc set (Meilisearch, Typesense, your own),
 * registered with LaRecipe::extendSearch() and selected with search.driver.
 *
 * LaRecipe pushes every record (private pages and gated blocks included) when
 * the set's content changes. Records carry the abilities they require, so a
 * driver must filter by the reader's: the query carries the pages the reader
 * may see and the abilities they hold (the Same-Rules Rule). LaRecipe checks
 * every hit again and drops what the reader may not see; set SearchHit's
 * $record to the matched record's id, or a hit in a section with gated
 * blocks loses its snippet for readers who lack an ability there.
 */
interface SearchDriver
{
    /** @param iterable<SearchRecord> $records every record of one version and locale */
    public function index(string $set, string $version, string $locale, iterable $records): void;

    public function search(SearchQuery $query): SearchResults;

    public function flush(string $set, ?string $version = null): void;
}
```

LaRecipe calls `index()` with every record of a version and language, private pages and gated blocks included, the first time that version is searched after its content changed. Each record carries the abilities it requires, and every `SearchQuery` carries the pages the reader may see (`visible`, keyed by path) and the abilities they hold. **Your driver must filter by them.** `SearchRecord::allows()` does the ability check for you.

LaRecipe checks every hit your driver returns again before anyone sees it, so access never rests on the driver alone. It drops a hit outside the query's version and language, on a page the reader may not see, or at text the reader may not read. Set `record` on each `SearchHit` to the id of the record that matched: a section can hold gated blocks that share its anchor, and without the id a hit in such a section loses its snippet for a reader who lacks one of their abilities.

```php title="app/Search/EngineDriver.php"
<?php

namespace App\Search;

use LaRecipe\Domains\Content\DTOs\PageRef;
use LaRecipe\Domains\Search\Contracts\SearchDriver;
use LaRecipe\Domains\Search\DTOs\SearchHit;
use LaRecipe\Domains\Search\DTOs\SearchQuery;
use LaRecipe\Domains\Search\DTOs\SearchRecord;
use LaRecipe\Domains\Search\DTOs\SearchResults;

final class EngineDriver implements SearchDriver
{
    // EngineClient stands for your engine's PHP client.
    public function __construct(private readonly EngineClient $client) {}

    public function index(string $set, string $version, string $locale, iterable $records): void
    {
        $documents = [];

        foreach ($records as $record) {
            $documents[] = $record->toArray();
        }

        // Replace everything stored for this set, version and locale, so deleted pages disappear.
        $this->client->replace("{$set}-{$version}-{$locale}", $documents);
    }

    public function search(SearchQuery $query): SearchResults
    {
        $hits = [];

        foreach ($this->client->search("{$query->set}-{$query->version}-{$query->locale}", $query->query) as $document) {
            $record = SearchRecord::fromArray($document);

            // Only what this reader may see: a visible page, and the abilities a gated block asks for.
            if (! isset($query->visible[$record->path]) || ! $record->allows($query->abilities)) {
                continue;
            }

            $hits[] = new SearchHit(
                ref: new PageRef($query->set, $query->version, $query->locale, $record->path),
                title: $record->title,
                heading: $record->heading,
                anchor: $record->anchor,
                snippet: mb_substr($record->text, 0, 180),
                record: $record->id,
            );

            if (count($hits) === $query->limit) {
                break;
            }
        }

        return new SearchResults($hits);
    }

    public function flush(string $set, ?string $version = null): void
    {
        $this->client->drop($set, $version);
    }
}
```

Register it in a service provider and select it by name:

```php title="app/Providers/AppServiceProvider.php"
use App\Search\EngineClient;
use App\Search\EngineDriver;
use LaRecipe\Facades\LaRecipe;

public function boot(): void
{
    LaRecipe::extendSearch('engine', fn ($app, array $config) => new EngineDriver($app->make(EngineClient::class)));
}
```

```php
// config/larecipe.php
'search' => [
    'driver' => 'engine',
],
```

The factory receives the app and the set's `search` config, so you can add keys of your own there. With a custom driver, the boot data has no index URL: the palette queries the search endpoint, and MCP, Ask AI and Markdown 404s all search through your driver. A driver name nobody registered throws an exception on the first search, so a typo never goes unnoticed.

LaRecipe does not call `flush()` itself yet; keep it for your own maintenance. Because `index()` receives the whole version every time, replacing what you stored is enough to keep the engine current.

</Accordion>

<Accordion title="Searches as events">

Every search fires `SearchPerformed`, with a `source`: MCP `search_docs` (`mcp`), Ask AI's retrieval (`assistant`), the `/ask` endpoint (`endpoint`) and the `/_search` endpoint (`server`), which is what the palette queries on the `server` driver or a custom one.

Searches the palette answers in the browser, from the client index, never reach the server on their own, so the palette reports them in small batches to the set's `_search/report` route, and each one fires `SearchPerformed` with the source `palette`. Only a settled query counts, one the reader stopped typing, so a word typed letter by letter is one search. The route is rate limited to 30 reports a minute per reader and stores nothing. A [static export](static-export.md) has no server, so its pages report nothing. The counts come from the reader's browser: use them for analytics, never as a security signal.

<Event name="SearchPerformed" />

The `zeroResults` flag is the useful one: it is how you find questions your docs do not answer yet. A listener in your app can collect them:

```php title="app/Providers/AppServiceProvider.php"
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LaRecipe\Domains\Delivery\Events\SearchPerformed;

Event::listen(function (SearchPerformed $event) {
    if ($event->zeroResults) {
        Log::info('Docs search found nothing', ['query' => $event->query, 'source' => $event->source]);
    }
});
```

LaRecipe stores nothing. See [Analytics and events](analytics.md) for the other events.

</Accordion>

</AccordionGroup>

## Next

- [Analytics and events](analytics.md): forward searches and page views to your own tools.
- [Validation and doctor](validation.md): the `search.budget` check.
- [Caching](caching.md): go deeper into how records are cached.
