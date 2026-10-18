---
title: تعليمات الوكلاء
description: كتلة LaRecipe في AGENTS.md التي تطلب من كل جلسة برمجة بالذكاء الاصطناعي أن تحدّث التوثيق المتأثر قبل الـ push، مع فهرس مختصر لصفحاتك، وتُولَّد من إعداداتك.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/InstructionsRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildInstructionsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteInstructionsBlockAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildDocsIndexAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/DocsIndexRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/MarkedBlock.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/PushRange.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/LinkClaudeMdAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Laravel/BoostGuideline.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsMdCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SyncSettings.php
  - vendor/larecipe/larecipe/resources/boost/guidelines/core.blade.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - AGENTS.md
translated_from: sha256:984ff7e239521632f80546c257388d1a54c61228ea5462209b306f132341ca97
---

# تعليمات الوكلاء

يقرأ Codex و Cursor و Copilot و Gemini CLI ومعظم وكلاء البرمجة الآخرين الملف `AGENTS.md`، في جذر مستودعك، في بداية كل جلسة. ويكتب `php artisan larecipe:install` قسم LaRecipe فيه، بين علامتين، فتتعلّم كل جلسة قاعدة واحدة: قبل الـ push، حدّث صفحات التوثيق التي يمسّها تغييرك.

يُولَّد هذا القسم من إعداداتك، فيذكر دائمًا مجلد التوثيق والفرع ووضع المراجعة الصحيحة. وتحته فهرس مختصر لصفحاتك، فيعرف الوكيل ما يغطيه التوثيق من دون أن يبحث أولًا. لا تعدّله بيدك أبدًا. وبعد أي تغيير في الإعدادات أو تحديث لـ LaRecipe، أعد توليده:

```bash
php artisan larecipe:agents
```

## ماذا يقول {#what-it-says}

