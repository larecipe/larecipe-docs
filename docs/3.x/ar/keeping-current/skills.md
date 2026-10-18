---
title: المهارات وإضافة Claude Code
description: مهارات LaRecipe الأربع التي تعلّم أي وكيل برمجة كيف يكتب توثيقك ويحدّثه ويدقّقه ويرقّيه، وأين يضعها المثبّت، وإضافة Claude Code التي تقدّمها مع الخطّافات.
sources:
  - vendor/larecipe/larecipe/resources/boost/skills/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/SkillReferences.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallSkillsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/ComponentRegistry.php
  - vendor/larecipe/larecipe/resources/schemas/**
  - vendor/larecipe/larecipe/.claude-plugin/marketplace.json
  - vendor/larecipe/larecipe/.claude-plugin/plugin.json
  - vendor/larecipe/larecipe/claude-plugin/**
translated_from: sha256:62ccc433160154ec658556587ac385924cfc86a7e75063ef41b53b8685c9ae70
---

# المهارات وإضافة Claude Code

المهارة (skill) مجلد من التعليمات يحمّله الوكيل حين تتطلبه مهمة ما. ويقدّم LaRecipe أربع مهارات، واحدة لكل عمل من أعمال التوثيق. وهي تعمل في Claude Code و Codex و Cursor و Copilot و Gemini CLI وأي وكيل آخر يقرأ صيغة Agent Skills.

| المهارة | عملها | يلجأ إليها الوكلاء حين |
| --- | --- | --- |
| `larecipe-write-docs` | تكتب صفحات جديدة من كود التطبيق، وتجري الإعداد الأولي (onboarding) الذي يكتب [توثيقك الأول](../get-started/your-first-docs.md). | يُطلب منهم توثيق ميزة أو API أو أمر أو جزء من الكود. |
| `larecipe-update-docs` | تحدّث ما جعله تغييرٌ ما قديمًا، وتحافظ على كل جملة لا علاقة لها به، وتُبقي الترجمات محدّثة. | قبل كل push، أو حين يوقف [خطّاف pre-push](pre-push-hook.md) عملية push. |
| `larecipe-audit-docs` | تراجع مجموعة توثيق كاملة وتخرج بقائمة إصلاحات مرتّبة حسب الأولوية. | يُطلب منهم تدقيق التوثيق، أو قبل إصدار جديد. |
| `larecipe-upgrade-v2` | تُكمل الترقية من LaRecipe 2 بعد `php artisan larecipe:upgrade`. | يوجد الملف `larecipe-upgrade-report.md`، أو ما زال التوثيق يحمل بقايا من الإصدار 2. |

ينسخها `php artisan larecipe:install` إلى `.claude/skills/`، حيث يبحث Claude Code، وإلى `.agents/skills/`، المجلد المشترك الذي يقرؤه الوكلاء الآخرون. أضف المجلدين كليهما إلى git، ليجدها وكيل كل زميل في الفريق. وبعد تحديث الحزمة، أو بعد أن تضيف مكوّنًا، زامنها من جديد:

```bash
php artisan larecipe:agents
```

يحدّث الأمر المجلدات التي تحوي مهارات LaRecipe أصلًا، أو المجلدين الافتراضيين كليهما حين لا يحويها أيٌّ منهما. ويمكن للمثبّت أن يضيفه إلى `post-update-cmd` في Composer، فلا تتأخر المهارات أبدًا؛ راجع [تعليمات الوكلاء](agent-instructions.md#regenerate-after-a-change).

## ما تعلّمه كل مهارة {#what-every-skill-teaches}

تسري القواعد القليلة نفسها في المهارات الأربع: شغّل `larecipe:affected` قبل الـ push وحدّث ما يسرده، وراجع التوثيق الموجود قبل أن تكتب، واضبط `sources:` في كل صفحة، واكتب للناس أولًا، ولا تكتب قيم الإعدادات أو البيئة في التوثيق أبدًا، وشغّل `larecipe:validate`، واتبع وضع المراجعة المذكور في `AGENTS.md`. وفي مجموعة قاعدة البيانات تكتب عبر أدوات التأليف في MCP الخاصة بـ LaRecipe، التي تسجّل التغيير في ترحيل التوثيق الخاص بالفرع، ولا تعدّل الصفوف أو تكتب SQL أبدًا.

تشير [كتلة `AGENTS.md`](agent-instructions.md) وخطّاف pre-push كلاهما بالوكلاء إلى `larecipe-update-docs`، فتؤدي المهارة عملها في اللحظة التي يهمّ فيها.

## إضافة Claude Code {#the-claude-code-plugin}

مستودع حزمة LaRecipe هو أيضًا سوق إضافات (marketplace) لـ Claude Code. وتجمع الإضافة المهارات الأربع، وخطّافي pre-push و SessionStart، والأمر `/larecipe:onboard`، وخادم MCP المحلي `larecipe`. ثبّتها من داخل Claude Code:

```text
/plugin marketplace add saleem-hadad/larecipe
/plugin install larecipe@larecipe
```

تبقى الملفات التي يكتبها `larecipe:install` هي الإعداد الموصى به: اقرأ تحفّظات الإضافة أدناه قبل أن تختارها.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ما تفعله كل مهارة، خطوة بخطوة">

- **`larecipe-write-docs`** تقرأ الكود أولًا، وتبحث في التوثيق الموجود، وتضبط `sources:`، وتفضّل المراجع الحية على القيم الملصوقة، وتضيف الصفحة إلى التنقل وتشغّل `larecipe:validate`. وتحوي أيضًا سير عمل الإعداد الأولي وإعداداته المسبقة لكل جمهور.
- **`larecipe-update-docs`** تشغّل `larecipe:affected`، ولا تغيّر إلا ما جعله تغيير الكود خاطئًا، وتحافظ على كل جملة لا علاقة لها به، وتتحقق، وتلخّص تغيير التوثيق في سطر واحد. وتحمل أيضًا سير عمل الترجمة.
- **`larecipe-audit-docs`** تبحث عن الصفحات المنحرفة، والصفحات التي لا تحوي `sources:`، والأوصاف الضعيفة، والمسارات والأوامر والنماذج والأحداث غير الموثّقة، والأمثلة المعطوبة. وترتّب ما تجده إلى P1 (خاطئ أو غير آمن) و P2 (ناقص) و P3 (ضعيف)، ولا تصلح إلا ما تطلبه.
- **`larecipe-upgrade-v2`** تعالج مكوّنات Vue المخصّصة، و CSS المظهر، و Blade داخل Markdown، وكل ما يعدّه تقرير الترقية عملًا يدويًا. راجع [الترقية من الإصدار 2](../upgrade.md).

</Accordion>

<Accordion title="ترويسة قابلة للنقل">

لا يستخدم كل ملف `SKILL.md` إلا مفاتيح Agent Skills القابلة للنقل: `name` و `description`، وعلى الأكثر `license` و `compatibility` و `metadata` و `allowed-tools`. لا مفاتيح خاصة بوكيل بعينه، فيقرؤها كل وكيل بالطريقة نفسها. وهذه ترويسة `larecipe-write-docs`:

```yaml
---
name: larecipe-write-docs
description: Write new LaRecipe docs pages from this app's code. Use when asked to document a feature, an API, an artisan command or part of the codebase, to add a page or section to the docs, or to run LaRecipe onboarding (starter docs for product users, API consumers, package users or an internal team).
license: MIT
compatibility: A Laravel app with larecipe/larecipe installed, so php artisan larecipe:* works. The larecipe MCP server is recommended and required for database doc sets.
metadata:
  author: larecipe
  version: "3.0"
---
```

يبقى كل `SKILL.md` أقصر من 500 سطر. والتفاصيل في مجلد `references/` لا يفتحه الوكيل إلا حين يحتاج إليه.

</Accordion>

<Accordion title="مراجع مولَّدة من تطبيقك">

المهارة التي تصف مكوّنًا أو مفتاحًا غير موجود تعلّم الوكيل أن يكتب صفحات معطوبة. لذا فالمراجع التي تصف لغة LaRecipe يولّدها `larecipe:agents` من الكود، ولا تُكتب بيد:

| المرجع | مولَّد من | في المهارات |
| --- | --- | --- |
| `components.md` | سجلّ المكوّنات: كل مكوّن مدمج، والمراجع الحية، والمكوّنات التي يسجّلها تطبيقك بـ `LaRecipe::component()`، كلٌّ منها مع خصائصه (props) وقيمها المسموحة وقيمها الافتراضية | الأربع كلها |
| `frontmatter.md` | مخطط JSON Schema لترويسة الصفحة | الأربع كلها |
| `manifest.md` | مخطط JSON Schema لملف `larecipe.json` | الكتابة والتدقيق والترقية |
| `navigation.md` | مخطط JSON Schema لملف `navigation.json` | الكتابة والتدقيق والترقية |

تظهر مكوّناتك الخاصة في الفهرس موسومة بعبارة "(defined by this app)"، فيعرف الوكيل الذي يكتب توثيقك أنها موجودة وما خصائصها. ويبدأ كل ملف مولَّد بسطر يقول إنه وُلّد بالأمر `php artisan larecipe:agents` وإنه لا ينبغي تعديله.

أما المراجع الأخرى فمكتوبة بيد وتُقدَّم كما هي: `writing-style.md` و `live-references.md` و `database-sets.md` (متطابقة في كل المهارات التي تحملها)، والإعدادات المسبقة للإعداد الأولي في `larecipe-write-docs`، وسير عمل الترجمة في `larecipe-update-docs`، وجدول المقابلة مع الإصدار 2 في `larecipe-upgrade-v2`.

</Accordion>

<Accordion title="مع Laravel Boost">

حين تكون الحزمة `laravel/boost` مثبّتة، يوزّع Boost المهارات على التطبيق كله، فلا يكتب LaRecipe نسخًا خاصة به. وتأتي المهارات من المجلد `resources/boost/skills/` في الحزمة: شغّل `php artisan boost:install` (أو `boost:update`) واختر `larecipe/larecipe`، فينسخها Boost إلى مجلد كل وكيل.

ويبقى `larecipe:agents` مهمًا مع Boost. فهو يجد النسخ التي كتبها Boost (في `.claude/skills` و `.agents/skills` و `.github/skills` و `.cursor/skills` والمجلدات الأخرى التي يستخدمها Boost) ويعيد توليد مراجعها، فتظهر مكوّناتك الخاصة فيها أيضًا. راجع [تعليمات الوكلاء](agent-instructions.md#with-laravel-boost).

</Accordion>

<Accordion title="الإضافة: ما تقدّمه، وتحفّظاتها">

تجمع الإضافة:

- المهارات الأربع، من `resources/boost/skills/`؛
- خطّافي pre-push و SessionStart، من `claude-plugin/hooks.json`؛
- الأمر `/larecipe:onboard`، من `claude-plugin/commands/onboard.md`، الذي يشغّل سير عمل الإعداد الأولي في `larecipe-write-docs` للجماهير التي تسمّيها (`product` و `api` و `package` و `internal`)؛
- خادم MCP المحلي `larecipe`.

ليعرضها Claude Code لدى كل زميل في الفريق، سجّل سوق الإضافات وفعّل الإضافة في ملف `.claude/settings.json` الخاص بالمشروع:

```json
{
  "extraKnownMarketplaces": {
    "larecipe": {
      "source": { "source": "github", "repo": "saleem-hadad/larecipe" }
    }
  },
  "enabledPlugins": {
    "larecipe@larecipe": true
  }
}
```

تشغّل خطّافات الإضافة نسخة الإضافة الخاصة من `larecipe-hook`، التي تشغّل بدورها `php artisan larecipe:hook` في تطبيقك. والإضافة مثبّتة لك أنت، لا لمشروع واحد، لذا لا يفعل الخطّاف شيئًا في مشروع لا يحوي LaRecipe: يبحث عن أقرب تطبيق Laravel، ويتوقف عند جذر مستودع git، ولا يتصرف إلا حين يكون `larecipe/larecipe` مثبّتًا أو مطلوبًا في ذلك التطبيق. وفي غير ذلك يترك كل أمر يمرّ.

ثلاثة أمور يجب أن تعرفها قبل أن تختار الإضافة بدلًا من ملفات المثبّت:

- **ملفات المثبّت أولًا.** تشغّل الإضافة خادم MCP بالأمر `php ${CLAUDE_PROJECT_DIR}/artisan larecipe:mcp`، وهو طريق لم يُتحقق منه بالكامل بعد. والملف `.mcp.json` الذي يكتبه `larecipe:install` هو الطريقة الموصى بها لربط الخادم.
- **مراجعها تأتي من الحزمة، لا من تطبيقك.** مهارات الإضافة لا تذكر مكوّناتك الخاصة أبدًا. فثبّت المهارات في المستودع أيضًا حين تكون لديك مكوّنات مخصّصة.
- **مجموعة واحدة من الخطّافات تكفي.** حين تكون خطّافات الإضافة مفعّلة، ثبّت بالأمر `larecipe:install --no-hooks` كي لا يعمل الفحص مرتين مع كل push.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [تعليمات الوكلاء](agent-instructions.md): الكتلة التي تخبر الوكلاء متى يستخدمون المهارات.
- [أوضاع المراجعة](review-modes.md): هل يسأل الوكيل قبل الـ commit.
- [مكوّنات React المخصّصة](../customize/custom-components.md): مكوّنات تظهر في فهرس المهارات.
