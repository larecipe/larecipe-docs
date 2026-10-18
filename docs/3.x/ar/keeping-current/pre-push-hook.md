---
title: خطّاف pre-push
description: حين يترك الـ push الذي يجريه وكيلٌ صفحاتٍ قديمة، يوقفه الخطّاف مرة واحدة ويخبر الوكيل بالصفحات التي يجب تحديثها، مع خطّاف git اختياري لمن يرفع من دون وكيل.
sources:
  - vendor/larecipe/larecipe/bin/larecipe-hook
  - vendor/larecipe/larecipe/src/Domains/Sync/Support/PushCommandDetector.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/CheckPrePushAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildSessionContextAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/CompileHookPatternsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallGitHookAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/HookCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/GitHooksCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/LoopGuard.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/HookMessages.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/PushRange.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/HookPayload.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/InstallAgentHooksAction.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - .claude/settings.json
translated_from: sha256:af08b497d9457437ce35ee08e97e2728f648632e9cd15c613ac25077a91b058f
---

# خطّاف pre-push

حين يشغّل وكيل البرمجة `git push` أو `gh pr create` ويترك التغيير صفحاتٍ من التوثيق قديمة، يوقف خطّاف pre-push عملية الـ push مرة واحدة ويخبر الوكيل بالصفحات التي يجب تحديثها. فيحدّثها الوكيل ثم يرفع من جديد. وإن لم يكن التوثيق بحاجة إلى تغيير، يخبرك الوكيل بالسبب ويرفع من جديد: المحاولة الثانية تمرّ دائمًا، فلا يعلق أبدًا.

يضيف المثبّت هذا الخطّاف لكل من Claude Code و Codex. وهو حتمي ويعمل من دون اتصال: يقرأ git والتوثيق، ولا يستدعي نموذجًا لغويًا أبدًا. وأي أمر غير متوقع، مثل غياب مستودع git أو تعطّل تشغيل التطبيق، يجعله يترك الأمر يمرّ.

## ما الذي يُقال للوكيل {#what-the-agent-is-told}

حين يوقف الخطّاف عملية push، يتلقى الوكيل سببًا كهذا:

```text
LaRecipe: this push changes code that 2 docs pages describe, and they were not updated:
  - docs/billing/webhooks.md   (sources: app/Webhooks/**)
  - docs/installation.md       (sources: composer.json)
Update them with the larecipe-update-docs skill, run php artisan larecipe:validate,
show the user the docs diff and wait for approval, then commit and push again.
If no docs change is needed, tell the user why and push again; the next push is allowed.
```

والخطوة التالية تتبع [وضع المراجعة](review-modes.md):

- **`required`** (الافتراضي): يعرض عليك الوكيل فروقات التوثيق وينتظر موافقتك قبل الـ commit، كما في المثال أعلاه.
- **`auto`**: يصبح السطر "then commit and push again."، فيحدّث الوكيل الصفحات ويحفظها في commit ويرفعها من دون أن يسأل.

