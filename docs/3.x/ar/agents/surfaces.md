---
title: واجهات الوكلاء
description: كل صفحة بصيغة Markdown، وفهارس llms.txt، وملف skill.md مولَّد، وخادم MCP، فيقرأ الوكلاء التوثيق نفسه الذي يقرؤه الناس، بقواعد الوصول نفسها.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/NotFoundResponder.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/SitemapController.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/AgentMarkdownRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - vendor/larecipe/larecipe/resources/views/prerender/rail.blade.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:48411e1f983f4744ae3301a0eeb65e403f970fd7b5f947128d0757388636a2f7
---

# واجهات الوكلاء

تقدّم كل مجموعة توثيق للوكلاء الصفحات نفسها التي تقدّمها للناس، بصيغ يستطيع الوكلاء استخدامها. وهي مفعّلة افتراضيًا، وتتبع قواعد الوصول نفسها التي تتبعها صفحات HTML، فالصفحة الخاصة تبقى خاصة على كل واجهة.

| الواجهة | في هذا الموقع | ما هي |
| --- | --- | --- |
| نسخة Markdown | `/3.x/agents/surfaces.md` | أي صفحة بصيغة Markdown: أضف `.md`، أو أرسل `Accept: text/markdown`. |
| `llms.txt` | `/llms.txt`، `/3.x/llms.txt` | الخريطة: كل صفحة مع وصفها. |
| `llms-full.txt` | `/3.x/llms-full.txt` | صفحات إصدار واحد في ملف واحد. |
| `skill.md` | `/skill.md` | كيف ينبغي للوكيل أن يستخدم هذا التوثيق. |
| MCP | `/mcp` | أدوات للبحث والقراءة. راجع [خادم MCP](mcp.md). |

```bash
curl https://larecipe.saleem.dev/3.x/index.md
```

هذه المجموعة مركّبة على جذر النطاق، لذا توجد ملفاتها في `/llms.txt` و `/skill.md`. والمجموعة المركّبة على `/docs` تقدّم الملفات نفسها في `/docs/llms.txt` و `/docs/skill.md`.

## كل صفحة بصيغة Markdown {#every-page-as-markdown}

أضف `.md` إلى رابط أي صفحة، أو اطلب رابط الصفحة نفسه مع `Accept: text/markdown`، فتحصل على الصفحة بصيغة Markdown. والوكيل الذي يفضّل Markdown لا يحتاج أبدًا إلى معرفة هذا العرف. ونسخة Markdown لصفحة رئيسية هي رابطها مع `.md`: `/3.x.md` و `/ar.md` في هذا الموقع، أو `/docs.md` لمجموعة مركّبة على `/docs`.

نسخة Markdown ليست ملفك المصدري. بل تُصيَّر من الصفحة المحلَّلة نفسها التي تُصيَّر منها صفحة HTML: تصبح المكوّنات Markdown قابلًا للنقل، وتصل المراجع الحية بقيمها الحالية، وتصبح الروابط مطلقة، وتُزال كتل الصلاحيات التي لا يجوز للقارئ رؤيتها. فلا نسخة ثانية تحتاج إلى مزامنة.

<Accordion title="ما الذي تحويه نسخة Markdown">

```markdown
---
title: 'Docs that keep up with your code'
description: 'LaRecipe is a docs site that lives inside your Laravel app, written and kept current by ...'
url: 'https://larecipe.saleem.dev/3.x'
version: 3.x
locale: en
last_updated: '2026-10-17'
---

> Docs index: https://larecipe.saleem.dev/3.x/llms.txt · MCP: https://larecipe.saleem.dev/mcp · Skill: https://larecipe.saleem.dev/skill.md

# Docs that keep up with your code

You write pages in Markdown, in your own repository, next to the code they describe. ...
```

