---
title: كيف يعمل
description: تذكر كل صفحة الكود الذي تشرحه، ويربط larecipe:affected كل تغيير بالصفحات التي جعلها قديمة، ثم يحدّثها وكيل البرمجة في الـ push نفسه مع الكود.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:c7ecd2534c511223394c30926539001b2a810c35053df4fd270937aa19666451
---

# كيف يعمل

تذكر كل صفحة الكود الذي تشرحه في ترويستها (frontmatter)، تحت `sources:`. وحين يتغيّر الكود، يربط `php artisan larecipe:affected` الملفات التي تغيّرت بالصفحات التي تشرحها، من دون ذكاء اصطناعي ومن دون اتصال بالشبكة. ثم يحدّث وكيل البرمجة الذي أجرى التغيير تلك الصفحات قبل أن يرفع عمله، فيصل التوثيق في الـ pull request نفسه مع الكود.

لا تشغّل شيئًا من هذا بيدك. المثبّت يُعدّه، ووكيلك يتبعه.

```text
Change the code
   ↓
Your agent updates the affected pages in the same pull request
   ↓
You review them with the code (or let them through, in auto mode)
   ↓
Every reader, human or agent, gets the current answer
```

## حدّد ما توثّقه الصفحة {#declare-what-a-page-documents}

اذكر الكود الذي توثّقه الصفحة على شكل مسارات أو أنماط glob تبدأ من جذر المشروع:

```yaml
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources:
  - app/Webhooks/**
  - routes/webhooks.php
---
```

