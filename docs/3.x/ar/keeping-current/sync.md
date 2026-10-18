---
title: "larecipe:sync"
description: شغّل وكيل توثيق داخل تطبيقك بمفتاح مزوّد الذكاء الاصطناعي الخاص بك، عند الحاجة، ليحدّث الصفحات التي تأثرت بتغيير ما، أو يدقّق مجموعة توثيق كاملة، أو يعدّل مجموعة قاعدة بيانات في الإنتاج خارج الـ pull request.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ReviewPolicy.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Enums/Channel.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/WritePageAction.php
  - vendor/larecipe/larecipe/src/Support/SecretFiles.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/composer.json
translated_from: sha256:c720e909867e8d612b5edddc85e621dda97e6e8fe7cb725b49ee52e27d6fc126
---

# larecipe:sync

يشغّل `php artisan larecipe:sync` وكيل توثيق داخل تطبيق Laravel الخاص بك، بمفتاح مزوّد الذكاء الاصطناعي الخاص بك، حين تطلب ذلك. وهو يؤدي العمل الذي يؤديه وكيل البرمجة قبل الـ push: يجد الصفحات التي تأثرت بتغيير ما ويحدّثها. استخدمه حين:

- **تريد تدقيقًا** لمجموعة توثيق كاملة، لا للصفحات التي مسّها تغيير واحد فقط؛
- **لا يملك مطوّرٌ وكيل برمجة محليًا**، ويريد مسودة لتحديث التوثيق في فرعه؛
- **تعدّل مجموعة قاعدة بيانات في الإنتاج** خارج الـ pull request.

ليس هو من يكتب التوثيق افتراضيًا. بل وكيل البرمجة، لأن لديه السياق الكامل للتغيير الذي أجراه قبل لحظات. راجع [كيف يعمل](how-it-works.md).

## شغّله {#run-it}

هو مبني على `laravel/ai`. ثبّت هذه الحزمة وأضف مفتاح المزوّد إلى `.env` بالطريقة التي يتوقعها `laravel/ai`:

```bash
composer require laravel/ai
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
```

ثم شغّله على فرعك. فيحدّث الصفحات التي يمسّها فرعك مقارنةً بالفرع الافتراضي، بما في ذلك العمل الذي لم يُحفظ في commit:

```bash
php artisan larecipe:sync
```

يطبع خطته أولًا (المجموعة، والمهمة، والتغيير الذي يقرؤه، والمزوّد والنموذج، والميزانية، وأين تذهب كتاباته، والصفحات التي سيعمل عليها)، ويسألك قبل أن يستهلك أي token. وفي مجموعة المستودع، تكون النتيجة ملفات Markdown معدَّلة في شجرة العمل لديك، لم تُحفظ في commit، لتراجعها بـ `git diff`.

لا توجد خدمة لـ LaRecipe في المنتصف ولا عدّاد رصيد: فالتشغيل يكلّفك الـ tokens التي يحاسبك عليها مزوّدك.

## أخبره بما يعمل عليه {#tell-it-what-to-work-on}

```bash
php artisan larecipe:sync --base=origin/main --head=HEAD
php artisan larecipe:sync --page=billing/refunds --page=billing/webhooks
php artisan larecipe:sync --audit --dry-run
```

