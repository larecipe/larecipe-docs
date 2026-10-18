---
title: ترحيلات التوثيق
description: اشحن تغييرات المحتوى إلى مجموعة توثيق في قاعدة البيانات ضمن الـ pull request، على شكل ملفات ترحيل يطبّقها php artisan migrate عند النشر من دون أن تكتب أبدًا فوق تعديل أحدث.
sources:
  - vendor/larecipe/larecipe/src/Migrations/DocsMigration.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ApplyDocsMigrationAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/RollbackDocsMigrationAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DocsMigrationWriter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/DocsMigrationReader.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitBranch.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/DocsMigrationOperations.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/DocsMigrationRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/PageAddresses.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/Preconditions.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/NavigationPatcher.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/MakeDocsMigrationCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/PullCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/SourcesServiceProvider.php
  - vendor/larecipe/larecipe/stubs/docs-migration.stub
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:118e8fe608e62e9ccadfba4c6b4b4614cca516cc1b3d1667fccefe2d6512e75d
---

# ترحيلات التوثيق

تحفظ [مجموعة التوثيق في قاعدة البيانات](database.md) محتواها في قاعدة بياناتك، فلا يستطيع الـ pull request أن يحمل تغيير التوثيق على شكل ملفات Markdown. وبدلًا من ذلك يحمله ترحيل توثيق: ملف ترحيل Laravel عادي، يُراجع في الـ pull request مع الكود، ويطبّقه `php artisan migrate` عند النشر. ولا تحتاج إليها إلا لمجموعات قاعدة البيانات.

يكتبها وكيل البرمجة نيابةً عنك. ففي مجموعة قاعدة البيانات يغيّر التوثيق عبر أدوات التأليف في خادم MCP المحلي (`write_page` و `move_page` و `delete_page` و `update_navigation` و `update_manifest`)، وكل استدعاء يفعل أمرين: يطبّق التغيير على قاعدة بياناتك المحلية، فتستطيع معاينته فورًا، ويكتب التغيير في ترحيل التوثيق الخاص بالفرع في `database/migrations/docs/`. أضف ذلك الملف في commit مع الكود وارفعهما معًا.

يبدو الترحيل الصغير هكذا:

```php title="database/migrations/docs/2026_10_17_141502_docs_feature_csv_exports.php"
<?php

use LaRecipe\Migrations\DocsMigration;

return new class extends DocsMigration
{
    protected string $set = 'handbook';

    protected string $summary = 'Document CSV exports';

    public function up(): void
    {
        $this->createPage('3.x/billing/exports', frontmatter: [
            'title' => 'CSV exports',
            'description' => 'Export invoices as a CSV file from the billing page.',
        ], body: <<<'MD'
            # CSV exports

            Open **Billing**, then choose **Export as CSV**.
            MD);
    }
};
```

لا حاجة إلى كتابة `down()`: فالصنف الأساسي فيه واحدة مدمجة.

## لا يكتب أبدًا فوق تعديل أحدث