الصفحة التي لا تحوي `sources:` لا يراها كشف الانحراف (drift). ويقترح [`larecipe:sources:suggest`](#find-sources-for-pages-that-have-none) مصادرها مما تذكره الصفحة أصلًا.

<Accordion title="صيغة glob والمناطق">

- `*` يبقى داخل مجلد واحد، و `**` يعبر المجلدات، و `?` حرف واحد، و `{a,b}` يختار واحدًا من عدة بدائل، و `[abc]` فئة من الأحرف: وهي صيغة `.gitignore` و GitHub Actions نفسها.
- المسار الذي لا يحوي رموز تعميم يطابق ذلك الملف، أو كل ما بداخله إن كان اسم مجلد. فـ `app/Billing` و `app/Billing/**` سواء.
- يحصر `path#region` المصدر في الأسطر الواقعة بين `#region webhooks` وأول `#endregion` بعده في ذلك الملف، أيًّا كانت بادئة التعليق قبلهما (`// #region webhooks`). اكتبه هكذا: `routes/api.php#webhooks`. ولا تتأثر الصفحة إلا حين يمسّ التغيير تلك الأسطر. وحين لا يجد LaRecipe المنطقة أو الأسطر التي تغيّرت، يُحتسب الملف كله: فحكم «قديمة» الخاطئ يكلّفك نظرة، أما حكم «سليمة» الخاطئ فيكلّفك صفحة خاطئة.
- التغييرات داخل مجلد التوثيق لا تُحتسب أبدًا تغييرات في الكود بالنسبة إلى الصفحات الأخرى.

</Accordion>

## التوثيق يصل مع الكود {#docs-ship-with-the-code}

يصل التوثيق في الـ push نفسه مع الكود الذي يشرحه: ملفات Markdown في مجموعة المستودع، أو [ترحيل توثيق](../sources/docs-migrations.md) في مجموعة قاعدة البيانات. فلا pull request منفصل للتوثيق، ولا وكيل ذكاء اصطناعي يعمل في CI.

والسبب هو السياق. فالوكيل الذي غيّر الكود يعرف الآن بالضبط ما الذي تغيّر ولماذا. وبعد ساعة، أو في pull request لاحق يكتبه شخص آخر، يضيع ذلك السياق.

## الطبقات {#the-layers}

أربع طبقات، كل ما فوق الأولى منها اختياري. وكلها تطرح السؤال نفسه على الكود نفسه، فلا تختلف أبدًا في تحديد الصفحات القديمة.

| الطبقة | ما هي | متى تعمل | ما تكتبه |
| --- | --- | --- | --- |
| L0، النواة الحتمية | ترويسة `sources:` و `larecipe:affected` و `larecipe:validate` | تعمل دائمًا | لا شيء |
| L1، تعليمات في المستودع | كتلة LaRecipe في `AGENTS.md` (أو إرشادات Laravel Boost)، والمهارات الأربع، وخادم MCP المحلي | في بداية كل جلسة برمجة بالذكاء الاصطناعي | الوكيل يكتب التوثيق |
| L2، الفحص قبل الـ push | خطّاف للوكيل على `git push` و `gh pr create` يوقف الـ push مرة واحدة ويسرد الصفحات القديمة، وخطّاف git اختياري للأشخاص | حين يوشك التغيير أن يغادر الجهاز | لا شيء |
| L3، فحص CI الاختياري | `larecipe:validate` و `larecipe:affected` على كل pull request | عند الـ pull requests | لا شيء |

- **L1** نص عادي تقرؤه كل جلسة ذكاء اصطناعي. راجع [تعليمات الوكلاء](agent-instructions.md) و [المهارات](skills.md).
- **L2** يلتقط اللحظة التي ينسى فيها الوكيل، ولا يستدعي نموذجًا لغويًا أبدًا. راجع [خطّاف pre-push](pre-push-hook.md).
- **L3** شبكة أمان لعمليات الـ push التي تجري من دون وكيل. راجع [فحص CI](ci-check.md).

يكتب الوكيل التوثيق لأن السياق عنده، ولا شيء بعده يكتبه نيابةً عنه. وإن أردت أن يكتب وكيلٌ مسودة التحديث من دون جلسة محلية، فشغّل [`larecipe:sync`](sync.md) عند الحاجة.

<Accordion title="إعدادات مقترحة">

| الفريق | فعّل | ثبّت بالأمر |
| --- | --- | --- |
| مطوّر منفرد مع Claude Code أو Codex | L0 و L1 و L2 (خطّاف الوكيل) | `php artisan larecipe:install` |
| فريق يراجع الـ pull requests | L0 و L1 و L2 (خطّاف الوكيل، مع خطّاف git لمن يرفع من دون وكيل)، و L3 في وضع التعليق أو الإفشال | `php artisan larecipe:install --git-hook=warn --ci=comment` |
| لا وكلاء برمجة محليين | L0 و L2 (خطّاف git) و L3، و `larecipe:sync` عند الحاجة حين يريد أحد أن يكتب وكيلٌ مسودة التحديث | `php artisan larecipe:install --agents=none --git-hook=warn --ci=fail` |
| مجموعة قاعدة بيانات | L0 و L1 و L2 (يكتب خادم MCP المحلي ترحيلات التوثيق في الـ push نفسه) و L3 | `php artisan larecipe:install --driver=database --ci=fail` |

كل خيار مشروح في صفحة [التثبيت](../get-started/installation.md). ويمكنك أن تضيف طبقة لاحقًا بتشغيل المثبّت مرة أخرى، أو بالأمر الخاص بتلك الطبقة.

</Accordion>

## larecipe:affected {#larecipeaffected}

يجيب `larecipe:affected` عن سؤال واحد: أي صفحات التوثيق يمسّها هذا التغيير، وهل حدّثها؟ وهذا ما يشغّله وكيلك قبل أن يرفع:

```bash
php artisan larecipe:affected --uncommitted --base=main
```

```text
LaRecipe: 4 files changed since main, plus uncommitted work.

Stale (the code changed, the page did not):
  - docs/billing/webhooks.md   (sources: app/Webhooks/**)

Updated in this change:
  - docs/billing/refunds.md   (sources: app/Billing/Refund.php)

Drifted earlier (what they document changed after they were last updated):
  - docs/installation.md   its sources changed in 3f2a9c1 (2026-10-02): Require PHP 8.4

Update the stale pages with the larecipe-update-docs skill, then run php artisan larecipe:validate.
```

- **Stale** (قديمة): ملف تغيّر يطابق `sources:` الصفحة، والصفحة نفسها لم تتغيّر في النطاق نفسه. هذه هي الصفحات التي يجب تحديثها.
- **Updated** (محدّثة): تغيّرت الصفحة في نطاق الكود نفسه: ملف Markdown الخاص بها، أو، في مجموعة قاعدة البيانات، ترحيل توثيق تغيّر ويذكرها.
- **Drifted earlier** (انحرفت سابقًا): صفحات لا يمسّها هذا التغيير، لكن مصادرها تغيّرت في commit بعد آخر تحديث لها. يذكر LaRecipe ذلك الـ commit، فترى متى تأخّرت الصفحة. ويتخطّى `--no-drift` هذا الجزء.

الفحص نفسه متاح في الأداة `affected_pages` على [خادم MCP](../agents/mcp.md) المحلي.

<AccordionGroup>

<Accordion title="اختر النطاق">

| تشغّل | فيقارن |
| --- | --- |
| `larecipe:affected` | العمل الذي لم يُحفظ في commit فقط: الملفات المجهّزة (staged) وغير المجهّزة (unstaged) وغير المتتبَّعة (untracked). |
| `larecipe:affected --base=main` | الـ commits على هذا الفرع منذ تفرّع عن `main` (`main...HEAD`)، من دون العمل الذي لم يُحفظ. |
| `larecipe:affected --base=main --uncommitted` | الاثنين معًا. وهذا ما يشغّله وكيلك قبل أن يرفع. |
| `larecipe:affected --base=main --head=feature/refunds` | الـ commits على فرع آخر، من دون الانتقال إليه. |

حين لا يكون الأساس فرعًا أو commit معروفًا، يذكر التقرير ذلك ويقترح `git fetch origin`. وفي CI يكون الأساس هو الفرع الذي يستهدفه الـ pull request: `--base=origin/${{ github.base_ref }}`.

</Accordion>

<Accordion title="الصيغ: text و json و github و markdown">

يحدّد `--format` من يقرأ التقرير:

- `text` (الافتراضية): للأشخاص، كما في المثال أعلاه.
- `json`: للبرامج والوكلاء. فيه `summary` بالأعداد، و `changed_files`، وكل صفحة متأثرة مع `status` (`stale` أو `updated`) و `reason` و `matched_sources` و `changed_files`، وقائمة `drift` مع commit `stale_since` لكل صفحة، وأي `notes`.
- `github`: تعليقات توضيحية (annotations) في GitHub Actions. تصبح كل صفحة قديمة تحذيرًا على ملفها (وخطأً مع `--fail-on-stale`)، ويصبح الانحراف إشعارًا، وداخل تشغيل GitHub Actions يضيف الأمر أيضًا تقرير Markdown إلى ملخص المهمة (job summary) ويضبط مُخرج الخطوة `stale`.
- `markdown`: تعليق على الـ pull request، فيه جدول بالصفحات القديمة، والمصادر التي طابقت، والملفات التي تغيّرت. ويضبط هو أيضًا مُخرج الخطوة `stale`.

تقرير `json` مختصر (تحمل كل صفحة أيضًا `version` و `locale` و `sources` كاملة):

```json
{
    "summary": { "changed_files": 4, "affected": 2, "stale": 1, "updated": 1, "drift": 1 },
    "pages": [
        {
            "set": "docs",
            "path": "billing/webhooks",
            "title": "Webhooks",
            "file": "docs/billing/webhooks.md",
            "status": "stale",
            "reason": "sources",
            "matched_sources": ["app/Webhooks/**"],
            "changed_files": ["app/Webhooks/Handler.php"]
        }
    ]
}
```

يجعل `--fail-on-stale` الأمر ينتهي بالرمز `1` حين يترك التغيير أي صفحة قديمة. ويستخدمه [فحص CI](ci-check.md) في وضع الإفشال.

</Accordion>

<Accordion title="المجموعات والإصدارات والترجمات">

- يفحص `--set=handbook` مجموعة توثيق واحدة بدلًا من المجموعات كلها.
- يفحص `--docs-version=2.x` إصدارًا آخر، ويفحص `--docs-version=all` كل الإصدارات. وافتراضيًا يُفحص الإصدار الافتراضي لكل مجموعة.
- يفحص `--locale=ar` صفحات لغة ما مقابل اللغة الافتراضية:

```bash
php artisan larecipe:affected --uncommitted --base=main --locale=ar
```

تكون الترجمة قديمة حين يتغيّر الكود الذي توثّقه (والترجمة التي لا تحوي `sources:` خاصة بها تستخدم مصادر صفحتها الأصلية)، أو حين تتغيّر صفحتها الأصلية في النطاق ولا تتغيّر هي. وتحت الانحراف، يسرد الترجمات التي تغيّرت صفحتها الأصلية في commit بعد آخر تحديث للترجمة. راجع [اللغات](../writing/languages.md).

</Accordion>

<Accordion title="مجموعات قاعدة البيانات">

صفحات مجموعة قاعدة البيانات صفوفٌ في جدول، لذا يقرأ `larecipe:affected` قيم `sources:` الخاصة بها من قاعدة البيانات المحلية. و«Updated» هنا تعني أن ترحيل توثيق تغيّر في مجلد الترحيلات ويذكر الصفحة، وفي الانحراف يكون «آخر تحديث» هو وقت تحديث الصفحة نفسها. وحين لا تحوي قاعدة البيانات المحلية أي صفحات، يذكر التقرير ذلك. حدّثها من نسخة مصدَّرة من الإنتاج بالأمر `larecipe:pull` (يأخذ `--from` حزمة أُنشئت على الإنتاج بالأمر `larecipe:source:export`). راجع [ترحيلات التوثيق](../sources/docs-migrations.md#start-from-what-is-live).

</Accordion>

<Accordion title="كل الخيارات">

مقروءة مباشرة من سجلّ الأوامر في هذا الموقع:

<Artisan command="larecipe:affected" />

</Accordion>

</AccordionGroup>

## ابحث عن مصادر للصفحات التي لا مصادر لها {#find-sources-for-pages-that-have-none}

يقترح `larecipe:sources:suggest` قيم `sources:` للصفحات التي لا تذكر أي مصدر، استنادًا إلى الأصناف (classes) والأوامر والمسارات التي تذكرها. أضف `--apply` ليكتبها في الصفحات:

```bash
php artisan larecipe:sources:suggest
```

<Accordion title="ما الذي يقرؤه، وخياراته">

يبحث عن:

- أسماء الأصناف الكاملة في الكود المضمَّن في النص، مثل `App\Billing\Invoice` أو `App\Billing\Invoice::refund()`؛
- أوامر artisan في الكود المضمَّن (`orders:prune` و `php artisan orders:prune`) وفي كتل كود الطرفية؛
- أسماء المسارات، بصيغة `route('orders.store')` أو `orders.store` وحدها؛
- المراجع الحية: `<Route name="...">` و `<Artisan command="...">` و `<Model name="...">` (أسماء مختصرة في `App\Models`) و `<Event name="...">` (أسماء مختصرة في `App\Events`).

يسأل التطبيقَ العامل أين عُرّف كلٌّ منها (خريطة الأصناف في Composer، ونواة الطرفية، والموجّه)، ولا يحتفظ إلا بالملفات الموجودة داخل مشروعك، ولا شيء من `vendor/` أبدًا. ومن دون `--apply` يطبع اقتراحاته:

```text
docs/billing/refunds.md
  sources: ["app/Console/Commands/PruneRefunds.php","app/Http/Controllers/RefundController.php"]
    app/Console/Commands/PruneRefunds.php  `refunds:prune`
    app/Http/Controllers/RefundController.php  <Route name="refunds.store" />
```

- يضيف `--apply` الاقتراحات إلى صفحات المستودع التي لا تذكر مصادر بعد. فيُدرج سطر `sources:` واحدًا ويترك بقية الترويسة كما كانت تمامًا. ويعرض `--dry-run` ما سيغيّره.
- يحصره `--page=billing/refunds` في صفحة واحدة، و `--set` في مجموعة توثيق واحدة، ويقترح `--all` مصادر أيضًا للصفحات التي تذكر مصادرها أصلًا.
- يطبع `--json` الاقتراحات لوكيل.
- يقرأ الإصدار واللغة الافتراضيين لكل مجموعة. ولا يعدّل `--apply` صفحات قاعدة البيانات أبدًا: حدّث `sources:` فيها عبر أدوات التأليف في MCP.

<Artisan command="larecipe:sources:suggest" />

</Accordion>

## التالي {#next}

- [تعليمات الوكلاء](agent-instructions.md): كتلة `AGENTS.md` التي تقرؤها كل جلسة ذكاء اصطناعي.
- [خطّاف pre-push](pre-push-hook.md): ما يحدث حين يرفع وكيلٌ تغييرًا ترك التوثيق قديمًا.
- [فحص CI](ci-check.md): تعمّق أكثر مع شبكة أمان لعمليات الـ push التي تجري من دون وكيل.