- **ترويسة ثابتة.** `title` و `description` و `url` و `version` (في المجموعة ذات الإصدارات) و `locale` (في المجموعة متعددة اللغات) و `last_updated`. ولا تُنقل أبدًا ترويسة الصفحة كما كُتبت، فقد تحوي أي شيء.
- **سطر اكتشاف.** اقتباس واحد يشير إلى `llms.txt` ونقطة نهاية MCP و `skill.md`. فنادرًا ما يجد الوكلاء هذه الملفات ما لم يشر إليها شيء.
- **المكوّنات بصيغة Markdown قابلة للنقل.** يصبح التنبيه (callout) تنبيهًا بأسلوب GitHub، وتصبح التبويبات أقسامًا معنونة، وتصبح البطاقة رابطًا، ويصبح الفيديو رابطًا مع عنوانه. ويسرد [مرجع المكوّنات](../reference/components.md) صيغة Markdown لكل مكوّن.
- **المراجع الحية محلولة.** تصل `<Route>` و `<Artisan>` و `<Model>` و `<Event>` على شكل جداول وكتل كود. راجع [المراجع الحية](../writing/live-references.md).
- **روابط مطلقة.** تصبح الروابط والصور النسبية روابط كاملة، وتشير الروابط إلى الصفحات الأخرى إلى نسخ Markdown الخاصة بها.
- **ما يجوز للمستدعي رؤيته فقط.** راجع [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md).

</Accordion>

<Accordion title="كيف يختار LaRecipe بين HTML و Markdown">

اللاحقة `.md` الصريحة تغلب. وإلا فالترويسة `Accept` هي التي تقرر، وتُقرأ حسب ترتيب الأفضلية، فلا يغلب `text/markdown` إلا حين يضعه العميل فوق `text/html`. والمتصفحات تحصل على HTML.

</Accordion>

## llms.txt و llms-full.txt {#llmstxt-and-llms-fulltxt}

`llms.txt` هو خريطة مجموعة التوثيق. ويُولَّد من التنقل، فلا تكتبه بيدك أبدًا ولا يتأخر أبدًا: تصبح كل مجموعة في التنقل عنوانًا، وكل صفحة سطرًا فيه عنوانها ورابط Markdown الخاص بها ووصفها.

يأتي الملخص في أعلاه من `agents.llmsTxt.description` في [larecipe.json](../reference/manifest.md)، والتعليمات تحته من `agents.llmsTxt.instructions`. ومن دونهما، يُستخدم `description` الخاص بالمجموعة.

ويحوي `/3.x/llms-full.txt` محتوى Markdown لكل صفحة في إصدار واحد، بترتيب القراءة. ولا يوجد ملف يجمع الموقع كله عن قصد: فهو سيتجاوز سياق الوكيل.

<AccordionGroup>

<Accordion title="ملف llms.txt الخاص بهذا الموقع">

```markdown
# LaRecipe

> LaRecipe is the docs engine that lives inside your Laravel app: ...

Read the 3.x pages unless the user is on LaRecipe 2. ...

This index lists the pages of 3.x, the default version. Every version has its own index, under Versions.

Every page is also available as Markdown: the links below point to it, and adding `.md` to any page URL works too. Search and read these docs with the MCP server at `https://larecipe.saleem.dev/mcp` (tools: search_docs, read_page, list_pages). ...

## Get started

