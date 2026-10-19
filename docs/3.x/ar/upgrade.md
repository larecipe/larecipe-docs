---
title: الترقية من الإصدار 2
description: انقل موقع LaRecipe 2 إلى LaRecipe 3 بأمر واحد وتقرير، ثم أكمل البنود اليدوية مع وكيل البرمجة والمهارة larecipe-upgrade-v2.
sources:
  - vendor/larecipe/larecipe/src/Domains/Upgrade/**
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Access/Laravel/GateAuthorizer.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltIn/Inline.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentProcessor.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-upgrade-v2/**
  - routes/web.php
translated_from: sha256:e9129b4401b683b59c294c8c3c785a7352e8a489832c96efe90f17fe96908fa0
---

# الترقية من الإصدار 2

يحوّل `php artisan larecipe:upgrade` موقع LaRecipe 2 إلى LaRecipe 3: إعداداتك، والشريط الجانبي لكل إصدار، وكل صفحة. يبدأ بتشغيل تجريبي لا يكتب إلا تقريرًا. وحين تبدو الخطة صحيحة، يكتب `--apply` تلك الخطة بالضبط، ويكمل وكيل البرمجة الباقي بالمهارة `larecipe-upgrade-v2`.

ابدأ من شجرة عمل نظيفة، لتكون الترقية diff واحدًا تستطيع قراءته والتراجع عنه. يحتاج LaRecipe 3 إلى PHP 8.3 و Laravel 12.62 أو أحدث، راجع [التثبيت](get-started/installation.md).

<Steps>

<Step title="أضف LaRecipe 3">

```bash
composer require larecipe/larecipe
```

تقرأ الترقية ملفات الإصدار 2 لديك، لا حزمة الإصدار 2. فإن كان تطبيقك يستدعي أصناف `BinaryTorch\LaRecipe` أثناء إقلاعه، مثل `LaRecipe::script()` في مزوّد خدمة، فأبقِ `binarytorch/larecipe` مثبّتة حتى تُشغَّل الترقية. وإن لم يستطع Composer تثبيت الحزمتين معًا، فاحذف حزمة الإصدار 2 أولًا وعلّق تلك الاستدعاءات.

</Step>

<Step title="اقرأ التشغيل التجريبي">

```bash
php artisan larecipe:upgrade
```

لا يتغيّر شيء في تطبيقك. يكتب الأمر `larecipe-upgrade-report.md` ونسخته بصيغة JSON في جذر مشروعك. شغّله كلما شئت.

</Step>

<Step title="طبّقه">

```bash
php artisan larecipe:upgrade --apply --move-to=docs --relative-links
```

الخياران اختياريان. ينقل `--move-to=docs` التوثيق من `resources/docs/` إلى `docs/`، حيث يحفظه الإصدار 3. ويحوّل `--relative-links` روابط `/docs/{{version}}/page` إلى روابط `.md` نسبية، فاستخدمه حين ستتغيّر بادئة المسار لديك.

</Step>

<Step title="استبدل الحزمة">

```bash
composer remove binarytorch/larecipe
```

ثم غيّر `BinaryTorch\LaRecipe\` إلى `LaRecipe\` في الكود لديك و[حدّث البوابة (gate) viewLarecipe](#update-the-viewlarecipe-gate). ويسرد التقرير كل سطر عليك تغييره.

</Step>

<Step title="أكمل مع وكيل البرمجة">

ثبّت مهارات LaRecipe، ثم اطلب من وكيلك أن يكمل:

```bash
php artisan larecipe:agents
```

```text
Use the larecipe-upgrade-v2 skill to finish the LaRecipe upgrade.
```

</Step>

<Step title="تحقّق من النتيجة">

```bash
php artisan larecipe:validate
```

ثم مرّ على [التحقق بعد الترقية](#check-after-the-upgrade)، وافتح التوثيق في متصفحك، وأضف التغييرات إلى git.

</Step>

</Steps>

وقد مرّ هذا الموقع بالترقية نفسها: [توثيق 2.x](/2.x) فيه هو توثيق LaRecipe 2 بعد تحويله.

## حدّث البوابة viewLarecipe {#update-the-viewlarecipe-gate}

تحتفظ البوابة باسمها، لكن وسائطها تغيّرت. كان الإصدار 2 يمرّر كائن صفحة التوثيق. أما الإصدار 3 فيمرّر ملخص الصفحة ومفتاح المجموعة:

```php
// LaRecipe 2
Gate::define('viewLarecipe', function ($user, $documentation) {
    return $user->isAdmin();
});
```

```php
// LaRecipe 3
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use LaRecipe\Domains\Content\DTOs\PageSummary;

Gate::define('viewLarecipe', function (?User $user, ?PageSummary $page = null, ?string $set = null) {
    return $user?->isAdmin() ?? false;
});
```

اجعل نوع المستخدم قابلًا للقيمة الفارغة إن كان ينبغي للزوّار أن يمرّوا في أي وقت: وإلا فإن Laravel يرفض الزوّار قبل أن تعمل الدالة. وفي الإصدار 3 تحرس البوابة كل الواجهات، لا صفحة HTML وحدها. وتشرح صفحة [كتل الصلاحيات والتوثيق الخاص](writing/gate-blocks.md) الوسائط، وأوضاع الوصول، والبوابات الأخرى.

## أكمل بالمهارة larecipe-upgrade-v2 {#finish-with-the-larecipe-upgrade-v2-skill}

يثبّت `php artisan larecipe:agents` المهارة في `.claude/skills/` و `.agents/skills/`، بجوار مهارات LaRecipe الأخرى، ويكتب تعليمات LaRecipe في `AGENTS.md`. ومع Laravel Boost، شغّل `php artisan boost:update` بدلًا منه.

يقرأ الوكيل التقرير ونسخته بصيغة JSON، ثم يعمل على البنود اليدوية: ما يكسر التصيير أولًا، ثم المحتوى، ثم الشكل.

- **Blade في Markdown** يصبح مكوّنات، أو تضمين مقتطفات (`<<< @/path#region`)، أو نصًا عاديًا. والقيم التي كانت تأتي من الإعدادات تصبح نصًا يسمّي المفتاح، لا القيمة أبدًا.
- **مكوّنات Vue المخصّصة** تصبح مكوّنات React مبنية بـ `@larecipe/vite`، ويُسجَّل كلٌّ منها بـ `LaRecipe::component()`. وإلى أن يُعاد بناء أحدها، يستبدل الوكيل الوسم بـ Markdown عادي. راجع [المكوّنات المخصّصة](customize/custom-components.md).
- **المظاهر و CSS المخصّص** تنتقل إلى رموز المظهر في `larecipe.json` (اللون المميّز، والخطوط، ونصف قطر الحواف)، والباقي يوضع في `theme.css` مُدرج تحت `assets.css`. راجع [المظهر](customize/theme.md#add-your-own-stylesheet).
- **كل صفحة** تحصل على `description`، وعلى `sources:` حيث تشرح كودًا. وروابط المسارات أو تواقيع الأوامر الملصوقة تصبح [مراجع حية](writing/live-references.md).

ينتهي الوكيل حين ينجح `php artisan larecipe:validate`، ويترك لك إضافة التغييرات إلى git، وفق وضع المراجعة في `AGENTS.md`.

## تحقّق بعد الترقية {#check-after-the-upgrade}

المحوّل حتمي، لا مثالي. مرّ على قائمة "Left for you" في التقرير، ثم تحقّق من هذه:

- **الشارات والأزرار.** الشارة أو الزر في الإصدار 2 حين يكون في سطر مستقل يصبح `<Badge tone="success">Completed</Badge>` أو `<Button href="..." variant="primary">Text</Button>`. والنوع في الإصدار 2 الذي لا مقابل له في الإصدار 3، والأيقونات، وسمات التنسيق، تُحذف وتُسرد في التقرير، والشارة التي لا نص فيها تُزال. انظر إلى كلٍّ منها في المتصفح.
- **الروابط التي احتفظت بـ `{{version}}`.** يبقى الرابط `/docs/{{version}}/...` كما كان ما لم تمرّر `--relative-links`. ويظل يعمل ما دامت البادئة فيه صحيحة. جِد ما تبقّى منها بالأمر `grep -rn '{{' docs/`، وأعد كتابة كلٍّ منها رابط `.md` نسبيًا إن تغيّرت بادئتك.
- **الروابط القديمة.** إن تغيّرت بادئة المسار لديك، فأبقِ الروابط القديمة ونتائج البحث تعمل بإعادات التوجيه.

<Accordion title="كيف يُبقي هذا الموقع روابط الإصدار 2 تعمل">

نقل هذا الموقع توثيق الإصدار 2 من `/docs/{version}/{page}` إلى `/2.x/{page}`، ويحوي ملف `routes/web.php` فيه:

```php title="routes/web.php"
Route::permanentRedirect('/docs', '/');
Route::permanentRedirect('/docs/{version}', '/2.x');
Route::permanentRedirect('/docs/{version}/{page}', '/2.x/{page}');
```

استخدم مسارات تطبيقك لبادئة قديمة كاملة. أما داخل بادئة واحدة، فاستخدم `redirects` في `larecipe.json` بدلًا من ذلك، راجع [التنقل](writing/navigation.md).

</Accordion>

## ما الذي تغيّره الترقية {#what-the-upgrade-changes}

التفاصيل وراء التقرير، لحين تريد أن تعرف بالضبط ما الذي حدث لملف ما.

<AccordionGroup>

<Accordion title="ما الذي يكتبه الأمر">

| التشغيل | ما يكتبه |
| --- | --- |
| `php artisan larecipe:upgrade` | التقرير فقط: `larecipe-upgrade-report.md` و `larecipe-upgrade-report.json`. |
| `php artisan larecipe:upgrade --apply` | ملف `config/larecipe.php` الجديد (ويُحفظ ملف الإصدار 2 باسم `config/larecipe.v2.php.bak`)، و `larecipe.json`، وملف `navigation.json` لكل إصدار، وكل صفحة محوّلة، والتقرير. |

يكتب `--apply` الملفات الجديدة والمحوّلة أولًا ويحذف القديمة أخيرًا، فلا يضيع أي صفحة إن انقطع التشغيل. ومع `--move-to`، يجب أن يكون المجلد الهدف فارغًا أو غير موجود، ويُحذف المجلد القديم حين لا يبقى فيه شيء.

يتوقف الأمر مبكرًا حين لا يكون هناك ما يُرقّى: حين لا يوجد `config/larecipe.php`، أو حين يحوي ذلك الملف أصلًا المفتاح `sets` الخاص بالإصدار 3، أو حين لا يوجد مجلد التوثيق الذي يسمّيه. وبعد `--apply` تصبح إعداداتك للإصدار 3، فلتشغّل الترقية مرة أخرى، استعد ملفات الإصدار 2 من git أولًا.

</Accordion>

<Accordion title="الإعدادات: ملف واحد يصبح ملفين">

تصبح إعدادات الإصدار 2 لديك ملفين: البنية التحتية في `config/larecipe.php`، والمحتوى والعرض في `larecipe.json`. راجع [أساسيات الإعداد](get-started/configuration.md).

| الإصدار 2 | الإصدار 3 |
| --- | --- |
| `docs.route` | `sets.docs.route.prefix` |
| `docs.path` | `sets.docs.source.path` (أو مجلد `--move-to`) |
| `docs.middleware` و `settings.middleware` | `sets.docs.route.middleware` |
| `settings.auth` | `access.mode` بالقيمة `auth` |
| البوابة `viewLarecipe` في الكود لديك | `access.mode` بالقيمة `gate` (وإلا فـ `public`) |
| `versions.published` و `versions.default` | `versions` في `larecipe.json` |
| `docs.landing` | `landing` في `larecipe.json` |
| `ui.fav` | `favicon` في `larecipe.json` |
| `ui.colors.primary` | `theme.accent` في `larecipe.json` |
| `seo.description` | `description` في `larecipe.json` |
| `settings.ga_id` | تكامل GA4 تحت `integrations.analytics` في `larecipe.json` |

يأتي `name` في ملف التعريف من `APP_NAME` لديك. ولا تحوي الإعدادات المكتوبة إلا المجموعة `docs`، وكل مفتاح آخر يحتفظ بالقيمة الافتراضية للحزمة. وانشر الملف كاملًا بالأمر `php artisan vendor:publish --tag=larecipe-config` فقط إن أردته في تطبيقك.

</Accordion>

<Accordion title="التنقل: الشريط الجانبي للإصدار 2 يصبح navigation.json">

كان `index.md` في كل إصدار هو شريطه الجانبي في الإصدار 2. تحلّل الترقية عناوين `## Group` والروابط فيه إلى ملف `navigation.json` لذلك الإصدار، وتحتفظ بتسمية الرابط حين تختلف عن عنوان الصفحة، وتُبقي الروابط الخارجية روابط، وتحذف `index.md` القديم. ويُبلَّغ عن أي سطر في الشريط الجانبي ليس عنوان مجموعة ولا رابطًا. والإصدار الذي لا `index.md` فيه يحصل على [تنقل من مجلداته](writing/navigation.md).

</Accordion>

<Accordion title="الصفحات: ما الذي يصبحه كل سطر">

تُعاد كتابة كل صفحة سطرًا بسطر. ولا تُمسّ كتل الكود أبدًا.

| الإصدار 2 | الإصدار 3 |
| --- | --- |
| `> {warning} text` و `> {info} text` وأنواع التنبيهات الأخرى | تنبيهات GitHub: `primary` يصبح `IMPORTANT`، و `secondary` و `info` يصبحان `NOTE`، و `success` يصبح `TIP`، و `warning` يبقى `WARNING`، و `danger` يصبح `CAUTION` |
| `<a name="x"></a>` قبل عنوان | معرّف عنوان `{#x}`، فتظل الروابط القديمة `#x` تصل |
| `<larecipe-card>` | `<Card>` |
| `<larecipe-badge>` و `<larecipe-button>` في سطر مستقل | `<Badge tone="success">Text</Badge>` و `<Button href="..." variant="primary">Text</Button>`، مع تحويل نوع الإصدار 2 إلى `tone` أو `variant` |
| `<larecipe-badge>` و `<larecipe-button>` داخل جملة | نصّهما |
| روابط `/{{route}}/{{version}}/page#anchor` | روابط نسبية: `page.md#anchor` |
| روابط `/docs/{{version}}/page` | تبقى، لأن الإصدار 3 ما زال يملأ `{{version}}`، وتصبح نسبية مع `--relative-links` |
| قوائم `<ul>` و `<ol>` البسيطة، بعنصر `<li>` واحد في كل سطر | قوائم Markdown |
| سطر لا يحوي إلا `<br>` | يُحذف |
| أول `# Heading` والفقرة التي تليه | `title` و `description` في ترويسة الصفحة، وتغادر الفقرة المتن ما لم تحوِ رابطًا |
| جدول محتويات مكتوب يدويًا تحت العنوان | يُحذف: فالإصدار 3 يبني جدولًا من العناوين |

تُعاد كتابة الروابط في كل مكان، حتى داخل التنبيهات المحوّلة. وتُحفظ ترويسة الصفحة التي كانت موجودة أصلًا، ولا تضيف الترقية `title` و `description` إلا حين يكونان مفقودين.

</Accordion>

<Accordion title="ما الذي تكتفي بالإبلاغ عنه">

بعض الأمور تحتاج إلى شخص، أو إلى وكيلك. تسردها الترقية تحت "Left for you" في التقرير، مع الملف والسطر:

- Blade في Markdown (`@if` و `@include` و `{{ $variable }}` و `{!! !!}`). فالإصدار 3 لا ينفّذ Blade في المحتوى.
- مكوّنات Vue المخصّصة، وأي وسم `larecipe-` لم تستطع تحويله.
- HTML الخام، الذي يعرضه الإصدار 3 نصًا. استبدله بـ Markdown أو بمكوّن، أو اتركه نصًا، أو دع مجموعة مستودع تسمح به (راجع [Markdown](writing/markdown.md#raw-html)).
- مرساة `<a name>` لا عنوان بعدها.
- استدعاءات `LaRecipe::script()` و `LaRecipe::style()`، واستخدامات نطاق الأسماء `BinaryTorch\LaRecipe`، والبوابة `viewLarecipe` لديك، في `app/` و `routes/` و `config/` و `bootstrap/`.
- واجهات الإصدار 2 المنشورة في `resources/views/vendor/larecipe/`.
- حزم المظاهر والملفات المرفقة في مجلد حزم الإصدار 2 (`larecipe-components/` افتراضيًا).
- `settings.guard`: يستخدم الإصدار 3 الحارس الافتراضي لديك، فأضف middleware المصادقة الخاص بذلك الحارس إلى `route.middleware` الخاص بالمجموعة، أو اجعله الحارس الافتراضي.
- ملف `README.md` في مجلد إصدار، فالإصدار 3 يقدّمه صفحة.

</Accordion>

<Accordion title="ما الذي تحذفه عن قصد">

إعدادات الإصدار 2 هذه لا مقابل لها في الإصدار 3. ويسرد التقرير ما استخدمته إعداداتك منها:

- `cache`: يخزّن الإصدار 3 الصفحات المُصيَّرة مؤقتًا حسب المحتوى دائمًا. شغّل `php artisan optimize` عند النشر، فهو يشغّل `larecipe:optimize`.
- `search` و Algolia: البحث مدمج. احذف مفاتيح Algolia من `.env`.
- `ui.code_theme`: تتبع كتل الكود الوضع الفاتح أو الداكن للصفحة.
- `ui.colors.secondary`: لمظاهر الإصدار 3 لون مميّز واحد.
- `ui.fa_v4_shims` و Font Awesome: يستخدم الإصدار 3 أيقونات Lucide. وتُحذف الأيقونات في تنبيهات الإصدار 2، فتنبيهات GitHub تحمل أيقوناتها الخاصة.
- `ui.theme_order`: لا توجد حزم مظاهر متراكبة.
- `ui.show_side_bar`: استخدم `layout: wide` أو `layout: full` في ترويسة الصفحة.
- `seo.author` و `seo.keywords` و `seo.og`: تصف كل صفحة نفسها عبر ترويستها.
- `forum` (Disqus): أداة الملاحظات المدمجة ترسل حدث `FeedbackSubmitted` تستطيع تمريره إلى أي مكان.
- `blade-parser`.
- أشرطة `<larecipe-progress>`، وسمات التنسيق في البطاقات والشارات والأزرار.

وتُحذف حزم الإضافات للإصدار 2 أيضًا، مع سطر `composer remove` لكل منها: `binarytorch/larecipe-dark-theme` و `binarytorch/larecipe-rtl` (كلتاهما مدمجة الآن)، و `binarytorch/larecipe-feedback` (أداة الملاحظات مدمجة)، و `binarytorch/larecipe-swagger` (صفحات OpenAPI مخطّط لها في إصدار 3.x لاحق، فاربط بملف المواصفات لديك حتى ذلك الحين).

</Accordion>

<Accordion title="التقرير">

`larecipe-upgrade-report.md` موجّه إليك، و `larecipe-upgrade-report.json` يقول الشيء نفسه للمهارة. وكلاهما يحوي:

1. **Composer and code** (Composer والكود): الأوامر التي تشغّلها، وتغيير نطاق الأسماء، وتغيير البوابة.
2. **Left for you** (متروك لك): قائمة تحقق بالبنود اليدوية، لكلٍّ منها ملفه وسطره وإصلاح مقترح.
3. **Files** (الملفات): كل ملف تكتبه الترقية أو تنسخه أو تحذفه، والسبب.
4. **What changed** (ما الذي تغيّر): ما الذي حُوِّل، لكل صفحة وإجمالًا.
5. **Dropped on purpose** (ما حُذف عن قصد): ما لا مقابل له في الإصدار 3.
6. **Validation** (التحقق): كل صفحة محوّلة مُصيَّرة بأداة التصيير في الإصدار 3، ومفحوصة بحثًا عن مكوّنات غير صالحة، ومشكلات في الصفحة، وروابط إلى صفحات غير موجودة، ومراسٍ مفقودة.

يسمّي التقرير مفاتيح الإعدادات لكنه لا ينسخ قيمها أبدًا، مثل مفتاح Algolia أو معرّف التحليلات، فلصقه في issue آمن.

</Accordion>

<Accordion title="الحزمة ونطاق الأسماء">

| | LaRecipe 2 | LaRecipe 3 |
| --- | --- | --- |
| الحزمة | `binarytorch/larecipe` | `larecipe/larecipe` |
| نطاق الأسماء | `BinaryTorch\LaRecipe\` | `LaRecipe\` |

الواجهة الساكنة (facade) في الإصدار 3 هي `LaRecipe\Facades\LaRecipe`، باسم مستعار `LaRecipe`. وتحقّق من أن كل صنف تستخدمه ما زال موجودًا: فكثير من واجهة الإصدار 2 البرمجية، مثل `LaRecipe::script()` و `LaRecipe::style()`، لم يعد موجودًا. يطبع `LaRecipe::head()` أوراق الأنماط والسكربتات داخل `<head>` في كل صفحة توثيق، و[المكوّنات المخصّصة](customize/custom-components.md) تحلّ محلّ JavaScript المخصّص.

</Accordion>

<Accordion title="ميزات الإصدار 2 في الإصدار 3">

| LaRecipe 2 | LaRecipe 3 |
| --- | --- |
| موقع توثيق واحد لكل تطبيق | [مجموعات التوثيق](get-started/configuration.md): عدة مواقع مستقلة في تطبيق واحد، لكلٍّ منها مساره ومصدره ووصوله وواجهات الوكلاء الخاصة به |
| الإصدارات في الإعدادات | `versions` في `larecipe.json`، مع حالة لكل إصدار، ومجلد لكلٍّ منها. راجع [الإصدارات](writing/versions.md). |
| الشريط الجانبي في `index.md` لكل إصدار | `navigation.json`، أو تنقل مستنتج من المجلدات. راجع [التنقل](writing/navigation.md). |
| `settings.auth` والبوابة `viewLarecipe` | `access.mode` (`public` و `auth` و `gate`)، والبوابة نفسها في كل واجهة، والصفحات والإصدارات الخاصة، وكتل `<Can>`. راجع [كتل الصلاحيات والتوثيق الخاص](writing/gate-blocks.md). |
| Parsedown | league/commonmark مع Markdown بنكهة GitHub. راجع [Markdown](writing/markdown.md). |
| تنبيهات `> {type}` | تنبيهات GitHub و `<Callout>` |
| Blade في Markdown | لا يُنفَّذ. [المكوّنات](writing/components.md) و[تضمين المقتطفات](writing/code-blocks.md) والمراجع الحية بدلًا منه. |
| `{{version}}` في الروابط | ما زال يُملأ، والروابط النسبية `.md` هي المفضّلة |
| `{{route}}` في الروابط | روابط `.md` نسبية |
| مكوّنات Vue للبطاقة والشارة والزر | `<Card>` و `<Badge>` و `<Button>` |
| مكوّن شريط التقدم | أُزيل |
| مكوّنات Vue المخصّصة | [مكوّنات React المخصّصة](customize/custom-components.md) مع بديل يُعرض على الخادم |
| Algolia أو البحث المدمج | بحث مدمج في المتصفح، أو مرتِّب على الخادم، أو مشغّل مخصّص. راجع [البحث](operate/search.md). |
| حزمة المظهر الداكن | مدمج: فاتح، أو داكن، أو حسب إعداد نظام القارئ |
| حزمة RTL | مدمجة: لغة مع `"dir": "rtl"`. راجع [اللغات](writing/languages.md). |
| `colors.primary` و `fav` | `theme.accent` و `favicon` في `larecipe.json`. راجع [المظهر](customize/theme.md). |
| `code_theme` | تتبع كتل الكود الوضع الفاتح والداكن |
| `show_side_bar` | `layout: wide` أو `layout: full` في ترويسة الصفحة |
| Font Awesome | أيقونات Lucide |
| `LaRecipe::script()` و `LaRecipe::style()` | `assets.css` في `larecipe.json`، و `LaRecipe::head()`، وإضافات React |
| حزم المظاهر والملفات المرفقة | إضافات مبنية بـ `@larecipe/vite` |
| الواجهات المنشورة | فتحات (slots) مسمّاة تتجاوزها من إضافة، مثل `Header` و `Logo` و `Sidebar` و `Footer`، و `LaRecipe::head()` |
| إعدادات التخزين المؤقت | تخزين مؤقت حسب المحتوى دائمًا، و `php artisan optimize` عند النشر. راجع [التخزين المؤقت](operate/caching.md). |
| إعدادات SEO | `description` لكل صفحة، وروابط أساسية (canonical)، و JSON-LD، وخريطة موقع |
| `ga_id` | تكاملات التحليلات: GA4 أو Plausible أو Fathom أو PostHog أو سكربت مخصّص |
| Disqus | أداة الملاحظات، وروابط التعديل حين يُضبط `repository` في `larecipe.json` |
| اختصارات لوحة المفاتيح | `⌘K` أو `/` للبحث، و `?` لكل الاختصارات |
| حزمة الملاحظات | مدمجة، وترسل `FeedbackSubmitted` إلى تطبيقك |
| حزمة Swagger | صفحات OpenAPI، مخطّط لها في إصدار 3.x لاحق |
| `larecipe:docs` | `larecipe:make:version` و `larecipe:make:page` |
| `larecipe:theme` و `larecipe:asset` | إضافات مبنية بـ `@larecipe/vite` |

وما لم يملكه الإصدار 2 قط: نسخ Markdown، و `llms.txt`، و `skill.md`، وخادم MCP للوكلاء، والمراجع الحية، وبيانات التحديث (provenance) في كل صفحة، ووكلاء يُبقون التوثيق محدّثًا مع الكود. ابدأ بصفحة [كيف يعمل](keeping-current/how-it-works.md).

</Accordion>

<Accordion title="كل الخيارات">

مقروءة مباشرة من سجلّ الأوامر في هذا التطبيق:

<Artisan command="larecipe:upgrade" />

</Accordion>

</AccordionGroup>

## التالي {#next}

- [أساسيات الإعداد](get-started/configuration.md): ما الذي يحويه ملف `config/larecipe.php` الجديد.
- [كتل الصلاحيات والتوثيق الخاص](writing/gate-blocks.md): البوابات في الإصدار 3.
- [التثبيت](get-started/installation.md): إعداد الوكلاء، وخادم MCP، والخطّافات، لموقع رقّيته.
