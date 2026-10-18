---
title: الإعدادات
description: كل مفتاح في config/larecipe.php مع نوعه وقيمته الافتراضية ومعناه، والمفاتيح التي يمكن لمجموعة توثيق أن تعطيها قيمة خاصة بها، ومتغيرات البيئة التي يقرؤها الملف.
sources:
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DatabaseSourceFactory.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Ask/**
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SyncSettings.php
  - vendor/larecipe/larecipe/src/Domains/References/ReferencesServiceProvider.php
translated_from: sha256:154b0748a54da8953050f466554a8846b5225f019da98c04fe45f3045d1ec5fa
---

# الإعدادات

كل مفتاح في `config/larecipe.php`، بترتيبه في الملف. أما العرض (الاسم والمظهر والإصدارات واللغات) فمكانه [larecipe.json](manifest.md)، وتشرح [أساسيات الإعداد](../get-started/configuration.md) هذا التقسيم والمفاتيح التي تغيّرها معظم التطبيقات.

انشر الملف بالأمر `php artisan vendor:publish --tag=larecipe-config`. يبيّن عمود «لكل مجموعة» هل يمكن لمجموعة توثيق أن تعطي المفتاح قيمة خاصة بها، راجع [تجاوز مفتاح لكل مجموعة](#override-a-key-per-set).

## مجموعة التوثيق الافتراضية

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `default` | string | `docs` (`LARECIPE_DEFAULT_SET`) | لا | المجموعة التي تستخدمها أوامر artisan وخادم MCP المحلي حين لا تُحدَّد مجموعة. |

## مجموعات التوثيق

يربط `sets` مفتاح كل مجموعة بإعداداتها: موقع توثيق لكل منها، بمساره ومصدره وملف `larecipe.json` وواجهات الوكلاء الخاصة به. تأتي الحزمة بمجموعة واحدة هي `docs`.

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `sets.{key}.route.prefix` | string | `docs` (`LARECIPE_PREFIX`) | بادئة الرابط. القيمة `''` تضع المجموعة في جذر النطاق. ومن دون المفتاح يُستخدم مفتاح المجموعة. |
| `sets.{key}.route.domain` | string or null | `null` | تقديم المجموعة على هذا النطاق فقط. |
| `sets.{key}.route.middleware` | list of strings | `['web']` | الـ middleware لصفحات المجموعة. |
| `sets.{key}.source.driver` | string | `repository` | `repository` (ملفات Markdown في هذا المستودع) أو `database`. |
| `sets.{key}.source.path` | string | `base_path('docs')` | مجلد التوثيق لمجموعة المستودع. ومن دون المفتاح، `base_path('{key}')`. |
| `sets.{key}.source.connection` | string or null | غير مضبوط | لمجموعات قاعدة البيانات: اتصال قاعدة البيانات. القيمة `null` تستخدم الاتصال الافتراضي. |
| `sets.{key}.source.cache` | string or null | غير مضبوط | لمجموعات قاعدة البيانات: مخزن الذاكرة المؤقتة أمام قاعدة البيانات. يرجع إلى `cache.store`، ثم إلى المخزن الافتراضي للتطبيق. والمخزنان `array` و `null` مرفوضان خارج الاختبارات. |
| `sets.{key}.source.disk` | string or null | غير مضبوط | لمجموعات قاعدة البيانات: قرص نظام الملفات للأصول (assets). القيمة `null` تستخدم القرص الافتراضي. |
| `sets.{key}.access.mode` | string | `public` | `public`: أي شخص. `auth`: أي مستخدم مسجّل الدخول. `gate`: تُفحص كل صفحة بالبوابة (gate) `viewLarecipe`. والإصدارات والصفحات الخاصة تحتاج إلى `viewLarecipe` في كل الأوضاع. |
| `sets.{key}.html_input` | string | `escape` | `escape`: يظهر HTML الخام في Markdown نصًّا. `allow`، لمجموعة المستودع فقط: يمرّ HTML الخام كما كُتب، ما عدا الوسوم التي يرشّحها GitHub (`<script>` و `<iframe>` و `<style>` وبضعة وسوم أخرى). أما مجموعة قاعدة البيانات فتحوّل HTML الخام دائمًا إلى نص (escape) وتنظّف (sanitize) كل صفحة تُصيّرها. راجع [Markdown](../writing/markdown.md#raw-html). |
| `sets.{key}.integrations.analytics` | object | غير مضبوط | GA4 أو Plausible أو Fathom أو PostHog أو سكربت `https` مخصّص في كل صفحة من المجموعة، بالمفاتيح نفسها التي في [larecipe.json](manifest.md#integrationsanalytics). هذا هو المكان الوحيد الذي تقرؤها منه مجموعة قاعدة البيانات، والمكان الوحيد الذي يُعتدّ فيه بمضيف Plausible أو Fathom أو PostHog مستضاف ذاتيًا. وفي مجموعة المستودع تتقدّم قيمته على `larecipe.json` لكل أداة على حدة. راجع [التحليلات](../operate/analytics.md). |

## القيم الافتراضية لكل المجموعات

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `review` | string | `required` (`LARECIPE_REVIEW`) | نعم | `required`: التغييرات التي يجريها وكيل خارج pull request، مثل التي تمرّ عبر أدوات التأليف في MCP على الويب، تبقى مسودات حتى ينشرها شخص. `auto`: تُنشر فورًا، مع تسجيل مراجعة (revision) لكل منها. وتُكتب القيمة أيضًا في كتلة `AGENTS.md`. |
| `cache.store` | string or null | `null` (`LARECIPE_CACHE_STORE`) | لمصدر قاعدة البيانات فقط | مخزن الذاكرة المؤقتة للصفحات المُصيَّرة وقوائم الصفحات والتنقل والبحث. القيمة `null` تستخدم المخزن الافتراضي للتطبيق. |
| `search.driver` | string | `client` | نعم | `client`: MiniSearch في المتصفح. `server`: أداة الترتيب المكتوبة بـ PHP. وأي قيمة أخرى تسمّي مشغّلًا مسجّلًا عبر `LaRecipe::extendSearch()`. |
| `search.budget_kb` | integer | `300` | نعم | يُبلَّغ عن فهرس المتصفح إن تجاوز هذا الحجم بعد ضغطه بـ gzip. |
| `snippets.root` | string or null | `null` | نعم | المجلد الذي يُحَلّ فيه تضمين المقتطفات (`<<< @/...`). القيمة `null` تعني المسار الأساسي للتطبيق في مجموعات المستودع، أما مجموعات قاعدة البيانات فلا تضمّن شيئًا ما لم تضبط مجلدها الخاص. والملفات التي قد تحمل أسرارًا (الملفات المخفية التي تبدأ بنقطة، و `config/` و `storage/` و `vendor/` والمفاتيح و[البقية](../writing/code-blocks.md#what-can-be-included)) لا تُضمَّن أبدًا. |
| `migrations.path` | string | `database_path('migrations/docs')` | لا | المكان الذي تُكتب فيه ترحيلات التوثيق وتُحمَّل منه. |
| `migrations.skip_in_tests` | boolean | `false` | لا | تخطّي ترحيلات التوثيق حين يشغّل التطبيق اختباراته. |

## نماذج مصدر قاعدة البيانات

نماذج Eloquent التي تقف خلف جداول مجموعة قاعدة البيانات. وجّه أي مفتاح إلى صنف فرعي خاص بك لتضيف نطاقات (scopes) أو دعم تعدد المستأجرين (tenancy) أو مراقبين (observers). تُقرأ من المستوى الأعلى فقط.

| المفتاح | النوع | القيمة الافتراضية |
| --- | --- | --- |
| `models.page` | class name | `LaRecipe\Domains\Sources\Models\Page` |
| `models.navigation` | class name | `LaRecipe\Domains\Sources\Models\Navigation` |
| `models.manifest` | class name | `LaRecipe\Domains\Sources\Models\Manifest` |
| `models.revision` | class name | `LaRecipe\Domains\Sources\Models\Revision` |
| `models.asset` | class name | `LaRecipe\Domains\Sources\Models\Asset` |
| `models.docs_migration` | class name | `LaRecipe\Domains\Sources\Models\DocsMigrationRecord` |

## الذكاء الاصطناعي

تحتاج ميزة اسأل الذكاء الاصطناعي (Ask AI) والأمر `larecipe:sync` إلى `laravel/ai`. ومن دونها لا أثر لمفاتيح `ai.ask`، ويتوقف `larecipe:sync` ويعرض خطوات التثبيت. راجع [Ask AI](../agents/ask-ai.md).

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `ai.ask.enabled` | boolean | `false` (`LARECIPE_ASK`) | نعم | يشغّل Ask AI. حين يعرّف التطبيق البوابة `askLarecipe`، لا يسأل إلا القرّاء الذين تسمح لهم، ومن دونها يستطيع كل قارئ أن يسأل. |
| `ai.ask.provider` | string, list or null | `null` (`LARECIPE_AI_PROVIDER`) | نعم | مزوّد `laravel/ai`. القيمة `null` تستخدم مزوّده الافتراضي. والقائمة ترتيب للتحويل عند الفشل، يُجرَّب حين يفشل مزوّد: `['anthropic', 'openai']`، أو `['anthropic' => '{model}', 'openai' => '{model}']`. |
| `ai.ask.model` | string or null | `null` (`LARECIPE_AI_MODEL`) | نعم | النموذج. القيمة `null` تستخدم النموذج الافتراضي للمزوّد. ينطبق على مزوّد واحد فقط. |
| `ai.ask.max_steps` | integer | `6` | نعم | عدد الخطوات التي يمكن أن يتخذها المساعد لإجابة واحدة. |
| `ai.ask.rate_limit` | string | `'10,1'` | لا | `requests,minutes` لكل قارئ (المستخدم، وإلا عنوان IP)، وتُحسب لكل مجموعة على حدة. |
| `ai.ask.daily_budget` | integer or null | `500` | نعم | عدد الإجابات لكل مجموعة في اليوم. القيمة `null` تعني بلا حد. |
| `ai.ask.max_messages` | integer | `20` | نعم | عدد الرسائل التي يمكن أن تضمّها محادثة واحدة. |
| `ai.ask.max_question_length` | integer | `2000` | نعم | أقصى طول للسؤال بالأحرف، يسري على السؤال الحالي وعلى الأسئلة السابقة. |
| `ai.ask.max_history_length` | integer | `20000` | نعم | أقصى عدد من الأحرف تحمله الأدوار السابقة من المحادثة مجتمعة. |
| `ai.sync.provider` | string or null | `null` (`LARECIPE_AI_PROVIDER`) | لا | مزوّد `laravel/ai` للأمر [`larecipe:sync`](../keeping-current/sync.md). القيمة `null` تستخدم المزوّد الافتراضي في `laravel/ai`. ويتجاوزه `--provider` في تشغيل واحد. |
| `ai.sync.model` | string or null | `null` (`LARECIPE_AI_MODEL`) | لا | النموذج. القيمة `null` تستخدم النموذج الافتراضي للمزوّد. ويتجاوزه `--model` في تشغيل واحد. |
| `ai.sync.max_pages` | integer | `20` | لا | عدد الصفحات التي يمكن أن يغيّرها تشغيل واحد. ويتجاوزه `--max-pages` في تشغيل واحد. |
| `ai.sync.max_steps` | integer | `30` | لا | عدد الخطوات التي يمكن أن يتخذها تشغيل واحد. ويتجاوزه `--max-steps` في تشغيل واحد. |
| `ai.sync.timeout` | integer | `120` | لا | عدد الثواني لكل طلب إلى المزوّد. |
| `ai.sync.remote_token` | string or null | `null` (`LARECIPE_SYNC_TOKEN`) | لا | الرمز (token) الذي يرسله تشغيل `--remote` إلى نقطة نهاية MCP لمجموعة على الإنتاج، لمستخدم تسمح له البوابة `manageLarecipe` فيها. |
| `ai.sync.deny` | list of strings | `[]` | لا | مسارات أو أنماط glob لا يقرؤها وكيل التوثيق أبدًا، تُضاف إلى القائمة المدمجة (الملفات المخفية التي تبدأ بنقطة، و `config/` و `storage/` و `vendor/` والمفاتيح والبقية). |
| `ai.sync.queue` | string or null | `null` (`LARECIPE_SYNC_QUEUE`) | لا | الطابور لتشغيلات `--queue`. القيمة `null` تستخدم الطابور الافتراضي. |

## MCP

نقطة نهاية واحدة على الويب لكل مجموعة، وخادم stdio محلي واحد لكل المجموعات. راجع [خادم MCP](../agents/mcp.md).

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `mcp.web.enabled` | boolean | `true` | نعم | تقديم نقطة نهاية MCP الخاصة بالمجموعة. |
| `mcp.web.path` | string | `mcp` | نعم | مسار نقطة النهاية تحت بادئة المجموعة: `/docs/mcp`، أو `/mcp` لمجموعة في جذر النطاق. |
| `mcp.web.author_tools` | boolean | `false` | نعم | إتاحة أدوات التأليف على الويب. لمجموعة قاعدة بيانات فقط، ولمستدعٍ مسجّل الدخول تسمح له البوابة `manageLarecipe`. |
| `mcp.web.middleware` | list of strings | `[]` | نعم | الـ middleware الذي يصادق على المجموعات الخاصة: `['auth:sanctum']`، أو `['auth:api']` مع `Mcp::oauthRoutes()`. ويُفحص الوصول مجددًا عند كل استدعاء أداة. |
| `mcp.web.rate_limit` | string | `'60,1'` | نعم | `requests,minutes` لكل مستدعٍ (المستخدم، وإلا عنوان IP). |
| `mcp.local` | boolean | `true` | لا | تسجيل خادم stdio المحلي الذي يشغّله `php artisan larecipe:mcp`. |

## واجهات الوكلاء

راجع [واجهات الوكلاء](../agents/surfaces.md).

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `agents.markdown` | boolean | `true` | لا يُقرأ بعد | موجود في الملف، لكن نسخ Markdown من الصفحات تُقدَّم دائمًا في هذا الإصدار. |
| `agents.llms_txt` | boolean | `true` | نعم | `llms.txt` وملفاته الخاصة بكل مسار. |
| `agents.llms_full` | boolean | `true` | نعم | `llms-full.txt`. |
| `agents.skill_md` | boolean | `true` | نعم | `skill.md` وملفات `skills/` المكتوبة يدويًا. |
| `agents.well_known_skills` | boolean | `true` | نعم | `/.well-known/agent-skills/index.json`، ويُدرج حين يكون مفتاحا المهارات كلاهما مفعّلين في مجموعة واحدة على الأقل. |
| `agents.sitemap` | boolean | `true` | نعم | `sitemap.xml`. |
| `agents.ask_endpoint` | boolean | `false` | نعم | البحث `GET /ask?q=` للوكلاء الذين لا يستخدمون MCP. |
| `agents.llms_txt_limit` | integer | `50000` | نعم | عدد الأحرف لكل ملف `llms.txt`. والإصدار الأكبر من ذلك يُقسَّم إلى ملف لكل قسم من أقسام التنقل. |
| `agents.llms_full_limit` | integer | `400000` | نعم | عدد الأحرف لكل ملف `llms-full.txt` قبل أن يُقسَّم حسب أقسام التنقل. |
| `agents.markdown_budget` | integer | `50000` | نعم | يحذّر `larecipe:validate` (`markdown.size`) حين تكون نسخة Markdown للوكلاء من صفحة أكبر من ذلك. |

## الملاحظات

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `feedback.enabled` | boolean | `true` | نعم | عنصر «هل كانت هذه الصفحة مفيدة؟» ونقطة النهاية الخاصة به وأداة MCP `submit_feedback`. كلاهما يطلق `FeedbackSubmitted`، ولا يُخزَّن شيء. |
| `feedback.rate_limit` | string | `'20,1'` | لا | `requests,minutes` لكل قارئ على نقطة النهاية الخاصة بالعنصر. |

## إبقاء التوثيق متزامنًا

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `hooks.enabled` | boolean | `true` ما لم يكن `LARECIPE_HOOKS=off` | لا | فحص التوثيق قبل الـ push في خطّافات الوكلاء. |
| `hooks.push_commands` | list of strings | `git push`, `gh pr create`, `glab mr create`, `hub pull-request` | لا | أوامر الطرفية التي تطلق الفحص. يجمّعها `larecipe:agents` في `bootstrap/cache/larecipe/hooks.php`. |

## تعليمات وكلاء البرمجة

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `instructions.file` | string | `AGENTS.md` | لا | الملف الذي يحمل كتلة LaRecipe. |
| `instructions.index` | boolean | `true` | لا | تضمين فهرس التوثيق في الكتلة. |
| `instructions.index_max_bytes` | integer | `8192` | لا | حد حجم الفهرس (1024 على الأقل). |
| `instructions.default_branch` | string or null | `null` (`LARECIPE_DEFAULT_BRANCH`) | لا | الفرع الذي تُقارن به التغييرات. القيمة `null` تكتشفه من `origin/HEAD`. |

## المراجع الحية

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `references.routes.allow` | list of strings or null | `null` | لا | أسماء المسارات أو روابطها (مع `*`) التي يمكن لـ `<Route>` وصفها. القيمة `null` تعني كل مسار يعرّفه تطبيقك، باستثناء المسارات التي تسجّلها الحزم في `vendor/` (أما مسارات LaRecipe نفسه فتبقى). |
| `references.models.allow` | list of strings | `['App\\Models\\*']` | لا | مساحات أسماء (مع `*`) أو أصناف محددة يمكن لـ `<Model>` وصفها. ولا شيء غيرها. |
| `references.events.namespaces` | list of strings | `['App\\Events']` | لا | حيث يبحث `<Event>` عن الأسماء المختصرة. |

راجع [المراجع الحية](../writing/live-references.md).

## الواجهة {#ui}

| المفتاح | النوع | القيمة الافتراضية | لكل مجموعة | المعنى |
| --- | --- | --- | --- | --- |
| `ui.fresh_days` | number | `1` | لا | الصفحات التي تغيّرت خلال هذا العدد من الأيام تحمل نقطة في الشريط الجانبي إلى أن يفتحها القارئ. القيمة `0` تُلغي النقاط. |
| `ui.dev_server` | string or null | `null` (`LARECIPE_UI_DEV`) | لا | تحميل واجهة القراءة من خادم تطوير Vite على هذا الرابط، للعمل على الواجهة نفسها. |

## تجاوز مفتاح لكل مجموعة {#override-a-key-per-set}

توضع إعدادات المجموعة الخاصة بجوار `route` و `source` و `access`. يدمج LaRecipe أقسام المستوى الأعلى `review` و `cache` و `search` و `ai` و `mcp` و `agents` و `references` و `migrations` في كل مجموعة، وتتقدّم قيمة المجموعة. ويُقرأ `feedback.enabled` و `snippets.root` من المجموعة أولًا أيضًا.

```php
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
        'review' => 'auto',
        'search' => ['driver' => 'server'],
        'agents' => ['sitemap' => false, 'llms_full' => false],
        'mcp' => ['web' => ['middleware' => ['auth:sanctum']]],
    ],
],
```

بعض المفاتيح داخل الأقسام المدموجة تُقرأ من المستوى الأعلى فقط، فلا أثر لقيمة المجموعة فيها: `ai.ask.rate_limit` و `mcp.local` و `references.*` و `migrations.*`، و `cache.store` إلا للذاكرة المؤقتة الخاصة بمصدر مجموعة قاعدة بيانات.

## متغيرات البيئة

يقرأ الملف هذه المتغيرات. ومكان قيمها `.env`، لا التوثيق أبدًا.

| المتغير | ما يضبطه |
| --- | --- |
| `LARECIPE_DEFAULT_SET` | `default` |
| `LARECIPE_PREFIX` | `route.prefix` للمجموعة `docs` |
| `LARECIPE_REVIEW` | `review` |
| `LARECIPE_CACHE_STORE` | `cache.store` |
| `LARECIPE_ASK` | `ai.ask.enabled` |
| `LARECIPE_AI_PROVIDER` و `LARECIPE_AI_MODEL` | `ai.ask.provider` و `ai.ask.model`، والمفتاحان المقابلان في `ai.sync` |
| `LARECIPE_SYNC_TOKEN` | `ai.sync.remote_token`، للأمر `larecipe:sync --remote` |
| `LARECIPE_SYNC_QUEUE` | `ai.sync.queue`، للأمر `larecipe:sync --queue` |
| `LARECIPE_HOOKS` | `hooks.enabled`. ويقرؤه `larecipe:hook` مباشرة أيضًا، فيوقف `LARECIPE_HOOKS=off` الفحص لجلسة واحدة. |
| `LARECIPE_DEFAULT_BRANCH` | `instructions.default_branch` |
| `LARECIPE_UI_DEV` | `ui.dev_server` |
