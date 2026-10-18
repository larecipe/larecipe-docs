---
title: البحث
description: يعمل البحث من دون أي خدمة تشغّلها. ما الذي يُفهرَس، وكيف تُبقي فهرس المتصفح صغيرًا، ومشغّل الخادم، وكيف توصل محرّك بحث خاصًا بك.
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
translated_from: sha256:faf8ace55e0486fa3e32f5f8b70e8e3754522b1bd4167cc343fa72b9eecb753e
---

# البحث

يعمل البحث لحظة تثبيت LaRecipe، من دون خدمة تشغّلها ولا إعدادات تضبطها. اضغط `⌘K` (`Ctrl K`) أو `/` في أي صفحة لتجرّبه.

يحصل كل إصدار وكل لغة في مجموعة التوثيق على فهرس JSON صغير، ويبحث فيه متصفح القارئ أثناء الكتابة. ويبحث الوكلاء في السجلات نفسها عبر أداة ترتيب مكتوبة بـ PHP. ويخضع الجميع لقواعد الوصول نفسها، فلا تظهر صفحة خاصة في نتائج أحد إلا إذا كان يحق له فتحها.

يقسّم LaRecipe كل صفحة إلى سجلات: سجل للصفحة نفسها (وصفها والنص الذي يسبق أول H2)، وسجل لكل H2 و H3 مع النص الذي تحته، وسجل لكل كتلة `<Can>` أو `<Cannot>`. والوسوم `tags` في ترويسة الصفحة قابلة للبحث، والصفحات التي فيها `hidden: true` تبقى خارج البحث.

## من يبحث، وكيف

| من يبحث | كيف | المحرّك |
| --- | --- | --- |
| القرّاء | لوحة الأوامر | MiniSearch، في المتصفح، على فهرس المتصفح |
| الوكلاء | أداة MCP `search_docs`، أو `GET /ask?q=` حين يكون مفعّلًا | أداة الترتيب بـ PHP |
| القرّاء الذين يصلون إلى صفحة مفقودة | الاقتراحات في صفحة 404 | أداة الترتيب بـ PHP |
| المجموعات على المشغّل `server`، والسكربتات | `GET /_search?q=` | أداة الترتيب بـ PHP، أو مشغّلك الخاص |

تُبنى السجلات مرة واحدة لكل تغيير في التوثيق وتُخزَّن مؤقتًا. ويبنيها [`php artisan optimize`](deployment.md) عند النشر، قبل أول بحث.

## أبقِ الفهرس صغيرًا

يجب أن يبقى فهرس المتصفح صغيرًا بما يكفي ليُنزَّل في لحظة. تُضبط الميزانية في `config/larecipe.php`، بالكيلوبايت من JSON المضغوط بـ gzip:

```php
// config/larecipe.php
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```

- يقدّر `php artisan larecipe:validate` فهرس كل إصدار، ويبلّغ عن تحذير `search.budget` حين يتجاوز الميزانية، مع الإصلاح: انقل المجموعة إلى المشغّل `server`، أو قسّم التوثيق إلى إصدارات أو مجموعات أكثر.
- يقيس `php artisan larecipe:optimize` عند النشر الفهرسَ الحقيقي للزوار في كل إصدار ويطبع حجمه، مع تحذير إن تجاوز الميزانية.

الفهارس مقسّمة أصلًا حسب الإصدار وحسب اللغة، فتنطبق الميزانية على كل واحد منها على حدة. ويمكن لمجموعة أن تضبط قيم `search` الخاصة بها في مدخلها تحت `sets`.

## مشغّل الخادم

اضبط `search.driver` على `server`، فلا يُنزّل المتصفح أي فهرس. وبدلًا من ذلك ترسل اللوحة كل استعلام إلى نقطة نهاية البحث الخاصة بالمجموعة:

```bash
curl 'https://larecipe.saleem.dev/3.x/_search?q=cache+warm&limit=1'
```