يسجّل كل تغيير بصمة المحتوى الذي بُني عليه. وعند النشر، إن تغيّر الإنتاج في الأثناء (تعديل في لوحة الإدارة، أو وكيل يكتب في وضع المراجعة `auto`)، لا يكتب الترحيل فوقه. بل تنتظر نسخته مسودةَ تعارض في [قائمة المراجعة (The pass)](database.md#the-pass-the-review-queue) حتى يحلّها شخص، ويطبع `php artisan migrate` تحذيرًا ويتابع، فلا يُفشل تعارضٌ في التوثيق عمليةَ نشر أبدًا.

## ابدأ مما هو حي {#start-from-what-is-live}

قد تتأخر قاعدة بياناتك المحلية عن الإنتاج، والتغييرات المكتوبة على محتوى قديم تصبح تعارضات عند النشر. حدّثها قبل جلسة التوثيق. على الإنتاج، صدّر المجموعة:

```bash
php artisan larecipe:source:export --set=handbook --to=storage/handbook.tar.gz
```

انسخ الملف إلى جهازك، ثم حمّله:

```bash
php artisan larecipe:pull --set=handbook --from=storage/handbook.tar.gz
```

يستورد `larecipe:pull` الحزمة إلى مجموعة قاعدة البيانات المحلية، ويفحص كل بصمة محتوى، ويرفض مجموعات المستودع، فهي في git أصلًا. ولا يقرأ إلا الحزم: السحب مباشرة من نقطة نهاية MCP في الإنتاج غير متاح بعد، وعنوان URL يُرفض مع هذا التوضيح.

## اكتب ترحيلًا يدويًا

```bash
php artisan larecipe:make:docs-migration document_billing_exports --page=3.x/billing/exports
```

ينشئ هذا الأمر `database/migrations/docs/{timestamp}_docs_document_billing_exports.php`، وفيه `updatePage` واحد لكل `--page`، معبّأ بترويسة الصفحة الحالية ومتنها وبصمة محتواها. حرّر Markdown في مكانه: البصمة صحيحة أصلًا، فيبقى التغيير آمنًا من التعارضات.

<Accordion title="المزيد عن make:docs-migration">

يأتي الملخّص من الاسم ("Document billing exports"). ومن دون `--page`، يحوي الملف مثالًا في تعليق. ويحذّر الأمر حين تستخدم المجموعة مصدر المستودع، الذي لا تعمل ترحيلات التوثيق فيه أبدًا. ولتغيّر الملف المولَّد، انشر القالب بالأمر `php artisan vendor:publish --tag=larecipe-stubs` وحرّر `stubs/docs-migration.stub`.

<Artisan command="larecipe:make:docs-migration" />

</Accordion>

## تفاصيل

<AccordionGroup>

<Accordion title="مثال أكمل">

هذا نوع الملف الذي يكتبه وكيل البرمجة حين يوثّق ميزة جديدة، ويحدّث صفحة ذات صلة، ويضيف الصفحة الجديدة إلى التنقل:

```php
<?php

use LaRecipe\Migrations\DocsMigration;

return new class extends DocsMigration
{
    protected string $set = 'handbook';

    protected string $author = 'claude-code';

    protected string $ref = '';

    protected string $summary = 'Document CSV exports';

    public function up(): void
    {
        $this->createPage('3.x/billing/exports', frontmatter: [
            'title' => 'CSV exports',
            'description' => 'Export invoices as a CSV file from the billing page.',
            'sources' => ['app/Billing/Exports/**'],
        ], body: <<<'MD'
            # CSV exports

            Open **Billing**, then choose **Export as CSV**.
            MD);

        $this->updatePage('3.x/billing/invoices', basedOn: 'sha256:9f3a2c…', frontmatter: [
            'title' => 'Invoices',
            'description' => 'Find, download and export your invoices.',
            'sources' => ['app/Billing/**'],
        ], body: <<<'MD'
            # Invoices

            Every invoice is on the billing page. To get them all at once, export them as CSV.
            MD);

        $this->updateNavigation('3.x', basedOn: 'sha256:51be07…', patch: [
            [
                'add' => 'billing/exports',
                'group' => 'Billing',
                'after' => 'billing/invoices',
            ],
        ]);
    }
};
```

البصمات مختصرة هنا؛ أما البصمة الحقيقية فهي `sha256:` يليها 64 حرفًا ست عشريًا. ومتون الصفحات نصوص nowdoc مُزاحة مع الكود، فيُقرأ فرق الـ pull request كأنه Markdown. ويزيل PHP إزاحة علامة الإغلاق من كل سطر، فتحصل الصفحة على Markdown الذي تراه بالضبط.

</Accordion>

<Accordion title="الصنف وعملياته">

يوسّع `LaRecipe\Migrations\DocsMigration` الصنف `Migration` في Laravel. وأربع خصائص تصف التغيير:

| الخاصية | معناها |
| --- | --- |
| `$set` | مجموعة التوثيق التي تتغيّر. القيمة الفارغة تعني `larecipe.default`. |
| `$author` | من كتب التغيير، شخص أو وكيل مثل `claude-code`. ويراه القرّاء بوصفه آخر تغيير في الصفحة. |
| `$ref` | من أين جاء التغيير، مثل عنوان الـ pull request. القيمة الفارغة تعني اسم ملف الترحيل. |
| `$summary` | سطر واحد لقائمة المراجعات. |

داخل `up()` تستدعي العمليات. وكل منها يأخذ عنوان صفحة: `3.x/billing/exports` في مجموعة ذات إصدارات، و `3.x/ar/billing/exports` لصفحة بلغة أخرى، و `billing/exports` في مجموعة بلا إصدارات.

| العملية | ما تفعله |
| --- | --- |
| `createPage(string $path, array $frontmatter = [], string $body = '')` | تنشئ صفحة. |
| `updatePage(string $path, ?string $basedOn = null, array $frontmatter = [], string $body = '')` | تستبدل ترويسة الصفحة ومتنها. |
| `movePage(string $from, string $to, ?string $basedOn = null)` | تنقل صفحة أو تعيد تسميتها. |
| `deletePage(string $path, ?string $basedOn = null)` | تحذف صفحة. وتبقى في سجل المراجعات. |
| `updateNavigation(string $version = '', ?string $basedOn = null, ?array $tree = null, ?array $patch = null, ?string $locale = null)` | تستبدل تنقل إصدار بشجرة `tree` كاملة، أو تطبّق `patch`: قائمة من عمليات `add` و `move` و `remove` و `rename`. والإصدار الفارغ يعني الإصدار الافتراضي. |
| `updateManifest(?string $basedOn = null, array $patch = [])` | تغيّر `larecipe.json` برقعة دمج JSON (JSON merge patch). |

تمر كل عملية عبر مسار الكتابة نفسه الذي يمر فيه أي تغيير آخر في مجموعة قاعدة البيانات، بوصفها ترحيلًا مع `$author` و `$ref` الخاصين بك. ويُتحقق منها: المحتوى الذي فيه أخطاء تحقق، أو العنوان غير الموجود في المجموعة، يُفشل الترحيل، فيلتقط CI الترحيل المعطّل قبل أن تلتقطه عملية النشر. وتُنشر فورًا: فالـ pull request كان هو المراجعة، في كلا [وضعي المراجعة](../keeping-current/review-modes.md).

</Accordion>

<Accordion title="أين تعيش، وكيف يشغّلها migrate">

تعيش ترحيلات التوثيق في `database/migrations/docs/`، داخل مجلد الترحيلات في تطبيقك لكن منفصلة عن ترحيلات المخطط، فيعرض الـ pull request النوعين منفصلين. غيّر المجلد عبر `migrations.path`:

```php title="config/larecipe.php"
'migrations' => [
    'path' => database_path('migrations/docs'),
    'skip_in_tests' => false,
],
```

حين تستخدم أي مجموعة مصدر قاعدة البيانات، يسجّل LaRecipe هذا المجلد لدى أداة الترحيل في Laravel. وكل عملية نشر تشغّل `php artisan migrate` أصلًا، فلا خطوة جديدة: `migrate:status` و `migrate:rollback` و `migrate --path=database/migrations/docs` تعمل كما مع أي ترحيل. وترحيل التوثيق لمجموعة لم تعد تستخدم مصدر قاعدة البيانات يُتخطّى ويبقى معلّقًا.

</Accordion>

<Accordion title="ملف واحد لكل فرع، يُكتب مع تقدّم الجلسة">

ملف واحد لكل فرع، يحمل اسمه: على `feature/csv-exports` يكون `database/migrations/docs/2026_10_17_141502_docs_feature_csv_exports.php`. والمجموعة غير الافتراضية تضيف مفتاحها: `..._docs_handbook_feature_csv_exports.php`. وخارج أي فرع git، ينتهي الاسم بـ `local`. وتذكر نتيجةُ كل أداة تأليف الملفَّ في `docs_migration`.

يُعاد كتابة الملف مع تقدّم الجلسة، فيحمل الـ pull request ملفًا واحدًا مقروءًا بدلًا من عشرات الملفات. والتغييرات على الصفحة نفسها تُدمج معًا: الإنشاء ثم التحديث يصبحان `createPage` واحدًا، والتحديثان يصبحان `updatePage` واحدًا يحتفظ بأول `basedOn`، والإنشاء ثم الحذف لا يتركان شيئًا (ويختفي الملف حين يصبح فارغًا). ويُسمّى المؤلف باسم عميل MCP، ويجمع الملخّصُ ملخّصَ كل استدعاء في سطر واحد.

يُسجَّل الملف في جدول الترحيلات المحلي لديك على أنه شُغّل من قبل، فلا يطبّقه `php artisan migrate` المحلي مرتين، ويتراجع `php artisan migrate:rollback` المحلي عن الجلسة. وتطلب [المهارتان `larecipe-write-docs` و `larecipe-update-docs`](../keeping-current/skills.md) من الوكلاء أن يضيفوه في commit مع الكود.

</Accordion>

<Accordion title="الأمان من التعارضات بالتفصيل">

`basedOn` هي بصمة المحتوى الذي كُتب التغيير بناءً عليه. تملؤها أدوات الوكيل من قاعدة بياناتك المحلية (يقرأ الوكيل الصفحة بالأداة `read_page`، التي تعيد `content_hash` الخاص بها، ويمرّره بوصفه `based_on`)، ويملؤها `larecipe:make:docs-migration` من المجموعة. وعند النشر، قبل كل تحديث أو نقل أو حذف أو تغيير في التنقل أو ملف التعريف، يقارنها LaRecipe بما هو حي. فإن تغيّر الإنتاج في الأثناء:

- يُحفظ التغيير مراجعةً بالحالة `conflict`، تحمل نسخة الترحيل بجوار نسخة الإنتاج، في أعلى قائمة المراجعة في لوحة الإدارة، حيث يبقي شخصٌ على الإنتاج، أو يأخذ الترحيل، أو يحرّر دمجًا بينهما.
- يُطلق الحدث `DocsMigrationConflicted`.
- يطبع `php artisan migrate` تحذيرًا ويتابع:

```text
WARN LaRecipe docs conflict: [3.x/billing/invoices] The page [billing/invoices] changed since this change was written (expected sha256:9f3a2c…, found sha256:e41b07…). Kept as a conflict draft for review.
```

و `createPage` على مسار فيه صفحة أصلًا تعارضٌ أيضًا. والعملية التي بلا `basedOn` تتخطّى الفحص وتكتب فوق المحتوى، فأبقِ `basedOn` في مكانه.

<Event name="DocsMigrationConflicted" />

</Accordion>

<Accordion title="التراجع">

يشغّل `php artisan migrate:rollback` الدالة `down()` المدمجة. فتمرّ على عمليات الترحيل بالعكس وتعيد كل واحدة بوصفها مراجعة جديدة: الصفحة المستبدلة تستعيد نسختها السابقة، والصفحة المنشأة تُزال، والصفحة المحذوفة تعود.

ولا يدهس التراجع تعديلًا لاحقًا أبدًا أيضًا. فإن غيّر أحدٌ صفحة بعد تشغيل الترحيل، تحتفظ الصفحة بذلك التعديل، وتنتظر نسخة التراجع مسودةَ تعارض، ويبلّغ التراجع عنها. أما التعارض الذي أثاره الترحيل ولم يحلّه أحد فيُرفض.

</Accordion>

<Accordion title="في الاختبارات">

في مجموعة اختبارات تطبيقك، تعمل ترحيلات التوثيق كأي ترحيل آخر؛ فهي عمليات إدراج صغيرة. ولتتخطّاها، اضبط `migrations.skip_in_tests` على `true`: فأثناء تشغيل الاختبارات تُتخطّى ترحيلات التوثيق وتبقى معلّقة.

</Accordion>

</AccordionGroup>

## التالي

- [قاعدة البيانات ولوحة الإدارة](database.md): المراجعات (revisions)، ومراجعة التغييرات، وقائمة المراجعة.
- [التبديل بين المصادر](switching.md): الحزم، والانتقال بين المصادر.
- [خادم MCP](../agents/mcp.md): أدوات التأليف التي يكتب عبرها وكيلك.