- [Installation](https://larecipe.saleem.dev/3.x/get-started/installation.md): ...

## Versions

- [3.x (default)](https://larecipe.saleem.dev/3.x/llms.txt): Current version.
- [2.x](https://larecipe.saleem.dev/2.x/llms.txt): Maintained: fixes only.

## Languages

- [English](https://larecipe.saleem.dev/llms.txt): en
- [العربية](https://larecipe.saleem.dev/ar/llms.txt): ar
```

</Accordion>

<Accordion title="الإصدارات واللغات وحدود الحجم">

- **في المجموعة ذات الإصدارات،** يسرد `/llms.txt` في الجذر صفحات الإصدار الافتراضي، فيصل الوكيل الذي يبدأ منه إلى التوثيق الحالي بطلب واحد، ويربط فهرس كل إصدار (`/3.x/llms.txt` و `/2.x/llms.txt`) مع حالته.
- **في المجموعة متعددة اللغات،** لكل لغة فهرسها: `/ar/llms.txt` و `/ar/3.x/llms.txt`.
- **يبقى `llms.txt` صغيرًا.** يبقى كل ملف دون `agents.llms_txt_limit` حرفًا (50,000 افتراضيًا، وهو حدّ AFDocs). وحين يتجاوزه إصدار، تنتقل كل مجموعة عليا في التنقل إلى ملف خاص بها، مثل `/3.x/get-started/llms.txt`، ويربطها الفهرس. ولا تُحذف الأوصاف إلا كحلّ أخير.
- **يُبَثّ `llms-full.txt` تدفقيًا،** فلا يحتاج الإصدار الكبير أبدًا إلى أن يتسع في الذاكرة. ويحوّل `/llms-full.txt` في جذر المجموعة ذات الإصدارات إلى ملف الإصدار الافتراضي. وبعد `agents.llms_full_limit` حرفًا (400,000 افتراضيًا) ينقسم الإصدار حسب مجموعات التنقل، مثل `llms.txt`، وكل ما يُستبعد يُذكر برابط، ولا يُقصّ بصمت أبدًا. وتبقى نسخ Markdown و MCP الطريقة الأفضل لقراءة صفحة واحدة.

</Accordion>

</AccordionGroup>

## skill.md {#skillmd}

`/skill.md` مهارة يستطيع الوكيل تثبيتها ليتعلم كيف يستخدم هذا التوثيق: ابحث أولًا، واقرأ الصفحات كاملة بصيغة Markdown، واذكر روابطها. ويكتبها LaRecipe من ملف التعريف (الاسم والوصف وتعليمات الوكلاء)، والتنقل (حتى 40 صفحة رئيسية مع روابط Markdown الخاصة بها)، وتفاصيل الاتصال بالمجموعة (MCP و `llms.txt` و `llms-full.txt` والإصدارات).

هذه هي المهارة التي تنشرها مجموعة التوثيق عن منتجها، لوكلاء قرّائها. أما المهارات التي يثبّتها LaRecipe في مستودعك لكتابة التوثيق فشيء آخر؛ راجع [المهارات وإضافة Claude Code](../keeping-current/skills.md).

<AccordionGroup>

<Accordion title="ملف skill.md الخاص بهذا الموقع">

لا تستخدم ترويسته إلا مفاتيح Agent Skills القابلة للنقل، `name` و `description`، فيستطيع أي وكيل تحميله.

```markdown
---
name: larecipe
description: 'Use when working with LaRecipe: ... Explains how to search and read the LaRecipe docs (MCP tools, llms.txt and a Markdown version of every page) and lists the key pages.'
---

# LaRecipe

## Read the docs

1. Search first: call `search_docs` on the MCP server at https://larecipe.saleem.dev/mcp, or scan the index at https://larecipe.saleem.dev/llms.txt.
2. Read whole pages as Markdown: call `read_page`, or add `.md` to any page URL.
3. Answer from the pages you read, and cite their URLs.
```

</Accordion>

<Accordion title="مهارات مكتوبة بيد">

يمكن لمجموعة المستودع أن تقدّم مهاراتها الخاصة في مجلد `skills/` في جذر توثيقها، مجلد لكل مهارة:

```text
docs/
├── larecipe.json
├── skills/
│   └── billing-integration/
│       ├── SKILL.md
│       └── references/
│           └── webhooks.md
└── 3.x/
```

تُقدَّم كل مهارة في `/skills/{name}/SKILL.md`، مع ملفات `references/` و `scripts/` و `assets/` بجوارها. والمهارة المكتوبة بيد التي تحمل اسم المهارة المولَّدة تحلّ محل `skill.md`. ويفحص `php artisan larecipe:validate` كل `SKILL.md` وفق مواصفة Agent Skills (`skills.spec`).

</Accordion>

<Accordion title="فهرس المهارات">

يسرد `/.well-known/agent-skills/index.json` كل مهارة لكل مجموعة عامة في الموقع، كلٌّ منها مع رابطها وبصمة `sha256` للبايتات المقدَّمة بالضبط، فتستطيع أدوات مثل `npx skills add https://larecipe.saleem.dev` أن تجدها وتتحقق منها.

```json
{
  "$schema": "https://schemas.agentskills.io/discovery/0.2.0/schema.json",
  "skills": [
    {
      "name": "larecipe",
      "type": "skill-md",
      "description": "Use when working with LaRecipe: ...",
      "url": "https://larecipe.saleem.dev/skill.md",
      "digest": "sha256:b688d79694302d4ae0866b9c855ef418c38dae9714691a1e6065278581184c39"
    }
  ]
}
```

</Accordion>

</AccordionGroup>

## للوكلاء، في كل صفحة {#for-agents-on-every-page}

في العمود الجانبي لكل صفحة كتلة **للوكلاء** (For agents)، فيستطيع القارئ أن يسلّم الصفحة لوكيله بنقرة واحدة. وعلى الهواتف توجد الإجراءات نفسها في قائمة تحت العنوان.

| الإجراء | ما يفعله |
| --- | --- |
| نسخ كـ Markdown | ينسخ محتوى Markdown الخاص بالصفحة للوكلاء إلى الحافظة. |
| عرض كـ Markdown | يفتح نسخة `.md` من الصفحة. |
| فتح في Claude، فتح في ChatGPT، فتح في Cursor | يفتح ذلك الوكيل مع طلب يوجّهه إلى محتوى Markdown الخاص بالصفحة. |
| ربط MCP | ينسخ الأمر `claude mcp add` لخادم MCP الخاص بهذه المجموعة. |

تحتاج إجراءات النسخ إلى JavaScript؛ ومن دونه تبقى الكتلة تربط نسخة Markdown.

## أوقف الواجهات {#turn-surfaces-off}

لكل واجهة مفتاح تحت `agents` في `config/larecipe.php`، وكلها مفعّلة افتراضيًا ما عدا نقطة النهاية `/ask`. ويمكن لأي مجموعة أن تغيّر أيًّا منها في إدخالها الخاص تحت `sets`.

| المفتاح | يتحكم في |
| --- | --- |
| `agents.llms_txt` | `llms.txt` وملفاته الخاصة بالمسارات |
| `agents.llms_full` | `llms-full.txt` |
| `agents.skill_md` | `skill.md` والمهارات المكتوبة بيد في `skills/` |
| `agents.well_known_skills` | `/.well-known/agent-skills/index.json` |
| `agents.sitemap` | `sitemap.xml` |
| `agents.ask_endpoint` | البحث الاختياري عبر `/ask?q=` (متوقف افتراضيًا) |
| `mcp.web.enabled` | نقطة نهاية MCP الخاصة بالمجموعة |

نسخة Markdown لكل صفحة تُقدَّم دائمًا: فهي المحتوى نفسه الذي في صفحة HTML.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="صفحات 404 بصيغة Markdown">

الرابط `.md` أو الطلب بـ `text/markdown` لصفحة غير موجودة يحصل على `404` حقيقي مع محتوى Markdown قصير: المسار الذي طلبه، وحتى خمس صفحات مشابهة من مرتّب نتائج البحث، وفهرس التوثيق.

```markdown
# Page not found

There is no page at `/agents/mcp-server` in LaRecipe.

Similar pages:

- [The MCP server](https://larecipe.saleem.dev/3.x/agents/mcp.md): ...

Docs index: https://larecipe.saleem.dev/llms.txt
```

والصفحة الموجودة المخفية عن المستدعي تحصل على الإجابة نفسها بالضبط، فلا يؤكد الـ 404 أبدًا وجود صفحة خاصة. وطلبات HTML لصفحات غير موجودة تحصل على الاقتراحات نفسها في صفحة «غير موجودة».

</Accordion>

<Accordion title="ترويسات الاكتشاف">

كل استجابة لصفحة، HTML كانت أو Markdown، تحمل ترويسات توجّه الوكلاء إلى الواجهات الأخرى:

```http
Vary: Accept
Link: <https://larecipe.saleem.dev/3.x/index.md>; rel="alternate"; type="text/markdown", <https://larecipe.saleem.dev/3.x/llms.txt>; rel="describedby"; type="text/plain", <https://larecipe.saleem.dev/mcp>; rel="mcp", <https://larecipe.saleem.dev/skill.md>; rel="describedby"; type="text/markdown"; title="skill"
ETag: "80ff5bd7412bd994f7dc"
Last-Modified: Sat, 17 Oct 2026 15:22:00 GMT
```

- تخبر `Vary: Accept` الذاكرات المؤقتة بأن الرابط نفسه يجيب بـ HTML أو Markdown.
- تعلن `Link` عن نسخة Markdown، و `llms.txt` الخاص بالإصدار، ونقطة نهاية MCP، و `skill.md`، لأي عميل يقرأ الترويسات. ولا يظهر الرابط إلا حين تكون واجهته مفعّلة.
- تسمح `ETag` و `Last-Modified` للوكلاء وللذاكرات المؤقتة بإعادة التحقق بتكلفة قليلة. والطلب الذي يحمل `If-None-Match` مطابقًا يحصل على `304`.
- ويحوي متن كل صفحة أيضًا رابطًا إلى `llms.txt` الخاص بالإصدار، مخفيًا عن النظر وعن لوحة المفاتيح، للوكلاء الذين يقرؤون HTML ولا يقرؤون ترويساته.

ومدة التخزين المؤقت المسموحة لكل استجابة، للزوار والقرّاء المسجّلين والصفحات الخاصة، مذكورة في صفحة [الأمان](../operate/security.md).

</Accordion>

<Accordion title="خريطة الموقع">

يسرد `/sitemap.xml` كل صفحة عامة في مجموعة عامة، مع `lastmod` وبدائل `hreflang` لترجماتها. وخريطة الموقع ملف عام، لذا لا تسرد إلا ما يجوز للزائر رؤيته: تجيب المجموعات الخاصة بـ `404`، وتُستبعد الإصدارات الخاصة والصفحات الخاصة والصفحات المخفية وصفحات `noindex`.

</Accordion>

<Accordion title="نقطة نهاية البحث للوكلاء بلا MCP">

الوكلاء الذين يستطيعون جلب رابط لكن لا يملكون عميل MCP يستطيعون البحث عبر `GET /ask?q=` (وفي المجموعة ذات البادئة، `/docs/ask?q=`). وهي متوقفة افتراضيًا؛ فعّلها بـ `agents.ask_endpoint`:

```php
// config/larecipe.php
'agents' => [
    'ask_endpoint' => true,
],
```

تجيب بصفحات مرتّبة بصيغة JSON (`title` و `url` و `markdown_url` و `heading` و `snippet` و `description` و `score`)، باستخدام المرتّب نفسه وقواعد الوصول نفسها التي تستخدمها أداة MCP المسمّاة `search_docs`. و `version` و `locale` و `limit` (حتى 20) معاملات استعلام اختيارية.

</Accordion>

<Accordion title="الأحداث و doctor">

كل جلب لهذه الملفات يطلق حدث Laravel (`MarkdownServed` و `LlmsTxtServed` و `SkillServed`) مع نوع الطلب، فتصل حركة الوكلاء التي لا تراها التحليلات في المتصفح أبدًا إلى أدواتك. راجع [التحليلات والأحداث](../operate/analytics.md).

ويقيّم `php artisan larecipe:doctor` كل هذا من داخل التطبيق، كما يقيّمه AFDocs من خارجه: تطابق Markdown، وحجم `llms.txt` وروابطه، وسلوك 404، والترويسات، وإعادات التوجيه. راجع [التحقق و doctor](../operate/validation.md).

</Accordion>

</AccordionGroup>

## التالي {#next}

- [خادم MCP](mcp.md): أدوات البحث والقراءة للوكلاء.
- [اسأل الذكاء الاصطناعي (Ask AI)](ask-ai.md): إجابات لقرّائك، من الأدوات نفسها.
- [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md): تعمّق أكثر في قواعد الوصول التي تتشاركها كل الواجهات.