تجيب نقطة النهاية هذه مع كل المشغّلات، فتستطيع السكربتات استخدامها أيضًا. قيمة `limit` من 1 إلى 50 (الافتراضية 10)، وتُقتطع الاستعلامات عند 200 حرف. وتحمل كل نتيجة `path` الصفحة و `title` و `heading` و `anchor` و `snippet` و `score` و `description` و `url` و `markdown_url`.

ويقبل `search.driver` أيضًا اسم مشغّل تسجّله بنفسك، لـ Meilisearch أو Typesense أو فهرس نصي كامل في قاعدة بياناتك: راجع [مشغّل بحث مخصّص](#details) أدناه.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ملف فهرس المتصفح">

تخبر بيانات الإقلاع في كل صفحة اللوحةَ بمكان فهرسها. هذا ما حملته صفحات 3.x في هذا الموقع حين كُتبت هذه الصفحة:

```json
"search": {
    "driver": "client",
    "index_url": "/3.x/_search/e83bc2e9096f.a8aa52d86b61.json",
    "endpoint": "/3.x/_search",
    "report_url": "/3.x/_search/report"
}
```

`report_url` هو المكان الذي تبلّغ فيه اللوحة عن عمليات البحث التي تجيب عنها في المتصفح (راجع عمليات البحث كأحداث، أدناه). واسم الملف هو `{audience}.{fingerprint}.json`، تحت بادئة المجموعة واللغة والإصدار:

- **البصمة** (fingerprint) تتغيّر كلما تغيّر محتوى الإصدار، فيبقى العنوان ثابتًا لا يتغيّر محتواه. ويحصل عليه الزوار في مجموعة عامة مع `Cache-Control: public, max-age=31536000, immutable`، وتستطيع شبكة CDN أن تحتفظ به إلى الأبد.
- **الجمهور** (audience) بصمة قصيرة للصفحات التي يحق للقارئ رؤيتها وللصلاحيات (abilities) التي يملكها. يتشارك كل الزوار ملفًا عامًا واحدًا. أما القارئ المسجّل دخوله فيحصل على الملف الخاص بما يحق له رؤيته، يُقدَّم `private`، ولا يحوي إلا تلك السجلات.
- **من يطلب ملف جمهور آخر**، أو بصمة قديمة، يُعاد توجيهه إلى فهرسه الحالي. فلا يستطيع أحد أن ينزّل سجلات مخصّصة لغيره.

يحوي الملف خيارات MiniSearch والسجلات:

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

هذا سجل واحد من 522 في الملف، الذي بلغ نحو 123 KB مضغوطًا بـ gzip. تحتفظ السجلات بما يصل إلى 1,000 حرف من النص، بما فيه كتل الكود، إضافة إلى عناوين المكوّنات ونصوصها مثل عنوان خطوة. والعناوين الأعمق ونصوصها تتبع القسم الذي فوقها، والعنوان الموجود داخل كتلة صلاحيات لا يسمّي أبدًا نصًا خارجها.

</Accordion>

<Accordion title="متى يُحمَّل الفهرس">

لا يُحمَّل شيء من البحث مع الصفحة. تصل اللوحة والفهرس أول مرة يفتح فيها القارئ البحث، أو قبل ذلك، حين يكون المتصفح خاملًا بعد أول نقرة أو ضغطة مفتاح أو تمرير أو لمسة من القارئ، فيفتح `⌘K` عادةً والنتائج جاهزة. ثم يبني المتصفح فهرس MiniSearch من السجلات، وهذا يستغرق بضعة أجزاء من الثانية لمجموعة عادية.

تطابق اللوحة البادئات وتتسامح مع الأخطاء الإملائية. يجب أن تطابق كل الكلمات؛ وحين لا يطابقها كلها شيء، تعرض النتائج التي تطابق أيًا منها. وإن تعذّر تحميل الفهرس، تعود اللوحة إلى نقطة نهاية البحث في الخادم، ومن دونها إلى عناوين الصفحات من التنقل.

</Accordion>

<Accordion title="معرّفات الكود">

التوثيق مليء بأسماء مثل `Route::middleware` و `$request->user()` و `orders:prune` و `snake_case` و `hasMany`. يُبقي المقسّم كل معرّف كاملًا ويقسّمه أيضًا إلى أجزائه، فيجد `middleware` النتيجة `Route::middleware`، وعلى الخادم يمنح الاستعلام عن `Route::middleware` المطابقاتِ التامة أفضلية.

في فهرس المتصفح، توضع أجزاء كل معرّف في السجل داخل الحقل `terms` منخفض الوزن. ففي السجل المعروض في ملف فهرس المتصفح أعلاه، صار `larecipe:sources:suggest` هو `larecipe sources suggest`، فيجد البحث عن `suggest` ذلك القسم.

</Accordion>

<Accordion title="كيف ترتّب أداة PHP النتائج">

تخدم أداة الترتيب بـ PHP نقطة النهاية `/_search`، وأداة MCP `search_docs`، ونقطة النهاية `/ask`، و Ask AI، والاقتراحات في صفحة 404 بصيغة Markdown. وتبدو الاستجابة من نقطة النهاية هكذا:

```json
{
    "query": "cache warm",
    "version": "3.x",
    "locale": "en",
    "hits": [
        {
            "path": "reference/commands",
            "title": "Artisan commands",
            "heading": "larecipe:cache:warm",
            "anchor": "larecipecachewarm",
            "snippet": "Warms the cache of database sets. Run it in your deploy after php artisan migrate.",
            "score": 9.3573,
            "description": "Every larecipe:* artisan command, grouped by job, with its usage and options read from the running app.",
            "url": "https://larecipe.saleem.dev/3.x/reference/commands#larecipecachewarm",
            "markdown_url": "https://larecipe.saleem.dev/3.x/reference/commands.md"
        }
    ]
}
```

تقيّم السجلات بخوارزمية BM25 على حقول موزونة (العنوان، ثم العنوان الفرعي والوسوم ومسار الصفحة والنص)، وتطابق البادئات (`migr` يجد `migrations`)، وتسمح بخطأ إملائي واحد في الكلمات من أربعة إلى سبعة أحرف، وبخطأين ابتداءً من ثمانية أحرف، لكن فقط للكلمة التي لا تطابق شيئًا كما كُتبت. والسجلات التي تطابق جزءًا أكبر من الاستعلام تأتي أعلى، والاستعلام الذي يساوي تمامًا عنوان صفحة أو عنوانًا فرعيًا يحصل على أفضلية، ولا تُسهم الصفحة الواحدة بأكثر من قسمين في قائمة النتائج.

يحصل الزوار في مجموعة عامة على إجابات من نقطة النهاية يجوز للذاكرات المؤقتة أن تحتفظ بها دقيقة واحدة؛ ويحصل كل من عداهم على `private, no-store`. والمتصفح والخادم يرتّبان بكود مختلف، فقد يختلف ترتيبهما قليلًا. أما ما يمكنهما إيجاده، ومن يحق له إيجاده، فهو نفسه.

</Accordion>

<Accordion title="مشغّل بحث مخصّص">

لمحرّك بحث خاص بك، طبّق العقد `SearchDriver` في LaRecipe:

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

يستدعي LaRecipe الدالة `index()` مع كل سجلات إصدار ولغة، بما فيها الصفحات الخاصة وكتل الصلاحيات، أول مرة يُبحث فيها في ذلك الإصدار بعد أن تغيّر محتواه. يحمل كل سجل الصلاحيات التي يتطلبها، ويحمل كل `SearchQuery` الصفحات التي يحق للقارئ رؤيتها (`visible`، مفهرسة بالمسار) والصلاحيات التي يملكها. **يجب أن يصفّي مشغّلك النتائج بحسبها.** والدالة `SearchRecord::allows()` تجري فحص الصلاحيات نيابةً عنك.

يفحص LaRecipe مرة أخرى كل نتيجة يعيدها مشغّلك قبل أن يراها أحد، فلا يعتمد الوصول على المشغّل وحده أبدًا. ويُسقط النتيجة إن كانت خارج إصدار الاستعلام ولغته، أو في صفحة لا يحق للقارئ رؤيتها، أو عند نص لا يحق له قراءته. اضبط `record` في كل `SearchHit` على معرّف السجل الذي طابق: فالقسم قد يحوي كتل صلاحيات تشاركه المرساة نفسها، ومن دون المعرّف تفقد النتيجة في مثل هذا القسم مقتطفها لدى القارئ الذي تنقصه إحدى صلاحياتها.

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

سجّله في مزوّد خدمة واختره بالاسم:

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

يستقبل المصنع التطبيقَ وإعدادات `search` الخاصة بالمجموعة، فتستطيع أن تضيف هناك مفاتيح خاصة بك. ومع مشغّل مخصّص، لا تحوي بيانات الإقلاع عنوان فهرس: تستعلم اللوحة نقطة نهاية البحث، ويبحث كل من MCP و Ask AI وصفحات 404 بصيغة Markdown عبر مشغّلك. واسم المشغّل الذي لم يسجّله أحد يرمي استثناءً عند أول بحث، فلا يمرّ خطأ إملائي من دون أن يُلاحظ.

لا يستدعي LaRecipe الدالة `flush()` بنفسه بعد؛ احتفظ بها لصيانتك الخاصة. ولأن `index()` تستقبل الإصدار كله في كل مرة، يكفي أن تستبدل ما خزّنته ليبقى المحرّك محدّثًا.

</Accordion>

<Accordion title="عمليات البحث كأحداث">

كل عملية بحث تطلق `SearchPerformed`، مع `source`: أداة MCP `search_docs` (`mcp`)، واسترجاع Ask AI (`assistant`)، ونقطة النهاية `/ask` (`endpoint`)، ونقطة النهاية `/_search` (`server`)، وهي ما تستعلمه اللوحة مع المشغّل `server` أو مع مشغّل مخصّص.

عمليات البحث التي تجيب عنها اللوحة في المتصفح، من فهرس المتصفح، لا تصل إلى الخادم من تلقاء نفسها، لذا تبلّغ اللوحة عنها في دفعات صغيرة إلى المسار `_search/report` الخاص بالمجموعة، وكل واحدة منها تطلق `SearchPerformed` بالمصدر `palette`. ولا يُحتسب إلا الاستعلام المستقر، أي الذي توقف القارئ عن كتابته، فالكلمة المكتوبة حرفًا حرفًا عملية بحث واحدة. وهذا المسار محدود بـ 30 بلاغًا في الدقيقة لكل قارئ، ولا يخزّن شيئًا. أما [التصدير الثابت](static-export.md) فلا خادم له، فلا تبلّغ صفحاته عن شيء. وتأتي الأعداد من متصفح القارئ: استخدمها للتحليلات، ولا تعتمد عليها أبدًا كإشارة أمان.

<Event name="SearchPerformed" />

العلامة `zeroResults` هي المفيدة: بها تجد الأسئلة التي لا يجيب عنها توثيقك بعد. ويستطيع مستمع في تطبيقك أن يجمعها:

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

لا يخزّن LaRecipe شيئًا. راجع [التحليلات والأحداث](analytics.md) للأحداث الأخرى.

</Accordion>

</AccordionGroup>

## التالي

- [التحليلات والأحداث](analytics.md): أرسل عمليات البحث ومشاهدات الصفحات إلى أدواتك الخاصة.
- [التحقق و doctor](validation.md): الفحص `search.budget`.
- [التخزين المؤقت](caching.md): تعمّق في كيفية تخزين السجلات مؤقتًا.
