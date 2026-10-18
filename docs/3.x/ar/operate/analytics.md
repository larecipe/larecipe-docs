---
title: التحليلات والأحداث
description: أضف GA4 أو Plausible أو Fathom أو PostHog أو سكربتك الخاص من الإعدادات، واجمع التقييمات على الصفحات والإجابات، وأرسل كل طلب، بما فيها طلبات الوكلاء، إلى أدواتك الخاصة عبر أحداث Laravel.
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
translated_from: sha256:08c1c3ddeadb2ae4aafd359c08908431c19ce09b51761c9805079a0be3c148e0
---

# التحليلات والأحداث

لا لوحات بيانات في LaRecipe، ولا يخزّن أي حركة زيارات. بل يغذّي الأدوات التي تستخدمها أصلًا. لتضيف أداة، اذكرها تحت `integrations.analytics` في مدخل المجموعة في `config/larecipe.php`، فيطبع LaRecipe وسوم السكربت الخاصة بها في رأس كل صفحة من المجموعة:

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

وسوم السكربت ترى القرّاء الذين تشغّل متصفحاتهم JavaScript. أما الوكلاء فلا يشغّلون سكربت التحليلات أبدًا، لذا يطلق LaRecipe أيضًا حدث Laravel لكل صفحة ونسخة Markdown و `llms.txt` ومهارة واستدعاء MCP يقدّمه. استمع إلى هذه الأحداث لترى كل حركة الزيارات لديك.

لا يشغّل هذا الموقع أي تكامل تحليلات: لا يحوي `config/larecipe.php` ولا `larecipe.json` الخاصان به المفتاح `integrations`.

## تكاملات التحليلات

| الأداة | المفاتيح | ملاحظات |
| --- | --- | --- |
| `ga4` | `measurementId` | `G-` يليه من 4 إلى 20 حرفًا لاتينيًا كبيرًا أو رقمًا. يحمّل `gtag.js` من Google. |
| `plausible` | `domain`، `src` | يمكن أن يذكر `domain` عدة نطاقات، مفصولة بفواصل. القيمة الافتراضية لـ `src` هي `https://plausible.io/js/script.js`؛ والمضيف الآخر من الإعدادات فقط. |
| `fathom` | `siteId`، `src` | `siteId` من 4 إلى 20 حرفًا لاتينيًا كبيرًا أو رقمًا. القيمة الافتراضية لـ `src` هي `https://cdn.usefathom.com/script.js`؛ والمضيف الآخر من الإعدادات فقط. |
| `posthog` | `apiKey`، `host` | يبدأ `apiKey` بـ `phc_`. القيمة الافتراضية لـ `host` هي `https://us.i.posthog.com`؛ والمضيف الخاص بك من الإعدادات فقط. |
| `custom` | `src`، `defer`، `async`، `attributes` | أي سكربت، بعنوان `https`. يُؤجَّل تحميله ما لم تضبط `defer` على `false`. ولا يقبل `attributes` إلا سمات `data-*`. |

يفحص LaRecipe كل قيمة مقابل الشكل الذي يستخدمه مزوّدها قبل أن تصل إلى الصفحة، ويُسقط الوسم الذي لا تناسب قيمه. يجب أن تكون مصادر السكربتات عناوين `https`، ولا يمكن لأي إعداد أن يضيف كودًا مضمّنًا إلى الصفحة. وحين يضبط تطبيقك قيمة nonce لسياسة أمان المحتوى (Content Security Policy) عبر `LaRecipe::useCspNonce()`، يحملها كل وسم.

ويجوز لمجموعة المستودع أيضًا أن تعرّف التكاملات في ملف `larecipe.json` الخاص بها، بالمفاتيح نفسها.

<Accordion title="config/larecipe.php أم larecipe.json: أيهما يُعتمد">

تعمل هذه السكربتات ضمن جلسة كل قارئ، لذا يهمّ من أين تأتي:

- **`config/larecipe.php` يُعتمد لكل مجموعة، ويتقدّم على `larecipe.json` في كل أداة.**
- **`larecipe.json` يُعتمد لمجموعة المستودع فقط**، لأن ملف تعريفها يُراجع مع الكود. أما ملف `larecipe.json` لمجموعة قاعدة البيانات فيمكن تغييره من لوحة الإدارة وعبر MCP، لذا تتجاهل مجموعة قاعدة البيانات التكاملات فيه، ويحذّر منها `larecipe:validate` تحت `manifest.integrations`.
- **المضيف المستضاف ذاتيًا أو الخادم الوسيط لا يُعتمد إلا من `config/larecipe.php`**: أي `src` في Plausible و Fathom، و `host` في PostHog. ومن `larecipe.json` لا تُحمَّل إلا من مضيفيها الرسميين (`plausible.io` و `cdn.usefathom.com` ومضيفي PostHog `us` و `eu` و `app`)، عبر `https`.

التكامل نفسه في `larecipe.json`:

```json title="docs/larecipe.json"
{
  "integrations": {
    "analytics": {
      "plausible": { "domain": "docs.acme.dev" }
    }
  }
}
```

يبلّغ `php artisan larecipe:validate` عن قيم `larecipe.json` التي تخالف المخطط بوصفها أخطاء `manifest.schema`.

</Accordion>

## تقييم الصفحات

تنتهي كل صفحة بالسؤال «هل كانت هذه الصفحة مفيدة؟»، مع «نعم» و «لا»، وتعليق اختياري يصل إلى 2,000 حرف. تحتاج هذه الأداة إلى JavaScript. ولا يخزّن LaRecipe أي إجابة. بل إن كل إجابة:

1. **تطلق `FeedbackSubmitted`** مع الصفحة والإجابة والتعليق ونوع الطلب، و `source` مضبوط على `reader`. وتطلق أداة MCP `submit_feedback` الحدث نفسه و `source` مضبوط على `agent`، فتصل تقييمات الوكلاء بالطريقة نفسها.
2. **تُبلَّغ إلى أداة التحليلات**، حين يكون هناك تكامل مضبوط. تحصل التحليلات على الصفحة والإصدار واللغة والإجابة والمصدر. أما التعليق فلا يذهب إلا إلى المستمعين في Laravel لديك.

أوقف هذه الأداة بضبط `feedback.enabled` على `false` في `config/larecipe.php`. فيزيل ذلك الأداة ومسارها وأداة MCP `submit_feedback`.

<Accordion title="مسار التقييم، وما تستقبله كل أداة تحليلات">

التصويت الذي يُترك من دون الضغط على «إرسال» يُرسل مع ذلك حين ينتقل القارئ. وكل إجابة تُرسل إلى مسار التقييم الخاص بالمجموعة:

<Route name="larecipe.docs.feedback" />

يعمل المسار ضمن مجموعة الوسائط (middleware) الخاصة بالمجموعة (`web` افتراضيًا)، فتنطبق الجلسة والحماية من CSRF لديك، وهو محدود لكل مستخدم أو عنوان IP بحسب `feedback.rate_limit` (20 في الدقيقة افتراضيًا). ويجيب بـ `204`. والتقييم على صفحة لا يحق للمرسل رؤيتها يعيد `404`، مثل الصفحة نفسها.

تطلق الأداة حدث المتصفح `larecipe:feedback`، وتمرّره وسوم LaRecipe: حدث `larecipe_feedback` في GA4 و PostHog، وحدث `Docs Feedback` في Plausible، و `docs feedback: helpful` أو `docs feedback: not helpful` في Fathom.

</Accordion>

### تقييم الإجابات {#answer-feedback}

حين يكون [Ask AI](../agents/ask-ai.md) مفعّلًا، تحمل كل إجابة مكتملة زرّي إعجاب وعدم إعجاب. يُرسل التصويت إلى مسار التقييم نفسه مع السؤال الذي كانت الإجابة له، فيصل `FeedbackSubmitted` و `source` مضبوط على `ask` و `question` معبّأ، والصفحة هي التي سأل منها القارئ. وتحصل التحليلات لديك على حدث `larecipe:feedback` نفسه، بالمصدر `ask`. ويتبع الزرّان `feedback.enabled` أيضًا.

## الأحداث

يطلق LaRecipe حدث Laravel لكل نوع من الطلبات التي يقدّمها. وهي موجودة في `LaRecipe\Domains\Delivery\Events`.

