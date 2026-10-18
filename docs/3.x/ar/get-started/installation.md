---
title: التثبيت
description: أضف LaRecipe إلى تطبيق Laravel بأمرين، واحصل على موقع توثيق يعمل وعلى إعداد للوكلاء يُبقيه محدّثًا.
sources:
  - vendor/larecipe/larecipe/composer.json
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/**
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/IntroductionPage.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/ProjectFacts.php
  - vendor/larecipe/larecipe/stubs/docs-migration.stub
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallSkillsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/LinkClaudeMdAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteInstructionsBlockAction.php
translated_from: sha256:0c8e55f3a3f2007e4eb064691a9330ddfa759dd64099bf7fa053bdeaceb7226a
---

# التثبيت

تحتاج إلى PHP بالإصدار 8.3 أو أحدث، و Laravel بالإصدار 12.62 أو أحدث (أو Laravel 13)، وإلى الأداة `git` على أجهزة المطوّرين وفي CI. أما خوادم الإنتاج فلا تحتاج إلى `git`.

<Steps>

<Step title="أضف الحزمة">

```bash
composer require larecipe/larecipe
```

ما دام LaRecipe 3 في مرحلة ما قبل الإصدار، اطلبه من Composer بالاسم: `composer require larecipe/larecipe:^3.0@alpha`.

</Step>

<Step title="شغّل المثبّت">

```bash
php artisan larecipe:install
```

يطرح المثبّت بضعة أسئلة. القيم الافتراضية تناسب معظم التطبيقات، فيمكنك أن تضغط Enter على كل منها.

</Step>

<Step title="افتح التوثيق">

زر المسار `/docs`. الصفحة الأولى مقدمة قصيرة كُتبت من تطبيقك: اسمه، وإصدار Laravel فيه، وحزمه الرسمية، ومطلع ملف README.

</Step>

</Steps>

هذا موقع يعمل. وقد أعدّ المثبّت أيضًا وكلاء البرمجة لديك، ليبقى التوثيق محدّثًا منذ أول push. أضف إلى git كل ما كتبه، ما عدا الملف الموجود في `bootstrap/cache`.

الخطوة التالية: دع وكيل البرمجة يكتب صفحات أولية حقيقية من الكود في [توثيقك الأول](your-first-docs.md).

## ما الذي يسأل عنه المثبّت

لكل سؤال خيار في سطر الأوامر، فيمكنك أن تجيب مسبقًا أو أن تشغّل المثبّت داخل سكربت.

| السؤال | الخيار | القيمة الافتراضية |
| --- | --- | --- |
| أين يُحفظ التوثيق؟ | `--driver=repository` أو `--driver=database` | `repository` |
| مجلد التوثيق في مجموعة المستودع | `--path=docs` | `docs` |
| وكلاء البرمجة الذين يُعَدّون | `--agents=claude,codex,cursor,gemini,copilot` أو `--agents=none` | الوكلاء الذين يكتشفهم |
| إيقاف push الوكيل مرة واحدة حين يترك صفحات قديمة | `--no-hooks` للتخطّي | نعم |
| فحص عمليات push التي يجريها الأشخاص من دون وكيل | `--git-hook=warn` أو `--git-hook=strict` | لا |
| إضافة فحص التوثيق الاختياري في CI من دون ذكاء اصطناعي | `--ci=fail` أو `--ci=comment` | لا |
| وضع المراجعة لتغييرات التوثيق | `--review=required` أو `--review=auto` | `required` |
| تحديث إعداد الوكلاء بعد كل `composer update` | `--composer-script` | يسأل |
| إضافة `@AGENTS.md` إلى ملف `CLAUDE.md` موجود | `--claude-md` | يسأل |
| إنشاء توثيق أولي الآن | `--onboard=product,api,package,internal` و `--onboard-agent=claude` | يسأل حين يكون وكيلٌ مثبّتًا |

يعرض الخيار `--dry-run` كل ملف سيغيّره المثبّت من دون أن يكتب شيئًا، ويستبعد `--no-index` فهرس التوثيق من `AGENTS.md`. ويمكنك تشغيل المثبّت مرة أخرى في أي وقت: فهو يدمج إعداداته في ملفاتك الموجودة ولا يستبدلها أبدًا.

## ما الذي يكتبه

مجلد التوثيق مع ملف التعريف وصفحة المقدمة، وكل ما يقرؤه وكلاؤك: كتلة LaRecipe في `AGENTS.md`، وأربع مهارات (skills)، وخادم MCP المحلي في إعدادات كل وكيل، وخطّاف pre-push لكل من Claude Code و Codex. أما خطّاف git وسير عمل CI فلا يُكتبان إلا إذا طلبتهما.

<Accordion title="كل ملف يكتبه المثبّت">

| الملف | ما هو |
| --- | --- |
| `docs/larecipe.json` | [ملف التعريف](../reference/manifest.md) لمجموعة التوثيق: الاسم من `APP_NAME` ومؤشّر `$schema`. |
| `docs/index.md` | صفحة المقدمة، وفيها `sources:` يشير إلى `README.md` و `composer.json`، فتظهر قديمة حين يتغيّران. يُتخطّى إن وُجد `docs/index.md` أو `docs/README.md` أو `docs/introduction.md`. |
| `database/migrations/docs/{timestamp}_docs_introduction.php` | مع `--driver=database` فقط، بدلًا من الملفين السابقين: ترحيل توثيق يمنح المجموعة اسمها وصفحة المقدمة نفسها. |
| `config/larecipe.php` | يُنشر فقط حين تختار غير الإعدادات الافتراضية (مشغّل قاعدة البيانات، أو مجلدًا آخر، أو المراجعة `auto`). |
| `AGENTS.md` | كتلة LaRecipe محدّدة بعلامات: ما يجب فعله بالتوثيق قبل كل push، وفهرس مختصر لصفحاتك. راجع [تعليمات الوكلاء](../keeping-current/agent-instructions.md). |
| `CLAUDE.md` | السطر `@AGENTS.md`، ولا يُضاف إلا بموافقتك، ليقرأ Claude Code التعليمات نفسها. |
| `.claude/skills/` و `.agents/skills/` | مهارات LaRecipe الأربع. راجع [المهارات](../keeping-current/skills.md). |
| `.mcp.json` و `.codex/config.toml` و `.cursor/mcp.json` و `.gemini/settings.json` و `.vscode/mcp.json` | خادم MCP المحلي `larecipe` (`php artisan larecipe:mcp`)، في الملف الذي يقرؤه كل وكيل مكتشَف. |
| `.claude/settings.json` و `.codex/hooks.json` | خطّافا pre-push و SessionStart لـ Claude Code و Codex. راجع [خطّاف pre-push](../keeping-current/pre-push-hook.md). |
| `.git/hooks/pre-push` | مع `--git-hook` فقط. ويحترم الإعداد `core.hooksPath`. |
| `.github/workflows/larecipe-docs-check.yml` | مع `--ci` فقط. راجع [فحص CI الاختياري](../keeping-current/ci-check.md). |
| `composer.json` | الأمر `@php artisan larecipe:agents --ansi` في `post-update-cmd`، بموافقتك فقط. |
| `bootstrap/cache/larecipe/hooks.php` | أنماط push المُجمَّعة التي يقرؤها الخطّاف، كي لا تُشغِّل أوامر الطرفية العادية تطبيق Laravel. لا يُضاف إلى git. |

يدمج المثبّت إعداداته في ملفاتك الموجودة. تبقى خوادم MCP والخطّافات الأخرى في مكانها، ولا يمسّ إدخال `larecipe` الموجود مسبقًا، ويتخطّى أي ملف لا يستطيع قراءته مع ملاحظة بذلك.

</Accordion>

## تفاصيل

<AccordionGroup>

<Accordion title="التشغيل من دون أسئلة">

في سكربت، أو في CI، أو في حاوية تطوير، مرّر `--no-interaction` مع الإجابات التي تريدها:

```bash
php artisan larecipe:install --no-interaction \
    --driver=repository \
    --agents=claude,codex \
    --review=required \
    --claude-md \
    --composer-script
```

حين لا توجد طرفية يسأل فيها، يستخدم المثبّت القيم الافتراضية في الجدول أعلاه: مشغّل المستودع، والوكلاء الذين يكتشفهم، وخطّافات الوكلاء، ومن دون خطّاف git ولا فحص CI، ومع المراجعة `required`، ومن دون أي تغيير على `composer.json` أو `CLAUDE.md` ما لم تمرّر الخيار. ولا يجري الإعداد الأولي (onboarding) أبدًا من دون `--onboard`.

</Accordion>

<Accordion title="كيف يكتشف وكلاءك">

يكتشف المثبّت الوكيل بطريقتين: أداة سطر الأوامر الخاصة به في `PATH` (`claude` و `codex` و `cursor-agent` و `gemini` و `copilot`)، أو ملفاته داخل المشروع (`.claude` و `CLAUDE.md` و `.codex` و `.cursor` و `.gemini` و `GEMINI.md` و `.github/copilot-instructions.md`). ولا يكتب الإعدادات إلا للوكلاء الذين تستخدمهم.

</Accordion>

<Accordion title="أين يمكن أن يكون مجلد التوثيق">

يجب أن يكون مجلد التوثيق داخل تطبيقك. ويرفض المثبّت قيمة `--path` إن كانت المجلد الجذر للتطبيق نفسه، أو مجلدًا خارجه.

</Accordion>

<Accordion title="مع Laravel Boost">

حين تكون الحزمة `laravel/boost` مثبّتة، يقدّم Boost تعليمات LaRecipe ومهاراته، فلا يكتب LaRecipe كتلة في `AGENTS.md` ولا نسخًا خاصة به من المهارات. شغّل `php artisan boost:install` (أو `boost:update`) واختر `larecipe/larecipe`. ويبقى `larecipe:install` هو من يكتب إعداد MCP والخطّافات. راجع [تعليمات الوكلاء](../keeping-current/agent-instructions.md#with-laravel-boost).

</Accordion>

<Accordion title="مع مشغّل قاعدة البيانات">

يحفظ الخيار `--driver=database` التوثيق في قاعدة بياناتك، مع واجهة إدارة وترحيلات توثيق (docs migrations) للتغييرات التي تجري في طلبات الدمج. لا يكتب المثبّت مجلد `docs/` في هذه الحالة، بل يكتب [ترحيل توثيق](../sources/docs-migrations.md) أوليًا هو `database/migrations/docs/{timestamp}_docs_introduction.php`، فيه اسم المجموعة وصفحة المقدمة نفسها. ولا يكتب ترحيلًا ثانيًا إن شغّلته مرة أخرى. بعد ذلك:

1. شغّل `php artisan migrate`. ينشئ جداول LaRecipe، وهي تُحمَّل من الحزمة (وانشرها بالأمر `php artisan vendor:publish --tag=larecipe-migrations` إن أردت تخصيصها)، ثم يطبّق الترحيل الأولي، فيفتح `/docs` على صفحة المقدمة مباشرة.
2. استخدم مخزن ذاكرة مؤقتة دائمًا. تعمل مجموعة قاعدة البيانات دائمًا خلف ذاكرة مؤقتة، ويحذّرك المثبّت إن كان المخزن `array` أو `null`. اضبط `LARECIPE_CACHE_STORE` على `redis` أو `database` أو `file`.

اقرأ صفحة [مصدر قاعدة البيانات](../sources/database.md) قبل أن تختاره. تبدأ معظم الفرق بالمستودع.

</Accordion>

<Accordion title="Codex و Ask AI">

خطوتان يعرضهما المثبّت حين تنطبقان:

- **Codex**: اجعل المشروع موثوقًا في `~/.codex/config.toml` ووافق على خطّافات LaRecipe عبر `/hooks`، ليحمّلها Codex.
- **Ask AI** يحتاج إلى الحزمة `laravel/ai` ومفتاح مزوّد خاص بك: `composer require laravel/ai`. وكل ما عدا ذلك يعمل من دونه. راجع [Ask AI](../agents/ask-ai.md).

</Accordion>

<Accordion title="كل خيارات larecipe:install">

مقروءة مباشرة من سجلّ الأوامر في هذا التطبيق:

<Artisan command="larecipe:install" />

</Accordion>

</AccordionGroup>

## التالي

- [توثيقك الأول](your-first-docs.md): دع وكيل البرمجة يكتب صفحات أولية من تطبيقك.
- [أساسيات الإعداد](configuration.md): الإعدادات القليلة التي تغيّرها معظم التطبيقات.
- [بنية المشروع](project-structure.md): ما الذي يوضع وأين في `docs/`.