وحين يضيف الـ push صفحات أو يحذفها أو يغيّر عناوينها، تطلب الرسالة من الوكيل أيضًا أن يشغّل `php artisan larecipe:agents-md`، ليبقى [فهرس التوثيق في `AGENTS.md`](agent-instructions.md#the-docs-index) محدّثًا.

## أوقفه {#turn-it-off}

- **لجلسة واحدة:** اضبط `LARECIPE_HOOKS=off` في البيئة التي يعمل فيها الوكيل، مثل `LARECIPE_HOOKS=off claude`.
- **للمشروع كله:** اضبط `hooks.enabled` على `false` في `config/larecipe.php` (أو `LARECIPE_HOOKS=off` في `.env`)، ثم شغّل `php artisan larecipe:agents` لتتوافق الأنماط المجمّعة مع ذلك.
- **لإزالته:** احذف إدخالات LaRecipe من `.claude/settings.json` و `.codex/hooks.json`. والأمر `larecipe:install --no-hooks` لا يضيفها أصلًا.

## خطّاف git، للأشخاص {#the-git-hook-for-people}

يمكن للمطوّرين الذين يرفعون من دون وكيل أن يحصلوا على الفحص نفسه على شكل خطّاف `pre-push` في git. وافتراضيًا يكتفي بالتحذير: يطبع الصفحات التي يتركها الـ push قديمة، ويترك الـ push يمرّ.

```bash
php artisan larecipe:hooks:git --install
```

ومع `--strict` يمنع الـ push بدلًا من ذلك، في كل مرة، حتى يُحدَّث التوثيق. ويتخطّاه `git push --no-verify` مرة واحدة حين لا يحتاج التوثيق إلى تغيير. ويمكن للمثبّت أن يضيفه أيضًا: `larecipe:install --git-hook=warn` أو `--git-hook=strict`.

<Accordion title="كيف يعمل خطّاف git، وخياراته">

- يفحص الـ commits التي تُرفع. فالعمل الذي لم يُحفظ في commit لا ينتقل مع الـ push، فلا يُحتسب هنا.
- لا توجد قاعدة «المحاولة الثانية تمرّ» للأشخاص في الوضع الصارم، لأن `--no-verify` هو المخرج المألوف.
- يحترم `core.hooksPath`. وحين يوجد خطّاف `pre-push` مكتوب بلغة الطرفية (shell) أصلًا، يضيف LaRecipe إليه كتلته المحدّدة بعلامات بدلًا من أن يستبدله. أما الخطّاف الذي ليس سكربت shell فلا يُمسّ أبدًا، ويطبع الأمر السطر الذي تضيفه بنفسك.
- يتخطّاه `LARECIPE_HOOKS=off git push` في عملية push واحدة. ويزيل `--uninstall` كتلة LaRecipe، ويحذف الخطّاف إن لم يبقَ فيه شيء آخر. ويعرض `--dry-run` ما سيتغيّر.
- لا يستدعي ذكاءً اصطناعيًا أبدًا. فخطّاف git يحتاج إلى مفاتيح وشبكة ووقت نموذج سيكون بطيئًا ولا يمكن التنبؤ به.

يبدو الخطّاف الجديد هكذا:

```bash
#!/bin/sh

# larecipe:start
# LaRecipe docs check. Deterministic: it reads git and the docs, and never calls an AI.
# Lists docs pages this push leaves stale, then lets the push through.
# Turn it off for one push with LARECIPE_HOOKS=off, or remove it: php artisan larecipe:hooks:git --uninstall
if [ "$LARECIPE_HOOKS" != "off" ]; then
  (cd "$(git rev-parse --show-toplevel)" && php artisan larecipe:hook git-pre-push) || true
fi
# larecipe:end
```

<Artisan command="larecipe:hooks:git" />

</Accordion>

## وكلاء بلا خطّافات {#agents-without-hooks}

لا يملك Cursor و Copilot و Gemini CLI اليوم خطّافًا يعمل قبل الأدوات يستطيع LaRecipe استخدامه. فهي تعتمد على [تعليمات `AGENTS.md`](agent-instructions.md)، التي تقرؤها في بداية كل جلسة. ولعمليات الـ push التي يجريها هؤلاء الوكلاء، أضف خطّاف git أعلاه و[فحص CI](ci-check.md).

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="كيف يُفحص الـ push">

1. يستدعي Claude Code و Codex الأمر `vendor/bin/larecipe-hook pre-push` قبل كل أمر Bash، مع الأمر داخل حمولة الخطّاف (payload). والسكربت مكتوب بـ PHP عادية بلا أي اعتماديات. وما لم يكن الأمر يرفع كودًا أو يفتح pull request، ينتهي خلال أجزاء من الثانية، من دون تشغيل Laravel.
2. في حالة الـ push، يشغّل السكربت `php artisan larecipe:hook pre-push`، الذي يحدّد التغيير الذي سيحويه الـ pull request: على فرع ميزة، كل ما جرى منذ تفرّع عن الفرع الافتراضي (`origin/main`، أو `main` حين لا يوجد فرع بعيد)؛ وعلى الفرع الافتراضي نفسه، الـ commits التي لم تُرفع بعد إلى الفرع الذي يتتبّعه. والعمل الذي لم يُحفظ في commit يُحتسب أيضًا.
3. يمرّ الـ push حين لا يتأثر شيء، أو حين تكون كل صفحة متأثرة قد حُدّثت في النطاق نفسه (ملف Markdown الخاص بها، أو ترحيل توثيق يذكرها في مجموعة قاعدة البيانات)، أو حين يكون الخطّاف متوقفًا، أو حين يكون الـ push نفسه قد أُوقف مرة من قبل. وإلا يتلقى الوكيل قرار رفض من نوع PreToolUse (`"permissionDecision": "deny"`) مع السبب المعروض أعلاه.

وحين تنتمي بعض الصفحات القديمة إلى مجموعة في وضع `required`، تطلب الرسالة الموافقة. وفي مجموعة قاعدة البيانات، يضيف سطر التحديث أن صفحات قاعدة البيانات تُعدَّل عبر أدوات LaRecipe في MCP، التي تكتب ترحيل التوثيق الخاص بالفرع في `database/migrations/docs/`.

وحين لا تكون أي صفحة قديمة لكن فهرس التوثيق متأخر، يتلقى Claude Code سطر `larecipe:agents-md` على شكل ملاحظة ويمرّ الـ push. أما Codex فلا يتلقى شيئًا في هذه الحالة، لأنه يعدّ حقول PreToolUse التي لا يدعمها فشلًا في الخطّاف.

</Accordion>

<Accordion title="Claude Code: ملف .claude/settings.json">

يضيف المثبّت خطّافين إلى `.claude/settings.json`. وهذا ملف هذا الموقع نفسه:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "\"$CLAUDE_PROJECT_DIR\"/vendor/bin/larecipe-hook pre-push",
            "timeout": 60
          }
        ]
      }
    ],
    "SessionStart": [
      {
        "matcher": "startup|resume|compact",
        "hooks": [
          {
            "type": "command",
            "command": "\"$CLAUDE_PROJECT_DIR\"/vendor/bin/larecipe-hook session-start",
            "timeout": 30
          }
        ]
      }
    ]
  }
}
```

- **PreToolUse** يعمل مع كل استدعاء لأداة Bash ويجري فحص ما قبل الـ push.
- **SessionStart** يعمل حين تبدأ جلسة أو تُستأنف أو تُضغط، ويعطي الوكيل خريطة مختصرة للتوثيق.

يدمج المثبّت هذين الخطّافين في إعداداتك الموجودة ولا يستبدلها أبدًا. تبقى الخطّافات الأخرى في مكانها، ويُترك الملف الذي فيه خطّاف LaRecipe أصلًا كما هو، ويُتخطّى الملف الذي ليس JSON صالحًا مع ملاحظة بذلك.

يجد السكربت تطبيقك من `$CLAUDE_PROJECT_DIR`، أو من مجلد العمل في الحمولة، أو من المجلد الحالي، صاعدًا إلى أقرب مجلد فيه ملف `artisan`، فيعمل أيضًا حين يكون تطبيق Laravel في مجلد فرعي داخل المستودع. ويتوقف عند جذر مستودع git، ولا يتصرف إلا حين يكون في ذلك التطبيق الأقرب LaRecipe (`vendor/larecipe/larecipe`، أو `larecipe/larecipe` في `composer.json`)؛ ولا يتجاوزه أبدًا إلى تطبيق أعلى منه. وفي أي مكان آخر يترك كل أمر يمرّ.

</Accordion>

<Accordion title="Codex: ملف .codex/hooks.json">

يقرأ Codex شكل الخطّاف نفسه من `.codex/hooks.json`. ولا يعطي Codex الخطّافات مجلد المشروع، لذا يجد الأمر الذي يكتبه المثبّت التطبيقَ من جذر المستودع ويمرّر `--client=codex`:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "\"$(git rev-parse --show-toplevel)\"/'vendor/bin/larecipe-hook' pre-push --client=codex",
            "timeout": 60
          }
        ]
      }
    ],
    "SessionStart": [
      {
        "matcher": "startup|resume|compact",
        "hooks": [
          {
            "type": "command",
            "command": "\"$(git rev-parse --show-toplevel)\"/'vendor/bin/larecipe-hook' session-start --client=codex",
            "timeout": 30
          }
        ]
      }
    ]
  }
}
```

