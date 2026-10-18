---
title: الأحداث
description: كل حدث Laravel يطلقه LaRecipe، ومتى يُطلق كل منها، وحمولته مقروءة من التطبيق أثناء تشغيله، وكيف يُحدَّد نوع الطلب.
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
translated_from: sha256:dd7fa43bebe4ba421cc5bf943b7726ca34034ec72d2d19219b657de104e442a4
---

# الأحداث

كل حدث Laravel يطلقه LaRecipe. لا يخزّن LaRecipe أي بيانات عن الزيارات ولا أي ملاحظات: تطبيقك هو الذي يستمع إليها ويمرّرها. ولما يمكنك فعله بها، راجع [التحليلات والأحداث](../operate/analytics.md).

```php
use Illuminate\Support\Facades\Event;
use LaRecipe\Domains\Delivery\Events\PageViewed;

Event::listen(function (PageViewed $event) {
    // $event->set, $event->page->path, $event->format, $event->kind
});
```

أحداث الزيارات في `LaRecipe\Domains\Delivery\Events`، وأحداث المحتوى في `LaRecipe\Domains\Sources\Events`. يُقرأ كل جدول حمولة أدناه من هذا التطبيق عبر [مرجع حي `<Event>`](../writing/live-references.md)، مع مستمعي هذا التطبيق (لا يملك مستمعًا إلا `ContentChanged`، ومستمعه هو الذي يملأ به LaRecipe الذاكرة المؤقتة مسبقًا).

## نوع الطلب

تحمل أحداث الزيارات `kind`، وقيمته `human` أو `crawler` أو `agent`. وتحدّده أول قاعدة تنطبق:

| الترتيب | متى | النوع |
| --- | --- | --- |
| 1 | يحتوي الـ user agent على اسم زاحف: `ClaudeBot`، `GPTBot`، `PerplexityBot`، `Googlebot`، `Bingbot`، `Applebot`، `CCBot`، `Bytespider`، `Amazonbot`، `DuckDuckBot`، `YandexBot`، `facebookexternalhit`، `Slackbot`، `Twitterbot` أو `LinkedInBot` | `crawler` |
| 2 | يحتوي الـ user agent على اسم وكيل أو عميل HTTP، بأي حالة أحرف: `Claude-User`، `Claude-Code`، `ChatGPT-User`، `Perplexity-User`، `OAI-SearchBot`، `Cursor`، `codex`، `GoogleAgent`، `MistralAI-User`، `axios`، `node-fetch`، `python-requests`، `curl`، `Go-http-client` أو `undici` | `agent` |
| 3 | يطلب الطلب Markdown: رابط `.md`، أو `Accept: text/markdown` | `agent` |
| 4 | أي شيء آخر | `human` |

الملفات `llms.txt` و `llms-full.txt` و `skill.md`، وملفات المهارات المكتوبة يدويًا، وفهرس المهارات، ونقطة النهاية `/ask` موجودة للوكلاء، فأي جلب لها لا يطلب HTML يُعدّ `agent` ما لم يسمِّ الـ user agent فيه زاحفًا. واستدعاءات MCP دائمًا `agent`. ولا يغذّي النوع إلا الأحداث: لا يغيّر أبدًا ما يُقدَّم.

## أحداث الزيارات

### PageViewed

يُطلق لكل صفحة تُقدَّم بصيغة HTML، أو بصيغة Markdown، أو بصيغة JSON التي تجلبها واجهة القراءة حين تتنقل (`format` قيمته `html` أو `markdown` أو `json`)، بعد اجتياز فحوص الوصول. والصفحة المفقودة، أو التي لا يحق للقارئ رؤيتها، لا تطلق شيئًا.

<Event name="PageViewed" />

### MarkdownServed

يُطلق مع `PageViewed` حين تُقدَّم نسخة Markdown من الصفحة. وقيمة `explicit` هي true لرابط `.md`، و false لـ `Accept: text/markdown`.

<Event name="MarkdownServed" />

### LlmsTxtServed

يُطلق حين يُقدَّم فهرس `llms.txt` أو ملف `llms-full.txt`. يبيّن `file` أيهما، ويُضبط `group` للملف المقسَّم حسب أقسام التنقل.

<Event name="LlmsTxtServed" />

### SkillServed

يُطلق حين يُقدَّم `skill.md`، أو ملف من مهارة مكتوبة يدويًا، أو `/.well-known/agent-skills/index.json`. وقيمتا `set` و `skill` هما null للفهرس، لأنه يغطي كل المجموعات.

<Event name="SkillServed" />

### McpToolCalled

يُطلق لكل استدعاء أداة على نقطة نهاية MCP لمجموعة على الويب أو على الخادم المحلي (`transport` قيمته `web` أو `local`). يحمل `arguments` الوسائط البسيطة (scalar)، مع اختصار القيم التي تتجاوز 200 حرف. واستدعاءات الأدوات التي تجريها ميزة اسأل الذكاء الاصطناعي (Ask AI) نفسها لا تطلقه.

<Event name="McpToolCalled" />

### SearchPerformed

يُطلق لكل عملية بحث، مع `source` الخاص بها:

| `source` | البحث |
| --- | --- |
| `mcp` | أداة MCP `search_docs`. |
| `endpoint` | نقطة النهاية `/ask` للوكلاء. |
| `assistant` | الاسترجاع في Ask AI. |
| `server` | نقطة النهاية `/_search`: لوحة البحث مع المشغّل `server` أو مع مشغّل مخصّص، والسكربتات. |
| `palette` | بحث أجابت عنه لوحة البحث في المتصفح، من فهرس المتصفح، ثم أبلغت عنه لاحقًا. |

