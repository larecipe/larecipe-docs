---
title: التحقق و doctor
description: تأكّد من صحة التوثيق بالأمر larecipe:validate، ومما يحصل عليه الوكلاء من تطبيقك بالأمر larecipe:doctor، واقرأ تقاريرهما، وشغّلهما في CI.
sources:
  - vendor/larecipe/larecipe/src/Domains/Validation/**
  - vendor/larecipe/larecipe/resources/schemas/validate.schema.json
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/src/Domains/References/Markdown/ResolveReferences.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/Doctor/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/DoctorCommand.php
  - vendor/larecipe/larecipe/src/Domains/Agents/DTOs/DoctorCheck.php
  - vendor/larecipe/larecipe/src/Domains/Agents/DTOs/DoctorReport.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteCiCheckAction.php
  - vendor/larecipe/larecipe/stubs/larecipe-docs-check.yml
translated_from: sha256:ed4570986f29d4f6b03a8728b5f574bdb9d27f965aa204c943a513b2a6f37043
---

# التحقق و doctor

فحصان للقراءة فقط، من دون ذكاء اصطناعي. شغّل `larecipe:validate` بعد كل تغيير في التوثيق لتتأكد من أنه صحيح، و `larecipe:doctor` بعد النشر لتتأكد مما يحصل عليه الوكلاء من تطبيقك.

```bash
php artisan larecipe:validate
php artisan larecipe:doctor
```

يقرأ `larecipe:validate` كل صفحة عبر أداة التصيير الحقيقية، ويبلّغ عن الروابط المعطّلة، وأخطاء المخطط، والمكوّنات المجهولة، والمقتطفات المفقودة، والمراجع الحية التي لم تعد تُحَل، وغير ذلك. ويخرج بالرمز `1` حين يجد خطأً، فيستطيع CI أن يتوقف عنده. أما التحذيرات فلا تُفشله أبدًا. ويشغّله وكيل البرمجة قبل كل push.

كل مشكلة تذكر مستواها وقاعدتها وصفحتها وسطرها، مع إصلاح حين يعرف LaRecipe إصلاحًا:

```text
  ERROR  links.broken  3.x/agents/mcp line 220
         Link to ../sources/docs-migrations.md: there is no page at "sources/docs-migrations".
         Fix: Fix the link, or write the page it points to.
```

هذه المشكلة من هذا الموقع نفسه، أثناء كتابة هذا التوثيق: الصفحة التي يشير إليها الرابط كانت التالية في الدور.

## larecipe:validate

من دون خيارات، يفحص كل إصدار وكل لغة في كل مجموعة توثيق. ضيّق نطاقه، أو اطلب التقرير الذي يقرؤه الوكلاء:

```bash
php artisan larecipe:validate --set=docs --docs-version=3.x
php artisan larecipe:validate --json
```

خيار الإصدار هو `--docs-version` لأن Symfony يحجز `--version` للتطبيق. وإن طلبت مجموعة أو إصدارًا غير موجود، يخرج الأمر بالرمز `2`.

لكل مشكلة معرّف قاعدة ثابت، مثل `links.broken` أو `components.unknown`. ولا يختلف التحقق والتصيير أبدًا: قواعد المكوّنات والمقتطفات والمراجع والعناصر النائبة تأتي مباشرة من أداة التصيير، التي تبلّغ عن كل مشكلة تصادفها أثناء تصيير الصفحة.

<AccordionGroup>

<Accordion title="كل القواعد">

قواعد الصفحة تعمل على كل صفحة، وقواعد المجموعة تعمل على مجموعة التوثيق كلها.

| القاعدة | المستوى | ما الذي تكشفه |
| --- | --- | --- |
| `page.empty` | error | صفحة بلا محتوى. |
| `frontmatter.schema` | error, warning | ترويسة صفحة ليست YAML صالحًا أو فيها قيم بشكل خاطئ (error)، أو مفتاح مجهول يبدو خطأً إملائيًا في مفتاح معروف، مثل `descripton` (warning). |
| `frontmatter.description.missing` | warning | صفحة بلا `description`. غيابه يُضعف `llms.txt` ومقتطفات البحث و `list_pages`. |
| `components.unknown` | error | وسم مكوّن لا يعرّفه أي شيء. يُصيَّر نصًا مُهرَّبًا. |
| `components.props` | error | خاصية (prop) مفقودة أو غير صالحة، أو صفحة يفشل تصييرها. |
| `references.missing` | error | مرجع حي (`<Route>` و `<Artisan>` و `<Model>` و `<Event>`) لم يعد هدفه موجودًا. |
| `snippets.missing` | error | تضمين مقتطف اختفى ملفه أو منطقته. |
| `snippets.denied` | error | تضمين من خارج جذر المقتطفات، أو لملف قد يحوي أسرارًا. |
| `snippets.disabled` | error | تضمين في مجموعة بلا جذر مقتطفات (مجموعات قاعدة البيانات، ما لم تضبط لها جذرًا). |
| `media.placeholder` | warning | عنصر نائب لفيديو: مكوّن `Video` فيه الخاصية `placeholder` ولا `src` له بعد. |
| `links.broken` | error | رابط إلى صفحة أو مرساة غير موجودة: روابط `.md` النسبية، والروابط المطلقة إلى أي إصدار أو لغة في المجموعة، و `#anchors` في الصفحة نفسها. والرابط إلى صفحة لم تُترجم بعد لا بأس به: يحصل القرّاء على النسخة الأصلية، فيعود المدقّق إلى اللغة الافتراضية بالطريقة نفسها. |
| `links.private-from-public` | warning | صفحة عامة تشير إلى صفحة خاصة، فيحصل كل زائر على 404. |
| `links.unsafe` | error | رابط في `navigation.json` أو شريط التنقل العلوي أو التذييل أو عنوان المستودع، ليس `http` ولا `https` ولا `mailto` ولا مسارًا في الموقع. الصفحات تحذفه. |
| `markdown.size` | warning | صفحة نسختها بصيغة Markdown للوكلاء أكبر من `agents.markdown_budget` (50,000 حرف افتراضيًا)، وهو الحد الذي يبدأ عنده الوكلاء بالاقتطاع. |
| `manifest.schema` | error, warning | ملف `larecipe.json` ليس JSON صالحًا أو يخالف المخطط (error)؛ أو غياب `larecipe.json`، أو مفتاح يبدو خطأً إملائيًا (warning). |
| `manifest.integrations` | warning | تكاملات التحليلات `integrations` في ملف `larecipe.json` لمجموعة قاعدة بيانات، وهي تتجاهلها. انقلها إلى `sets.{key}.integrations` في `config/larecipe.php`. |
| `assets.missing` | error | ملف أنماط مذكور تحت `assets.css` وليس بجوار `larecipe.json`. |
| `navigation.schema` | error | ملف `navigation.json` ليس JSON صالحًا أو يخالف المخطط. |
| `navigation.missing-page` | error | عنصر في التنقل لا صفحة خلفه. ويجوز لملف `navigation.json` الخاص بلغة ما أن يذكر صفحات لم تُترجم بعد: فهي تعود إلى النسخة الأصلية. |
| `pages.orphan` | warning | صفحة يتركها `navigation.json` ولا يخفيها، فلا يصل إليها القرّاء إلا بعنوانها. |
| `pages.duplicate` | error | ملفان يصيران عنوان URL واحدًا، مثل `guides.md` و `guides/index.md`. |
| `redirects.loop` | error | عمليات إعادة توجيه (في `larecipe.json` أو `redirect_from`) تعود إلى حيث بدأت. |
| `redirects.shadowed` | warning | إعادة توجيه من عنوان ما زالت فيه صفحة. الصفحة تفوز، فلا تعمل إعادة التوجيه أبدًا. |
| `search.budget` | warning | إصدار سيتجاوز فهرس البحث في المتصفح الخاص به `search.budget_kb`. فقط مع [مشغّل البحث](search.md) `client`. |
| `skills.spec` | error, warning | ملف `skills/{name}/SKILL.md` مكتوب يدويًا يخالف مواصفات Agent Skills (error)، أو يتجاوز 500 سطر (warning). |
| `cache.store` | error | مجموعة قاعدة بيانات على مخزن الذاكرة المؤقتة `array` أو `null`، خارج الاختبارات. تقرأ القاعدة `source.cache` الخاص بالمجموعة أولًا. |
| `access.gate-syntax` | error | وسم `<Can>` أو `<Cannot>` لم يستطع المحلّل قراءته: داخل جملة أو جدول أو عنوان، أو مقسوم على عدة أسطر، أو بأحرف صغيرة. والكتلة المحيطة به مخفية عن كل القرّاء حتى يُصلح. |
| `access.unknown-ability` | warning | كتلة صلاحيات (gate block) تسأل عن صلاحية (ability) لم يعرّفها تطبيقك أبدًا، فترفض كل القرّاء. ويذكر الإصلاح أقرب صلاحية معرَّفة. |
| `access.redundant-gate` | warning | كتلة صلاحيات في صفحة خاصة أصلًا، اجتاز قرّاؤها `viewLarecipe` من قبل. |
| `migrations.conflict` | warning | ترحيل توثيق صادف تغييرًا أُجري في الإنتاج، وينتظر بوصفه تعارضًا في [قائمة المراجعة (The pass)](../sources/database.md#the-pass-the-review-queue). ويذكر الإصلاح عنوانه في لوحة الإدارة. |

</Accordion>

<Accordion title="تقرير JSON">

يطبع `--json` التقرير الذي يقرؤه الوكلاء. وشكله منشور مخططَ JSON Schema، في الحزمة على `resources/schemas/validate.schema.json`، وفي كل مجموعة على `/schemas/validate.json` (تحت بادئة المجموعة).

أضفنا إلى هذه الصفحة للحظة رابطًا فيه خطأ إملائي في مرساته، فعاد هكذا:

```json
{
    "ok": false,
    "summary": { "pages": 41, "errors": 48, "warnings": 3 },
    "issues": [
        {
            "level": "error",
            "rule": "links.broken",
            "set": "docs",
            "page": "3.x/operate/validation",
            "file": "3.x/operate/validation.md",
            "line": 8,
            "message": "Link to caching.md#the-cache-stor: anchor #the-cache-stor not found.",
            "fix": "Use #the-cache-store."
        }
    ]
}
```

أما الأخطاء الـ 47 الأخرى فكانت روابط وعناصر تنقل لصفحات لم تُكتب بعد.

- قيمة `ok` هي `false` حين يوجد خطأ واحد على الأقل.
- قيمة `page` هي `{locale}/{version}/{path}`، من دون اللغة الافتراضية، ومن دون الإصدار في مجموعة بلا إصدارات.
- قيمة `file` نسبية إلى جذر المجموعة (`docs/` هنا)، و `line` يشير إلى سطر في ذلك الملف، مع احتساب ترويسة الصفحة.
- يوجد `fix` حين يستطيع LaRecipe أن يقترح إصلاحًا: أقرب مرساة، أو أقرب صفحة، أو مفتاح ترويسة الصفحة الذي قصدته على الأرجح. عامله على أنه اقتراح. فحين يشير الرابط إلى صفحة لم توجد بعد، تكون أقرب صفحة عادةً هي الإجابة الخاطئة.

</Accordion>

<Accordion title="كيف يستخدم الوكلاء التقرير">

بُني التقرير للوكيل الذي كتب الصفحة قبل لحظة. معرّفات القواعد الثابتة تخبره بنوع المشكلة، و `file` و `line` يخبرانه بمكانها، و `fix` يخبره كثيرًا بما يغيّره. وتطلب المهارتان `larecipe-write-docs` و `larecipe-update-docs` من الوكلاء أن يشغّلوا المدقّق ويصلحوا كل خطأ قبل أن يتوقفوا.

ويستطيع الوكلاء المتصلون بخادم MCP أن يستدعوا الأداة `validate_docs` بدلًا من الأمر. تعيد التقرير نفسه الذي يعيده `--json`، لمجموعة واحدة ولإصدار واحد اختياريًا. وهي متاحة دائمًا على الخادم المحلي (`php artisan larecipe:mcp`). أما على نقطة النهاية عبر الويب لمجموعة ما، فلا تُعرض إلا على من تسمح لهم البوابة (gate) `manageLarecipe`، لأنها تقرأ كل الصفحات، والخاصة منها أيضًا.

وحين يكتب وكيل صفحة عبر أدوات التأليف في MCP، تعمل قواعد الصفحة نفسها على تلك الصفحة أولًا، ولا تُكتب صفحة فيها أخطاء. ومشكلات الروابط في تلك المرحلة تحذيرات فقط، لأن الوكيل الذي يكتب عدة صفحات يضع رابطًا إلى الصفحة التالية قبل أن توجد. ثم يلتقط تشغيل `larecipe:validate` الكامل ما بقي معطّلًا.

</Accordion>

<Accordion title="كل الخيارات">

<Artisan command="larecipe:validate" />

</Accordion>

</AccordionGroup>

## في CI

تكفي خطوة واحدة. رمز الخروج يُفشل المهمة عند الأخطاء:

```yaml
- run: php artisan larecipe:validate
```

مع GitHub Actions، يستطيع المثبّت أن يكتب سير عمل جاهزًا يذكر أيضًا الصفحات التي تركها pull request قديمة: راجع [فحص CI](../keeping-current/ci-check.md).

## larecipe:doctor

يقيّم doctor كل مجموعة توثيق كما تقيّم فحوص AFDocs موقع توثيق للوكلاء، لكن من داخل تطبيقك: نسخ Markdown، و `llms.txt` وروابطه، وصفحات 404، وترويسات الاكتشاف، وعمليات إعادة التوجيه، و `skill.md`، ونقطة نهاية MCP، ومخزن الذاكرة المؤقتة. يرسل طلبات حقيقية عبر نواة HTTP في التطبيق، بوكيل المستخدم `LaRecipe-Doctor/1.0`، فلا يحتاج إلى خادم يعمل، ويرى الوسائط (middleware) والترويسات ومعالج الاستثناءات لديك تمامًا كما يراها وكيل. وتُبنى عناوين URL من `APP_URL`.

كل فحص إما ينجح أو يحذّر أو يفشل أو يُتخطّى، مع إصلاح لكل ملاحظة، وتحصل المجموعة على درجة من A إلى D. ولا يخرج الأمر بالرمز `1` إلا حين يفشل فحص. يحصره `--set` في مجموعة واحدة، ويطبع `--json` التقرير.

<AccordionGroup>

<Accordion title="الفحوص">

| الفحص | ينجح حين |
| --- | --- |
| `llms-txt` | يُقدَّم `llms.txt` (و `llms.txt` الخاص بالإصدار الافتراضي في مجموعة ذات إصدارات) نصًا، ويبقى دون 50,000 حرف، وله شكل llms.txt: عنوان وملخّص وأقسام. |
| `llms-txt-links` | ما يصل إلى 10 روابط صفحات من `llms.txt` تجيب بـ Markdown. |
| `markdown` | أول خمس صفحات بترتيب القراءة تجيب بـ Markdown على عنوان `.md` الخاص بها وعلى `Accept: text/markdown`، و 95% على الأقل من كلمات كل مقالة تظهر في نسختها بصيغة Markdown. وما بين 80% و 95% تحذير: عادةً مكوّن مخصّص بلا صيغة Markdown. |
| `not-found` | الصفحة المفقودة `404` حقيقي في HTML وفي Markdown، مع محتوى بصيغة Markdown. |
| `discovery` | يرسل HTML ترويسات `Link` إلى نسخة Markdown وإلى `llms.txt`، ويرسل `Vary: Accept`، ويضع رابطًا إلى `llms.txt` في متن الصفحة. |
| `redirects` | كل إعادة توجيه في `larecipe.json`، والعنوان غير المحدد بإصدار في مجموعة ذات إصدارات، يوجّه إلى صفحة تجيب بـ `200`. |
| `skill-md` | يُقدَّم `skill.md` بصيغة Markdown مع `name` و `description` صالحين. |
| `mcp` | تجيب نقطة نهاية MCP للمجموعة على `tools/list` بالأداتين `search_docs` و `read_page`. ويُتخطّى حين تطلب نقطة النهاية مصادقة. |
| `cache-store` | تستخدم مجموعة قاعدة البيانات مخزن ذاكرة مؤقتة دائمًا. ويُتخطّى لمجموعات المستودع. |

</Accordion>

<Accordion title="كيف تُحسب الدرجة">

الفحص الناجح يُحتسب كاملًا، والتحذير نصفه، والفشل لا شيء؛ والفحوص المتخطّاة لا تُحتسب. ثم تتحول النتيجة إلى درجة: A من 90، و B من 80، و C من 70، و D من 60.

</Accordion>

<Accordion title="تشغيل doctor على هذا الموقع">

من النسخة المحلية:

```text
  docs http://127.0.0.1:8780/

  PASS  llms.txt: llms.txt is served and every index is under 50,000 characters.
  PASS  llms.txt links: 10 sampled llms.txt links resolve to Markdown.
  WARN  Markdown twins: Only 93% of the words on http://127.0.0.1:8780/3.x are in its Markdown.
        Fix: Give custom components a Markdown form (LaRecipe::component(...)->markdown(...)).
  PASS  Missing pages: A missing page is a real 404, with a Markdown body for agents.
  PASS  Discovery hints: Pages send Link headers to their Markdown twin and llms.txt, and link to llms.txt in the page.
  PASS  Redirects: 1 redirects land on a page.
  PASS  skill.md: skill.md is served as the "larecipe" skill.
  PASS  MCP endpoint: http://127.0.0.1:8780/mcp serves search_docs, read_page, list_pages, submit_feedback.
  SKIP  Cache store: Repository sets read from files; no cache store is needed.

  Agent readiness: A (94 / 100)
```

</Accordion>

<Accordion title="كل الخيارات">

<Artisan command="larecipe:doctor" />

</Accordion>

</AccordionGroup>

## أيهما ومتى

| | `larecipe:validate` | `larecipe:doctor` |
| --- | --- | --- |
| يقرأ | التوثيق، عبر أداة التصيير | استجابات HTTP من التطبيق |
| يكشف | الأخطاء في المحتوى | المشكلات في ما يُقدَّم: الترويسات والمسارات وصفحات 404 و MCP |
| يفشل عند | أي خطأ | فشل أحد الفحوص |
| شغّله | بعد كل تغيير في التوثيق، وقبل كل push، وفي CI | بعد تغيير المسارات أو الوسائط أو التخزين المؤقت أو إعدادات الوكلاء، وبعد النشر |

قد تجتاز الصفحة التحقق وتفشل مع ذلك في doctor: خادم وسيط يحذف ترويسات `Link`، أو معالج استثناءات يحوّل 404 إلى صفحة خطأ ناعمة، لا يظهران أبدًا في المحتوى.

## التالي

- [النشر](deployment.md): أين يأتي كل من الأمرين في عملية النشر.
- [فحص CI](../keeping-current/ci-check.md): تعمّق مع سير عمل GitHub Actions الجاهز.
- [واجهات الوكلاء](../agents/surfaces.md): الملفات التي يفحصها doctor.