- **أين يوجد التوثيق**، وأنه يصل في الـ push نفسه مع الكود.
- **أربع خطوات قبل كل push**: اسرد الصفحات المتأثرة بالأمر [`larecipe:affected`](how-it-works.md#larecipeaffected)، وحدّثها باستخدام [المهارة](skills.md) `larecipe-update-docs`، وشغّل `larecipe:validate`، ثم احفظ التوثيق في commit مع الكود.
- **وضع المراجعة**: في `required`، اعرض على المستخدم فروقات التوثيق (diff) وانتظر موافقته قبل الـ commit. وفي `auto`، حدّث التوثيق وارفعه من دون سؤال. راجع [أوضاع المراجعة](review-modes.md).
- **كيف تكتب**: للناس أولًا، مع الحالة الشائعة في أعلى كل صفحة والتفاصيل بعدها.
- **أين تقرأ التوثيق**: خادم MCP المسمّى `larecipe`، مع تذكير بأن قيم الإعدادات والبيئة لا تدخل التوثيق أبدًا.

النص قصير عن قصد. فهو يعمل مع وكلاء لم يسمع بهم LaRecipe قط، ولا يكلّف شيئًا، ويُبقي تغيير التوثيق داخل جلستك أنت، حيث تراه وتوجّهه.

<Accordion title="كتلة هذا الموقع">

قسم LaRecipe في ملف `AGENTS.md` الخاص بهذا الموقع، كما كتبه `larecipe:agents`، مع اختصار الفهرس إلى أول ثلاثة أسطر من 46:

```markdown
<!-- larecipe:start -->
## Documentation (LaRecipe)

This app's docs live in `docs/` and are served by LaRecipe. Docs ship in the same push as the code they describe.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs with the code and push them together.

Review mode is `required`: show the user the docs diff and wait for approval before committing it.

Write docs for people first: open each page with the common case in a few short lines, then put options, edge cases and internals after it (in an `<Accordion>` or on a linked page). Order pages from easy to advanced. Agents still read everything in the Markdown twin.

Search and read the docs with the `larecipe` MCP server. Never write configuration or environment values into docs.

### Docs index: LaRecipe (version 3.x)

46 pages. One line per page: path, title, description. Read a page with the `read_page` tool of the `larecipe` MCP server, or open its file in `docs/`. Refresh this index with `php artisan larecipe:agents-md`.

- `index`: Docs that keep up with your code. LaRecipe is a docs site that lives inside your Laravel app. Your coding agent writes the pages and updates them with…
- `upgrade`: Upgrade from v2. Move a LaRecipe 2 site to LaRecipe 3 with one command and a report, then finish the manual items with your coding agent…
- `agents/ask-ai`: Ask AI. Let readers ask a question and get a short answer written only from your docs, with every cited page checked, on your…
- ...
<!-- larecipe:end -->
```

</Accordion>

## نصّك يبقى لك {#your-text-stays-yours}

الملف `AGENTS.md` ملك فريقك. ولا يستبدل LaRecipe إلا ما يقع بين `<!-- larecipe:start -->` و `<!-- larecipe:end -->`: النص فوق الكتلة وتحتها يبقى كما هو بايتًا بايتًا مع كل إعادة توليد. وحين لا يحوي الملف كتلة بعد، يضيف LaRecipe واحدة في آخره بعد سطر فارغ. وحين لا يوجد `AGENTS.md`، ينشئه. واسم الملف هو `instructions.file` في `config/larecipe.php` (وهو `AGENTS.md` افتراضيًا).

## فهرس التوثيق {#the-docs-index}

تحت التعليمات، تحمل الكتلة سطرًا لكل صفحة، فيه مسارها وعنوانها ووصفها. فتكون الخريطة دائمًا في يد الوكيل، ويقرأ الصفحة بأداة MCP المسمّاة `read_page`، أو يفتح ملفها، حين يحتاج إليها. وفي التوثيق الداخلي (ملاحظات المعمارية، وأدلة التشغيل، ومسرد مصطلحات المجال) يكون هذا غالبًا الطريق الوحيد لتعرف الجلسة بوجوده.

حين تُضاف صفحات أو تُحذف أو يتغيّر عنوانها، حدّث الفهرس:

```bash
php artisan larecipe:agents-md
```

ونادرًا ما تحتاج إلى تذكّر ذلك: فـ [خطّاف pre-push](pre-push-hook.md) يطلب من الوكيل أن يشغّله حين يتأخر الفهرس.

<Accordion title="كيف يُبنى الفهرس، وخياراته">

- يفهرس الإصدار الافتراضي واللغة الافتراضية للمجموعة الافتراضية. ويفهرس `--set=handbook` مجموعة أخرى.
- تُستبعد الصفحات المخفية. وتأتي الصفحة الرئيسية أولًا، ثم صفحات المستوى الأعلى، ثم صفحات كل مجلد، بترتيب أبجدي.
- تُقصّ الأوصاف عند 120 حرفًا.
- يبقى ضمن `instructions.index_max_bytes` (8192 افتراضيًا، ولا يقل أبدًا عن 1024). وحين لا تتسع الصفحات، تُحذف الأوصاف أولًا، ثم تُقصّ القائمة بسطر أخير يوجّه الوكيل إلى `list_pages` لبقية الصفحات.
- يعرض `--print` التعليمات والفهرس من دون كتابتهما، ويعرض `--dry-run` الملفات التي ستتغيّر.
- يحدّث `larecipe:agents` الفهرس أيضًا كلما أعاد توليد الكتلة.
- لاستبعاد الفهرس، اضبط `instructions.index` على `false`، أو مرّر `--no-index` إلى `larecipe:install` أو `larecipe:agents`.

<Artisan command="larecipe:agents-md" />

</Accordion>

## Claude Code و CLAUDE.md {#claude-code-and-claudemd}

يقرأ Claude Code الملف `CLAUDE.md`. فإن كان في مشروعك ملف منه لا يستورد `AGENTS.md` بعد، يسألك LaRecipe قبل أن يضيف سطر الاستيراد. ولا يضيف السطر أبدًا من دون موافقتك، ولا ينشئ `CLAUDE.md` أبدًا.

<Accordion title="كيف يُضاف الاستيراد">

يوضع الاستيراد في كتلة خاصة به محدّدة بعلامات في آخر الملف:

```markdown
<!-- larecipe:start -->
@AGENTS.md
<!-- larecipe:end -->
```

أجب عن سؤال المثبّت، أو مرّر `--claude-md` إلى `larecipe:install` أو `larecipe:agents-md` لتوافق مسبقًا، أو أضف السطر بنفسك. وحين لا توجد طرفية يسأل فيها ولا يُمرَّر الخيار، يطبع LaRecipe تحذيرًا ويترك `CLAUDE.md` كما هو.

ويعطي خطّاف SessionStart أيضًا Claude Code قاعدة «قبل الـ push» في بداية كل جلسة. راجع [خطّاف pre-push](pre-push-hook.md).

</Accordion>

## مع Laravel Boost {#with-laravel-boost}

حين تكون الحزمة `laravel/boost` مثبّتة، يدير Boost الإرشادات التي يقرؤها كل وكيل، فلا يكتب LaRecipe كتلة خاصة به، بل يقدّم التعليمات نفسها على شكل إرشاد لـ Boost. شغّل `php artisan boost:install` (أو `boost:update` بعد أي تغيير) واختر `larecipe/larecipe`.

<Accordion title="ما الذي يحويه إرشاد Boost">

يعدّ LaRecipe أن Boost مثبّت حين يوجد المجلد `vendor/laravel/boost` أو حين يطلب `composer.json` الحزمة `laravel/boost`. ويوجد الإرشاد في الحزمة في `resources/boost/guidelines/core.blade.php`:

- يُصيّر النص نفسه الذي كانت ستحويه كتلة `AGENTS.md`، من الإعدادات نفسها، مع فهرس التوثيق. وإن فشل التصيير لأي سبب، يعود إلى نسخة عامة من الخطوات الأربع نفسها بدلًا من أن يترك الوكيل بلا شيء.
- يذكر المهارات الأربع ومتى تُستخدم كلٌّ منها.
- يضيف قواعد خاصة بمسارات معيّنة: للملفات تحت `docs/**`، قواعد ترويسة الصفحة والمراجع الحية والمكوّنات؛ ولـ `database/migrations/docs/**`، أن ترحيلات التوثيق تأتي من أدوات التأليف في MCP ولا تُكتب بيد أبدًا.

ومع تثبيت Boost، لا يكتب `larecipe:agents-md` شيئًا، ويطلب منك أن تشغّل `boost:update`.

</Accordion>

## أعد التوليد بعد أي تغيير {#regenerate-after-a-change}

بعد تحديث الحزمة أو تغيير الإعدادات (مجموعة جديدة، أو فرع آخر، أو وضع مراجعة جديد)، أعد توليد كل ما يقدّمه LaRecipe لوكلائك:

```bash
php artisan larecipe:agents
```

يعيد كتابة كتلة `AGENTS.md`، ويزامن [المهارات](skills.md) مع مراجع مولَّدة من تطبيقك، ويجمّع أنماط الـ push التي يقرؤها [خطّاف pre-push](pre-push-hook.md). ولا يمسّ أبدًا النص الواقع خارج العلامات. ومع تثبيت Boost، يترك الكتلة والمهارات لـ Boost، ويكتفي بتحديث المراجع المولَّدة في نسخ المهارات التي كتبها Boost.

ويعرض المثبّت تشغيله بعد كل `composer update`، بإضافة `@php artisan larecipe:agents --ansi` إلى سكربت `post-update-cmd` لديك (والخيار `--composer-script` يوافق على ذلك مسبقًا).

<Accordion title="كل الخيارات">

<Artisan command="larecipe:agents" />

</Accordion>

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="من أين يأتي كل جزء من الكتلة">

يبني `larecipe:install` و `larecipe:agents` الكتلة من معلومات فعلية في كل مرة:

| في الكتلة | مصدره |
| --- | --- |
| أين يوجد التوثيق | مشغّل كل مجموعة ومجلدها في `config/larecipe.php` (`sets.*.source`). وتأتي المجموعة الافتراضية أولًا. |
| الفرع في الخطوة 1 | `instructions.default_branch` (`LARECIPE_DEFAULT_BRANCH`) إن كان مضبوطًا؛ وإلا فالفرع الذي يشير إليه `origin/HEAD`؛ وإلا فأول الموجود من `main` و `master` و `trunk` و `develop`؛ وإلا فـ `main`. |
| الخطوتان 2 و 4 | المشغّل: ملفات Markdown لمجموعة المستودع، أو ترحيل توثيق عبر أدوات التأليف في MCP لمجموعة قاعدة البيانات. |
| سطر المراجعة | `review`. ولا تقول الكتلة `auto` إلا حين تكون كل المجموعات `auto`. |

ومع عدة مجموعات، يذكر السطر الأول كل واحدة منها ("from 2 doc sets: `docs` in `docs/` and `handbook` in the database")، وتذكر الخطوة 2 المجموعات الموجودة في قاعدة البيانات.

</Accordion>

<Accordion title="الكتلة لمجموعة قاعدة البيانات">

في مجموعة قاعدة البيانات، تطلب الكتلة من الوكيل أن يكتب عبر أدوات MCP، لا عبر الصفوف أو SQL أبدًا، وأن يحفظ ترحيل التوثيق في commit:

```markdown
This app's docs live in the database (doc set `handbook`) and are served by LaRecipe. Docs ship in the same push as the code they describe, as docs migrations in `database/migrations/docs/`.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill, through the LaRecipe MCP tools; they write a docs migration in `database/migrations/docs/`; commit it. Never edit docs rows or write SQL by hand. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs migration with the code and push them together.
```

راجع [ترحيلات التوثيق](../sources/docs-migrations.md).

</Accordion>

</AccordionGroup>

## التالي {#next}

- [خطّاف pre-push](pre-push-hook.md): ما يحدث حين ينسى الوكيل هذه التعليمات.
- [المهارات](skills.md): سير العمل الذي تشير إليه الكتلة.
- [أوضاع المراجعة](review-modes.md): `required` و `auto`.
