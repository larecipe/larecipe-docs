---
title: أوضاع المراجعة
description: اختر هل يسألك وكيلك قبل أن يحفظ تغييرات التوثيق في commit (required، الافتراضي) أم يحدّثها ويرفعها وحده (auto)، وما يعنيه كل وضع للتغييرات التي تجري خارج الـ pull request.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/Enums/ReviewMode.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/InstructionsRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/HookMessages.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Enums/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/WritePageAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/PublishDraftAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/RevertPageAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Models/Revision.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:8a58904c13979225f9c2f97ebb826e3c1521bc7e3f03b2f5ad9d1269f80d148e
---

# أوضاع المراجعة

يحدّد وضع المراجعة هل يسألك وكيلك قبل أن يحفظ تغييرات التوثيق في commit:

- **`required`** (الافتراضي): يعرض عليك الوكيل فروقات التوثيق (diff) وينتظر موافقتك قبل الـ commit.
- **`auto`**: يحدّث الوكيل التوثيق ويحفظه في commit ويرفعه من دون أن يسأل.

اضبطه بـ `LARECIPE_REVIEW=auto` في `.env`، أو اختره في المثبّت:

```bash
php artisan larecipe:install --review=auto
```

ثم شغّل `php artisan larecipe:agents`، لتذكر كتلة `AGENTS.md` ذلك (ومع Laravel Boost، `php artisan boost:update`). أما خطّاف pre-push فيقرأ الإعدادات مع كل push ولا يحتاج إلى شيء.

وفي الحالتين، يبقى تغيير التوثيق داخل الـ pull request الخاص بك، ولكل تغيير مراجعة (revision) محفوظة، ونسبةٌ إلى من أجراه، ويمكن التراجع عنه بخطوة واحدة.

## أين تراه {#where-you-see-it}

| | `required` (الافتراضي) | `auto` |
| --- | --- | --- |
| [كتلة `AGENTS.md`](agent-instructions.md) | "Review mode is `required`: show the user the docs diff and wait for approval before committing it." | "Review mode is `auto`: update the docs and push without asking." |
| رسالة [خطّاف pre-push](pre-push-hook.md) | "...run php artisan larecipe:validate, show the user the docs diff and wait for approval, then commit and push again." | "...run php artisan larecipe:validate, then commit and push again." |
| [المهارات](skills.md) | اتبع الوضع المذكور في `AGENTS.md`: اعرض الفروقات وانتظر. | اتبع الوضع المذكور في `AGENTS.md`: احفظ في commit وارفع. |

الإعداد هو `review` في `config/larecipe.php`. ويمكن لكل مجموعة أن تختار وضعها الخاص بـ `'review' => 'auto'` في إدخالها تحت `sets`. اكتب القيمة بأحرف صغيرة: فأي قيمة غير `auto` تُعدّ `required`.

ومع عدة مجموعات توثيق، لا تقول كتلة `AGENTS.md` إن الوضع `auto` إلا حين تكون كل المجموعات `auto`، وتطلب رسالة الخطّاف الموافقة حين تنتمي أي صفحة قديمة إلى مجموعة `required`. وعند الشك، يسأل الوكيل.

## في الـ pull request، الـ pull request هو المراجعة {#in-a-pull-request-the-pull-request-is-the-review}

تغيير التوثيق الذي يصل داخل pull request يُراجَع هناك، مع الكود، في الوضعين. فالوضع `auto` لا يتخطّى الـ pull request أبدًا؛ بل يتخطّى السؤال في جلستك.

- **مجموعة المستودع:** التغييرات ملفات Markdown في الفرع. ودمجها هو نشرها.
- **مجموعة قاعدة البيانات:** تنتقل التغييرات على شكل [ترحيل التوثيق](../sources/docs-migrations.md) الخاص بالفرع. وحين يطبّقه `php artisan migrate` عند النشر، تُنشر تغييراته. وإن تغيّرت صفحة في الإنتاج بعد كتابة الترحيل، فلا يستبدلها الترحيل أبدًا: تنتظر نسخته على شكل مسودة تعارض حتى يحسمها شخص.

## خارج الـ pull request: مسودات أو مراجعات منشورة {#outside-a-pull-request-drafts-or-published-revisions}

لا تقبل التغييرات من خارج الـ pull request إلا مجموعة قاعدة البيانات، مثل التغييرات من وكيل على خادم MCP عبر الويب، أو من [`larecipe:sync`](sync.md) على الإنتاج. وهناك يحدّد الوضع ما يُنشر: تحت `required` ينتظر تغيير الوكيل على شكل مسودة حتى ينشره شخص، وتحت `auto` يُنشر فورًا. أما مجموعات المستودع فلا تقبل مثل هذه التغييرات أبدًا: فمكان تغييراتها هو git.