| الحدث | يُطلق حين |
| --- | --- |
| `PageViewed` | تُقدَّم صفحة بصيغة HTML، أو Markdown، أو JSON للتنقل من جهة المتصفح |
| `MarkdownServed` | تُقدَّم نسخة Markdown لصفحة (عنوان `.md` أو `Accept: text/markdown`)، إلى جانب `PageViewed` |
| `LlmsTxtServed` | يُقدَّم `llms.txt` أو `llms-full.txt` |
| `SkillServed` | يُقدَّم `skill.md`، أو ملف مهارة مكتوب يدويًا، أو `/.well-known/agent-skills/index.json` |
| `McpToolCalled` | تُستدعى أداة MCP، على نقطة النهاية عبر الويب أو على الخادم المحلي |
| `SearchPerformed` | يجري بحث على الخادم (أداة MCP `search_docs`، أو Ask AI، أو `/ask`، أو نقطة النهاية `/_search`)، أو تبلّغ اللوحة عن عمليات بحث أجابت عنها في المتصفح |
| `AskAiAnswered` | ينهي Ask AI إجابة |
| `FeedbackSubmitted` | يرسل قارئ أو وكيل تقييمًا لصفحة، أو يقيّم قارئ إجابة من Ask AI |

كل واحد منها يحمل `kind`: إما `human` أو `crawler` أو `agent`، ويُحدَّد من وكيل المستخدم في الطلب ومن كونه يطلب Markdown أم لا. ولا يُستخدم هذا النوع إلا في الأحداث؛ ولا يغيّر أبدًا ما يقدّمه LaRecipe. وتجد في [مرجع الأحداث](../reference/events.md) القواعد الدقيقة وحقول كل حدث.

استمع إليها كأي حدث Laravel، وأرسلها إلى حيث تشاء: واجهة برمجة أداة التحليلات من جهة الخادم، أو صف في جدول خاص بك، أو سجل.

<AccordionGroup>

<Accordion title="حقول الحدث PageViewed، مقروءة من هذا التطبيق">

<Event name="PageViewed" />

قيمة `format` هي `html` أو `markdown` أو `json`، و `page` كائن `PageRef` فيه المجموعة والإصدار واللغة والمسار. وكل الأحداث تحمل مجموعة التوثيق، ما عدا `SkillServed` لفهرس المهارات الذي يجمع المجموعات كلها. ويوجد `PageRendered` في مساحة الأسماء نفسها، لكنه يخص التصيير لا القرّاء، ولا يحمل `kind`.

</Accordion>

<Accordion title="مستمع يمرّر حركة الوكلاء وزواحف الويب">

يرسل هذا المستمع مشاهدات الصفحات من الوكلاء وزواحف الويب، وهي الحركة التي لا يراها سكربت التحليلات أبدًا، إلى السجلات لديك:

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

- يكتشف Laravel المستمعين في `app/Listeners` من تلقاء نفسه. ولتسجّله يدويًا، أضف `Event::listen(PageViewed::class, ForwardDocsTraffic::class)` إلى `boot()` في مزوّد خدمة.
- ينقل `ShouldQueue` العمل إلى الطابور لديك، فلا تنتظر الصفحة أداة التحليلات أبدًا. شغّل عامل طوابير، أو احذف الواجهة ليعمل المستمع أثناء الطلب.
- يطلب `larecipe:export` و `larecipe:doctor` الصفحات عبر تطبيقك باسمَي `LaRecipe-Export/3 (static export)` و `LaRecipe-Doctor/1.0`، وتطلق هذه الطلبات أحداثًا أيضًا. والفحص على `LaRecipe-` أعلاه يستبعدها.

</Accordion>

</AccordionGroup>

## التالي

- [البحث](search.md): اعثر بالحدث `SearchPerformed` على الأسئلة التي لا يجيب عنها توثيقك بعد.
- [مرجع الأحداث](../reference/events.md): كل حدث وحقوله وكيف يُحدَّد نوع الطلب.
- [واجهات الوكلاء](../agents/surfaces.md): الملفات التي يقرؤها الوكلاء، والأحداث التي يطلقها كل منها.