تبلّغ لوحة البحث عن عمليات البحث فيها على دفعات صغيرة إلى `POST {prefix}/_search/report` (مع اللغة والإصدار قبله في النطاقات الأخرى). ولا يُحسب إلا الاستعلام المستقر، لا كل ضغطة مفتاح. والمسار محدود بـ 30 بلاغًا في الدقيقة لكل قارئ، ولا يخزّن شيئًا. ويستبعد التصدير الثابت هذا الإبلاغ. والأعداد تأتي من المتصفح: عاملها بوصفها تحليلات، لا إشارة أمنية أبدًا.

يعلّم `zeroResults` عمليات البحث التي لم يستطع توثيقك الإجابة عنها.

<Event name="SearchPerformed" />

### FeedbackSubmitted

يُطلق حين يجيب قارئ عن «هل كانت هذه الصفحة مفيدة؟» (`source` قيمته `reader`)، وحين يقيّم قارئ إجابة من Ask AI بالإعجاب أو عدمه (`ask`، مع `question` الذي كانت الإجابة له، والصفحة التي سأل منها القارئ)، وحين يستدعي وكيل أداة MCP `submit_feedback` (`agent`). لا يحتفظ LaRecipe بشيء: تطبيقك هو الذي يمرّره.

<Event name="FeedbackSubmitted" />

### AskAiAnswered

يُطلق حين ينهي Ask AI إجابة. تكون قيمة `covered` هي false حين يقول المساعد إن التوثيق لا يغطي السؤال، وتسرد `citations` روابط الصفحات التي استشهد بها، وتأتي أعداد الرموز (tokens) من المزوّد.

<Event name="AskAiAnswered" />

### PageRendered

يُطلق حين يُصيّر LaRecipe صفحة، للعمل الذي ينبغي أن يلي التصيير. تكون قيمة `format` هي `html` حين صُيّر المقال لأن الذاكرة المؤقتة للتصيير لم تكن تحويه (صفحة تغيّرت، أو ذاكرة مؤقتة فارغة، أو `larecipe:optimize`)، والإجابة من الذاكرة المؤقتة لا تطلق شيئًا. وتكون `markdown` في كل طلب لنسخة Markdown من الصفحة، لأنها لا تُخزَّن مؤقتًا. يحمل الصفحة وبصمة محتواها، ولا يحمل القارئ أبدًا: احسب القرّاء بـ `PageViewed`. ولا تطلقه أداة MCP `read_page` ولا `llms-full.txt`.

<Event name="PageRendered" />

## أحداث المحتوى

تُطلق أحداث المحتوى حين يمرّ تغيير عبر مسار الكتابة في LaRecipe: أدوات التأليف في MCP، أو لوحة الإدارة، أو `larecipe:sync`، أو ترحيل توثيق، أو `larecipe:source:import`، أو `larecipe:source:move`. أما تعديل ملف Markdown في محرّرك فلا يطلق شيئًا.

### PageWritten

يُطلق حين تُكتب صفحة أو تُنقل أو تُحذف، مباشرة أو مسودةً، وحين يُحتجز تغيير ترحيل توثيق على صفحة بوصفه تعارضًا. وتبيّن المراجعة (revision) أي ذلك حدث، ومن أجراه، وعبر أي قناة.

<Event name="PageWritten" />

### DraftCreated

يُطلق حين ينتظر تغييرٌ المراجعةَ: مع `review: required` لتغيير أجراه وكيل خارج pull request، أو حين طُلبت مسودة.

<Event name="DraftCreated" />

### DraftPublished

يُطلق حين ينشر مراجِع مسودة، أو يحلّ تعارضًا بأخذ نسخة الترحيل، في [قائمة المراجعة (The pass)](../sources/database.md#the-pass-the-review-queue) في لوحة الإدارة.

<Event name="DraftPublished" />

### DraftRejected

يُطلق حين يرفض مراجِع مسودة، مع ذكر سببه، أو يحلّ تعارضًا بإبقاء نسخة الإنتاج، في قائمة المراجعة في لوحة الإدارة.

<Event name="DraftRejected" />

### ContentChanged

يُطلق حين يتغيّر المحتوى المنشور لمجموعة: تغيير منشور على صفحة أو على التنقل أو على ملف التعريف (ومنها ترحيلات التوثيق)، أو رفع أصل أو حذفه، أو استيراد، أو نقل مصدر، أو التراجع عن ترحيل توثيق. وتسرد `pages` الصفحات التي تغيّرت، حين تكون معروفة. ويستمع إليه LaRecipe نفسه، بمستمع في الطابور يملأ الذاكرة المؤقتة للمجموعة مسبقًا.

<Event name="ContentChanged" />

### DocsMigrationConflicted

يُطلق حين يجد ترحيل توثيق أن الإنتاج تغيّر منذ كُتب الترحيل. لا يكتب فوقه: تنتظر نسخته مسودةَ تعارض بجوار نسخة الإنتاج، ويستمر النشر.

<Event name="DocsMigrationConflicted" />

## التالي

- [التحليلات](../operate/analytics.md): مرّر هذه الأحداث إلى أدواتك.
- [واجهات الوكلاء](../agents/surfaces.md): الملفات التي يجلبها الوكلاء.