<Accordion title="من ينشر ماذا، وكيف تفعّل أدوات التأليف عبر الويب">

| من يجري التغيير | `required` | `auto` |
| --- | --- | --- |
| وكيل يستخدم أدوات التأليف في خادم MCP عبر الويب | مسودة | منشور |
| `larecipe:sync` حين يكتب مباشرة في مجموعة قاعدة البيانات | مسودة | منشور |
| شخص في [لوحة الإدارة](../sources/database.md#the-admin-ui) | منشور، أو مسودة إن اختار ذلك | كذلك |
| ترحيل توثيق يطبّقه `php artisan migrate` | منشور | كذلك |
| جلسة البرمجة المحلية لديك، عبر خادم MCP المحلي | يُطبَّق على قاعدة بياناتك المحلية ويُكتب في ترحيل التوثيق الخاص بالفرع | كذلك |

يرفض خادم MCP عبر الويب أدوات التأليف لمجموعات المستودع. وفي مجموعة قاعدة البيانات تكون متوقفة افتراضيًا: فعّلها بـ `mcp.web.author_tools`، ولا يستخدمها إلا مستدعٍ مسجّل الدخول تسمح له البوابة (gate) `manageLarecipe`:

```php
// config/larecipe.php
'sets' => [
    'handbook' => [
        // route, source and access as usual
        'mcp' => ['web' => ['author_tools' => true]],
    ],
],
```

تذكر كل أداة تأليف ما حدث في `outcome` الخاص بها: `published`، أو `drafted`، أو `conflicted` (تغيّرت الصفحة منذ قرأها الوكيل)، أو `unchanged`، أو `rejected` (أخطاء تحقق؛ لم يُكتب شيء). وتعيد معه المراجعة وتقرير التحقق. راجع [خادم MCP](../agents/mcp.md).

تنتظر المسودات في قائمة مراجعة حتى ينشرها شخص أو يرفضها. والنشر يتحقق من المسودة مرة أخرى أولًا، لأن القواعد قد تكون تغيّرت منذ كُتبت. وتعمل على القائمة في لوحة الإدارة، في [قائمة المراجعة (The pass)](../sources/database.md#the-pass-the-review-queue)، حيث يمكن للمراجع أيضًا أن يعدّل المسودة قبل نشرها. وتحت `auto`، تسرد قائمة المراجعة ما نشره الوكلاء مؤخرًا، وكلٌّ منه على بُعد نقرة واحدة من التراجع.

</Accordion>

## تفاصيل {#details}

<Accordion title="مراجعات محفوظة، ونسبة إلى صاحبها، وإمكانية تراجع، في كل وضع">

- **مراجعات محفوظة.** في مجموعة المستودع، كل تغيير هو commit. وفي مجموعة قاعدة البيانات، كل تغيير صفٌّ في `larecipe_revisions` يحمل الحالة الكاملة التي يؤدي إليها، ولا يُعدَّل بعد ذلك أبدًا إلا في حالة مراجعته.
- **نسبة إلى صاحبها.** في مجموعة المستودع، يقرأ LaRecipe آخر تغيير من git: راجع [بيانات التحديث (provenance)](../sources/repository.md). وفي مجموعة قاعدة البيانات، تسجّل كل مراجعة هل أجراها شخص أم وكيل (ويُسمّى الوكيل باسم عميل MCP الخاص به)، والقناة التي جاءت منها (`pull_request` أو `admin_ui` أو `remote_agent` أو `sync` أو `migration`)، وترحيل التوثيق، وملخصًا من سطر واحد. وتعرض الصفحات ذلك في بيانات التحديث.
- **إمكانية التراجع.** في مجموعة المستودع، تراجع عن الـ commit. وفي مجموعة قاعدة البيانات، يعيد التراجع مراجعة سابقة على شكل مراجعة منشورة جديدة، ويَسِم المراجعة التي يحلّ محلّها بأنها متراجَع عنها، فيُظهر السجل ما حدث بالضبط، ويمكن التراجع عن التراجع نفسه.

</Accordion>

## التالي {#next}

- [خطّاف pre-push](pre-push-hook.md): حيث يظهر الوضع لحظة الـ push.
- [مصدر قاعدة البيانات](../sources/database.md): تعمّق أكثر في المسودات والمراجعات ولوحة الإدارة.