- يعطيه `--base` و `--head` نطاقًا، بالطريقة نفسها التي يعمل بها [`larecipe:affected`](how-it-works.md#larecipeaffected).
- يسمّي `--page` الصفحات بنفسك، مرة لكل صفحة، بمسار الصفحة أو بملفها.
- يراجع `--audit` الإصدار كله: الصفحات التي تغيّرت مصادرها بعدها أولًا، ثم الصفحات التي لا تحوي `sources:`، ثم البقية.
- يذكر `--dry-run` ما كان الوكيل سيغيّره ولا يكتب شيئًا. ابدأ التدقيق به.
- يختار `--set` و `--docs-version` و `--locale` مجموعة توثيق أو إصدارًا أو لغة أخرى. ومن دونها، يعمل على المجموعة والإصدار واللغة الافتراضية.
- يتخطّى `--yes` السؤال. وحين لا توجد طرفية يسأل فيها، لا يعمل إلا مع `--yes`.

## عند الطلب فقط، لا في CI أبدًا {#on-demand-only-never-in-ci}

يعمل `larecipe:sync` حين يشغّله شخص: على جهاز مطوّر، أو على شكل مهمة في الطابور (queue) مع `--queue`. ولا يكون أبدًا جزءًا افتراضيًا من CI. فـ [فحص CI](ci-check.md) الذي يُعدّه LaRecipe لا يشغّل وكيل ذكاء اصطناعي، ولا يرفع شيئًا، ولا يحفظ شيئًا في commit، ولا يحتاج إلى أسرار، ولا يستدعي `larecipe:sync` أبدًا. وداخل المستودع، كل ما يكتبه يبقى في الفرع مع الكود.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="أين تذهب تغييراته، و --commit">

| مجموعتك | ما يكتبه |
| --- | --- |
| مستودع | تعديلات على ملفات Markdown في شجرة العمل لديك، لم تُحفظ في commit، لتراجعها بـ `git diff`. |
| قاعدة بيانات، داخل مستودع git | يُطبَّق التغيير على قاعدة بياناتك المحلية ويُكتب في ترحيل التوثيق الخاص بالفرع في `database/migrations/docs/`، لتراجعه وتحفظه في commit مع الكود. |
| قاعدة بيانات، من دون مستودع git حولها (عامل طابور على الإنتاج) | تغييرات على المجموعة نفسها، حسب [وضع المراجعة](review-modes.md) الخاص بها: مسودات تحت `review: required`، ومراجعات منشورة تحت `review: auto`. |
| قاعدة بيانات، مع `--remote` | تغييرات على مجموعة الإنتاج، عبر أدوات التأليف البعيدة الخاصة بها والمحمية بالمصادقة. مسودات تحت `review: required`، ومراجعات منشورة تحت `review: auto`. |

يحفظ `--commit` تغييرات التوثيق في commit على الفرع الحالي نيابةً عنك:

```bash
php artisan larecipe:sync --commit
```

لا يحفظ في الـ commit إلا الملفات التي كتبها الوكيل، برسالة تسرد كل صفحة وتسمّي الوكيل في سطر `Co-Authored-By`. ويبقى الـ push عليك، فيصل التوثيق مع الكود. ويرفض أن يبدأ حين تكون في ملفات التوثيق تلك تغييرات لم تُحفظ في commit، ولا يمكن جمعه مع `--dry-run`، ولا يعمل إلا حين يكون التغيير داخل الفرع.

</Accordion>

<Accordion title="عدّل مجموعة قاعدة بيانات في الإنتاج بـ --remote">

```bash
php artisan larecipe:sync --remote=https://acme.dev/handbook/mcp
```

يعمل `--remote` خارج أي pull request، فيحدّد وضع المراجعة الخاص بالمجموعة ما يُنشر. ويحتاج إلى `ai.sync.remote_token` (`LARECIPE_SYNC_TOKEN`): رمز يقبله التطبيق البعيد على نقطة نهاية MCP الخاصة به، مثل رمز Sanctum، لمستخدم تسمح له البوابة (gate) `manageLarecipe` هناك. ويجب أن تكون أدوات التأليف عبر الويب مفعّلة في مجموعة الإنتاج (راجع [أوضاع المراجعة](review-modes.md#outside-a-pull-request-drafts-or-published-revisions)). أما مجموعة المستودع فترفض `--remote`، لأن مكان تغييراتها هو git.

</Accordion>

<Accordion title="الإعدادات">

اختر المزوّد والنموذج في `config/larecipe.php`:

```php
// config/larecipe.php
'ai' => [
    'sync' => [
        'provider' => env('LARECIPE_AI_PROVIDER'),
        'model' => env('LARECIPE_AI_MODEL'),
        'max_pages' => 20,
        'max_steps' => 30,
        'timeout' => 120,
        'remote_token' => env('LARECIPE_SYNC_TOKEN'),
        'deny' => [],
        'queue' => env('LARECIPE_SYNC_QUEUE'),
    ],
],
```

| المفتاح | يتحكم في |
| --- | --- |
| `provider` | اسم مزوّد من `config/ai.php`. وحين يكون فارغًا يُستخدم المزوّد الافتراضي في `laravel/ai`. |
| `model` | النموذج. وحين يكون فارغًا يُستخدم النموذج الافتراضي للمزوّد. |
| `max_pages` | عدد الصفحات التي يجوز لتشغيل واحد أن يغيّرها. |
| `max_steps` | عدد الخطوات التي يجوز للوكيل أن يخطوها في تشغيل واحد. |
| `timeout` | الثواني لكل طلب إلى المزوّد. |
| `remote_token` | الرمز الذي يرسله تشغيل `--remote` إلى نقطة نهاية MCP لمجموعة الإنتاج. |
| `deny` | مسارات أو أنماط glob لا يجوز للوكيل أن يقرأها أبدًا، فوق القائمة المدمجة. |
| `queue` | الطابور لتشغيلات `--queue`. وحين يكون فارغًا يُستخدم طابورك الافتراضي. |

يقرأ `ai.sync.provider` و `ai.sync.model` متغيرات البيئة نفسها التي يقرؤها [Ask AI](../agents/ask-ai.md)، فيخدم مزوّد واحد الاثنين ما لم تفصل بينهما.

قبل أن يخطط لأي شيء، يتأكد الأمر من أن `laravel/ai` مثبّت، وأن المزوّد موجود في `config/ai.php` وله مفتاح، وأن `remote_token` مضبوط في حالة `--remote`. وحين ينقص شيء، يتوقف ويذكر بالضبط ما يجب تثبيته أو ضبطه. ولا يُفحص من المفاتيح إلا وجودها؛ ولا تُطبع قيمها أبدًا.

</Accordion>

<Accordion title="الميزانيات والمزوّدون والتشغيلات في الطابور">

كل تشغيل محدود. يضبط `ai.sync.max_pages` و `ai.sync.max_steps` القيم الافتراضية، ويمكنك تغييرها لتشغيل واحد. والصفحات التي تتجاوز الميزانية تُذكر في الخطة وفي التقرير على أنها لم تُنجز. وميزانية الصفحات تحدّ ما يجوز للتدقيق أن يغيّره، لا ما يجوز له أن يقرأه.

```bash
php artisan larecipe:sync --audit --max-pages=5 --max-steps=40
```

ويختار `--provider` و `--model` مزوّدًا أو نموذجًا آخر لتشغيل واحد، بالطريقة نفسها.

افتراضيًا يجري التشغيل في طرفيتك، وينتهي بما تغيّر، وملخص الوكيل، وأي شيء لم يستطع فعله. ومع `--queue`، يصبح التشغيل مهمة عادية في الطابور بدلًا من ذلك، بمحاولة واحدة: يمكنك أن تتابعها في Horizon أو Nightwatch، ويذهب تقريرها إلى سجلّك. وفي الاختبارات، زيّفه كأي وكيل آخر في `laravel/ai`، بـ `DocsMaintainer::fake()`.

</Accordion>

<Accordion title="الوكيل وأدواته">

الوكيل هو `DocsMaintainer`، وهو وكيل من `laravel/ai`. ولأنه يعمل داخل تطبيقك بعد تشغيله، فهو يكتب مما يفعله التطبيق فعلًا، لا من نص المستودع وحده. ويتبع سير العمل نفسه الذي يتبعه وكيل البرمجة: المهارة `larecipe-update-docs`، أو `larecipe-audit-docs` للتدقيق.

| الأداة | ما تفعله |
| --- | --- |
| `search_docs`، `read_page`، `list_pages` | تبحث في التوثيق وتقرؤه، كما يفعل أي عميل MCP. |
| `read_page_source` | ترويسة الصفحة و Markdown الخاص بها كما كُتبا، مع بصمة المحتوى (content hash)، فيعدّل الوكيل المصدر الحقيقي ولا يستبدل أبدًا تعديلًا أحدث. |
| `affected_pages` | الفحص نفسه الذي يجريه `larecipe:affected`: الصفحات التي جعلها تغييرٌ ما قديمة. |
| `read_code`، `git_diff` | تقرأ الملفات والفروقات في المشروع، للقراءة فقط. |
| `app_reference` | المسارات وأوامر artisan ومخططات النماذج والأحداث، بالطريقة التي تقرؤها بها [المراجع الحية](../writing/live-references.md). ولا قيم الإعدادات أو البيئة أبدًا. |
| `validate_docs` | تقرير `larecipe:validate`، ليصلح الوكيل ما كتبه. |
| أدوات التأليف | `write_page` و `move_page` و `delete_page` و `update_navigation` و `update_manifest`، وهي الأدوات نفسها التي يستخدمها وكيل البرمجة، فيُطبَّق التحقق وترحيلات التوثيق ووضع المراجعة كما يُطبَّق على أي كاتب آخر تمامًا. |

كل أداة مقيّدة بمجموعة التوثيق والإصدار واللغة الخاصة بالتشغيل. ولا تغادر `read_code` و `git_diff` المشروع أبدًا، ولا تفتحان أبدًا الملفات التي قد تحوي قيم الإعدادات أو البيئة أو الأسرار: وهي القائمة نفسها التي ترفضها تضمينات المقتطفات، الموصوفة في [كتل الكود](../writing/code-blocks.md#what-can-be-included). ويذكر `git_diff` اسم مثل هذا الملف من دون أن يعرضه. ويضيف `ai.sync.deny` مسارات إلى القائمة، ولا شيء يمكنه أن يحذف منها مسارًا.

ميزانية الصفحات والتشغيل التجريبي مفروضان في الكود، أمام أدوات التأليف، فلا يستطيع نموذج مرتبك أن يكتب متجاوزًا إياهما.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [أوضاع المراجعة](review-modes.md): المسودات والمراجعات المنشورة للتغييرات خارج الـ pull request.
- [ترحيلات التوثيق](../sources/docs-migrations.md): كيف تنتقل تغييرات مجموعة قاعدة البيانات داخل الـ pull request.
- [أوامر artisan](../reference/commands.md#larecipesync): كل الخيارات، مقروءة من التطبيق العامل.