يُقتبَس المجلد الأعلى للمستودع والمسار داخله كلٌّ على حدة في الطرفية، فلا يمكن لاسم مجلد أن يُنفَّذ أمرًا أبدًا. وحين يكون التطبيق في مجلد فرعي من المستودع، يتضمن المسار ذلك المجلد. ولا يحمّل Codex خطّافات المشروع إلا بعد أن تجعل المشروع موثوقًا في `~/.codex/config.toml` وتوافق على الخطّافات عبر `/hooks`؛ ويذكّرك المثبّت بذلك.

</Accordion>

<Accordion title="إيقاف مرة واحدة، ثم سماح">

يوقف الخطّاف الـ push مرة واحدة على الأكثر للـ commit نفسه ولقائمة الصفحات القديمة نفسها. والمحاولة الثانية تمرّ دائمًا، فالوكيل (أو الشخص) الذي يقرر أن التوثيق لا يحتاج إلى تغيير لا يعلق أبدًا. وتطلب المهارة `larecipe-update-docs` من الوكيل أن يخبرك بالسبب، ثم يرفع من جديد.

- الذاكرة خاصة بكل جلسة وكيل، مفتاحها معرّف الجلسة في حمولة الخطّاف، وتُحفظ في ملف JSON صغير تحت `storage/framework/larecipe/hooks/`. وتُنظَّف الملفات الأقدم من أسبوع.
- الـ commit الجديد الذي ما زال يترك صفحات قديمة يُعدّ عملية push جديدة، ويُوقف مرة واحدة من جديد.
- وتحصل كل جلسة وكيل أخرى على إيقافها الوحيد الخاص بها.

</Accordion>

<Accordion title="أي الأوامر تُعدّ push">

يُعدّ الأمر push حين يشغّل أي جزء منه أحد الأنماط في `hooks.push_commands`:

```php
// config/larecipe.php
'hooks' => [
    'enabled' => env('LARECIPE_HOOKS', 'on') !== 'off',
    'push_commands' => [
        'git push',
        'gh pr create',
        'glab mr create',
        'hub pull-request',
    ],
],
```

الكلمة الأولى في النمط هي البرنامج، ويجب أن تكون الكلمات الأخرى أول وسائطه، مع تجاهل الخيارات. ويُنظر داخل الأوامر المتسلسلة، والأصداف الفرعية (subshells)، و `bash -c '...'`:

| يُعدّ push | لا يُعدّ |
| --- | --- |
| `git push --force-with-lease -u origin feature` | `git status` |
| `git -C app push` | `git log --grep push` |
| `git add . && git commit -m "wip" && git push` | `git commit -m "git push later"` |
| `npm test; git push -u origin feature` | `grep -r 'git push' docs` |
| `FOO=1 git push`، `env GIT_TRACE=1 git push` | `gh pr view 12` |
| `sh -c 'cd app && git push'` | `git pushx` |
| `gh pr create --fill` | `gh pr list` |

يجب أن يقرر السكربت من دون تشغيل Laravel، لذا يقرأ الأنماط من ملف مجمَّع، هو `bootstrap/cache/larecipe/hooks.php`، يكتبه `larecipe:install` و `larecipe:agents` من إعداداتك. وهذه نسخة هذا الموقع:

```php
<?php

// Compiled by php artisan larecipe:agents for vendor/bin/larecipe-hook, so ordinary
// shell commands never boot the framework. Change config/larecipe.php instead.

return array (
  'enabled' => true,
  'push_commands' => 
  array (
    0 => 'git push',
    1 => 'gh pr create',
    2 => 'glab mr create',
    3 => 'hub pull-request',
  ),
);
```

بعد أن تغيّر `hooks.push_commands` أو `hooks.enabled`، شغّل `php artisan larecipe:agents` ليجمّعها من جديد. يوجد الملف في مجلد ذاكرة مؤقتة ولا يُضاف إلى git؛ ومن دونه يستخدم الخطّاف الأنماط الافتراضية الأربعة.

</Accordion>

<Accordion title="SessionStart: ما الذي تعرفه الجلسة الجديدة">

في بداية كل جلسة، يعطي خطّاف SessionStart الوكيلَ بضع مئات من الـ tokens من السياق: أين يوجد التوثيق، وخريطة مختصرة للصفحات، وعدد الصفحات المنحرفة، وقاعدة «قبل الـ push». وفي هذا الموقع يبدو هكذا (الخريطة مختصرة هنا):

```text
LaRecipe docs: set `docs` in `docs/` (repository, version 3.x, 45 pages, review mode required).
Map: index, upgrade, agents/{ask-ai, mcp, surfaces}, customize/{custom-components, insert-points, theme}, get-started/{configuration, installation, project-structure, your-first-docs}, ...
Stale: 7 pages document code that changed after they were last updated (php artisan larecipe:affected lists them).
Before you push or open a pull request: run php artisan larecipe:affected --uncommitted --base=main and update the pages it lists with the larecipe-update-docs skill. Search and read the docs with the larecipe MCP server.
```

لا يظهر سطر "Stale" إلا حين تكون هناك صفحات منحرفة. ويُتخطّى العدّ حين يزيد عدد الصفحات التي تذكر مصادرها على 200، ليبقى بدء الجلسة سريعًا، ويُقصّ السياق كله عند 1,600 حرف.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [أوضاع المراجعة](review-modes.md): ما الذي يغيّره `required` و `auto`.
- [المهارات](skills.md): المهارة `larecipe-update-docs` التي تشير إليها الرسالة.
- [فحص CI](ci-check.md): تعمّق أكثر مع شبكة الأمان الأخيرة، في الـ pull requests.
