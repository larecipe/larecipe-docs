---
title: التبديل بين المصادر
description: انقل مجموعة التوثيق بين المستودع وقاعدة البيانات في أي وقت، مع تشغيل تجريبي أولًا ومطابقة للبصمات واحدة واحدة بعد ذلك، أو احملها إلى أي مكان على شكل حزمة.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceMoveCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceExportCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/SourceImportCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/MoveReportPrinter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/MoveSourceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ExportSourceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/ImportBundleAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceCopier.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceComparer.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/SourceInventory.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/BundleArchive.php
  - vendor/larecipe/larecipe/src/Domains/Sources/DTOs/Bundle.php
translated_from: sha256:17910afe08889061a4b27d51bb654e22da57bf070076d361ce4986f1dce482f5
---

# التبديل بين المصادر

يمكن أن تعيش مجموعة التوثيق في [المستودع](repository.md) أو في [قاعدة البيانات](database.md)، وكلاهما يحتفظ بالصيغة نفسها: صفحات Markdown مع ترويسة الصفحة، و `larecipe.json`، و `navigation.json`. لذا فالانتقال بينهما نسخٌ مع إثبات. ينسخ `larecipe:source:move` المجموعة من مصدر إلى الآخر، ويفحص كل بصمة محتوى حين تطلب منه ذلك، ويطبع تغيير الإعدادات الذي يبدّل مصدر التقديم. ولا يعدّل إعداداتك بنفسه أبدًا.

> [!IMPORTANT]
> الانتقال إلى قاعدة البيانات يحتاج إلى جداول LaRecipe، ولا تحمّلها الحزمة إلا حين تستخدم مجموعةٌ ما مصدر قاعدة البيانات. انشرها وشغّلها أولًا: `php artisan vendor:publish --tag=larecipe-migrations`، ثم `php artisan migrate`. ويتوقف الأمر مع هذه النصيحة نفسها حين تكون الجداول مفقودة.

<Steps>
<Step title="اطّلع على الخطة">

```bash
php artisan larecipe:source:move repository database --set=docs --dry-run
```

ينسخ التشغيل التجريبي المجموعة إلى حزمة مؤقتة، ويتحقق من كل صفحة، ويطبع عدد الصفحات وملفات التنقل والملفات المرفقة التي ستنتقل، مع أي تحذيرات وأخطاء تحقق. ولا يكتب شيئًا في الوجهة.

</Step>
<Step title="انقل وتحقق">

```bash
php artisan larecipe:source:move repository database --set=docs --verify
```

مع `--verify`، يقرأ الأمر كل شيء من الوجهة مرة أخرى، ويقارن بصمة محتوى كل صفحة وشجرة تنقل وملف تعريف وملف مرفق. ثم يبلّغ أن كل شيء متطابق، أو يذكر كل اختلاف ويخرج بخطأ.

</Step>
<Step title="بدّل مصدر التقديم">

ينتهي الأمر بالتغيير الذي يجب إجراؤه في `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'source' => ['driver' => 'database'],
    ],
],
```

أجرِ التغيير، وانشره، وشغّل `php artisan larecipe:cache:warm` لمجموعة قاعدة البيانات.

</Step>
</Steps>

الاتجاهان هما ترتيبا الوسيطين: `repository database`، أو `database repository`. والانتقال في الاتجاه الآخر يطبع `['driver' => 'repository', 'path' => base_path('docs')]` بوصفه تغيير الإعدادات.

## أين تشغّله

يكتب النقل في الوجهة على الجهاز الذي تشغّله عليه. لتنقل توثيق الإنتاج إلى قاعدة بيانات الإنتاج، شغّله على الإنتاج قبل أن تنشر تغيير الإعدادات. ولتنقل مجموعة قاعدة بيانات إلى مستودعك، حدّث قاعدة بياناتك المحلية أولًا بالأمر [`larecipe:pull`](docs-migrations.md#start-from-what-is-live)، وشغّل النقل محليًا، وأضف الملفات في commit. ومن ذلك الحين تأتي بيانات التحديث (provenance) من git.

## الحزم: التصدير والاستيراد

الحزمة مجموعة توثيق مكتوبة على شكل ملفات قياسية، في مجلد أو في ملف `.tar.gz`، بترتيب مجلد مجموعة المستودع نفسه. استخدمها للنسخ الاحتياطي، أو لتسليم التوثيق إلى تطبيق آخر، أو لإعادة مجموعة كاملة من تعديلات لوحة الإدارة إلى git.

```bash
php artisan larecipe:source:export --set=docs --to=storage/docs.tar.gz
php artisan larecipe:source:import storage/docs.tar.gz --set=docs --verify
```

يتحقق الاستيراد من كل صفحة أولًا، ولا يكتب شيئًا إن كان في أي منها خطأ. وهو كالنقل، يضيف ويحدّث، ولا يحذف أبدًا صفحات موجودة في المجموعة أصلًا. ولتحمل بعض تعديلات لوحة الإدارة فقط عبر pull request، صدّرها ترحيلَ توثيق من [شاشة النشاط](database.md#history-and-activity) في لوحة الإدارة بدلًا من ذلك.

## تفاصيل

<AccordionGroup>

<Accordion title="كل خيارات source:move">

| الخيار | معناه |
| --- | --- |
| `--set` | مجموعة التوثيق. القيمة الافتراضية `larecipe.default`. |
| `--path` | المجلد في جهة المستودع، حين لا يكون المجلد المضبوط في الإعدادات. القيمة الافتراضية `docs/` للمجموعة الافتراضية، ومجلد يحمل اسم المجموعة لغيرها. |
| `--connection` | اتصال قاعدة البيانات في جهة قاعدة البيانات. |
| `--dry-run` | اعرض الخطة ونتيجة التحقق، ولا تكتب شيئًا. |
| `--verify` | بعد الكتابة، اقرأ الوجهة مرة أخرى وقارن كل بصمة محتوى. |

<Artisan command="larecipe:source:move" />

</Accordion>

<Accordion title="ما الذي ينتقل، وبأي ترتيب">

ينسخ النقل بهذا الترتيب:

- **ملف التعريف**، `larecipe.json`.
- **كل صفحة** من كل إصدار ولغة، مع ترويستها ومتنها. والصفحات الأعمق تذهب أولًا، فتصل صفحة المجلد نفسه على شكل `folder/index.md` في المستودع، وتستمر الروابط النسبية في العمل.
- **كل تنقل**، لكل إصدار. ولا يُنسخ تنقل لغة ما إلا حين يختلف عن تنقل اللغة الافتراضية، لأن كلا المصدرين يعود إليه.
- **الملفات المرفقة**: الملفات في `_assets/` وبجوار الصفحات في جهة المستودع، والملفات على قرص المجموعة في جهة قاعدة البيانات.

يُتحقق من كل صفحة قبل كتابة أي شيء. فإن كان في أي صفحة خطأ تحقق، لا يُكتب شيء على الإطلاق. والمحتوى المطابق أصلًا في الوجهة يُتخطّى، فتشغيل النقل نفسه مرتين لا يكتب شيئًا في المرة الثانية. والصفحات الموجودة في الوجهة وغير الموجودة في المصدر تبقى، وتحذّر منها الخطة. وكل تغيير يُسجَّل على أنه من `larecipe:source:move`.

</Accordion>

<Accordion title="ما الذي لا ينتقل">

- **سجل المراجعات.** سجل git وجدول المراجعات ليسا الشيء نفسه، فلا يُحوَّل أحدهما إلى الآخر. وتقول الخطة ذلك في كل مرة: تبدأ الوجهة سجلها الخاص من لحظة النقل.
- **المسودات والتعارضات.** لا ينتقل إلا المحتوى الحي. وتبقى المسودات وتعارضات ترحيلات التوثيق غير المحلولة في جدول المراجعات في قاعدة البيانات.
- **المهارات المكتوبة يدويًا.** مجلد `skills/` في مجموعة المستودع ليس جزءًا من محتوى المجموعة، فلا ينتقل.
- **إعداداتك.** يطبع الأمر التغيير؛ وأنت تجريه.

</Accordion>

<Accordion title="صيغ الحزم، مع تصدير هذا الموقع">

قيمة `--to` مجلد جديد أو فارغ، أو ملف ينتهي بـ `.tar.gz`. وتُحزم الأرشيفات بالصنف `PharData` من PHP نفسه، فلا يحتاج الخادم إلى برنامج `tar`. ويُسجَّل الاستيراد على أنه من `larecipe:source:import`، ويقارن `--verify` كل بصمة محتوى بعده، كما يفعل النقل. ويستخدم [`larecipe:pull`](docs-migrations.md#start-from-what-is-live) الحزم نفسها لتحديث مجموعة قاعدة بيانات محلية من الإنتاج.

تصدير مجموعة التوثيق الخاصة بهذا الموقع، حين كُتبت هذه الصفحة أول مرة، طبع:

```text
Manifest (larecipe.json) ........ yes
Pages ........................... 31
Navigation files ................ 2
Assets .......................... 0

INFO Exported the [docs] set to [storage/docs.tar.gz].
```

يحوي الأرشيف المسارات نفسها الموجودة في مجلد `docs/` في هذا الموقع، مثلًا:

```text
larecipe.json
2.x/index.md
2.x/navigation.json
3.x/index.md
3.x/navigation.json
3.x/agents/surfaces.md
3.x/ar/index.md
3.x/ar/get-started/installation.md
```

</Accordion>

</AccordionGroup>

## التالي

- [مصدر المستودع](repository.md): التوثيق على شكل ملفات، مع بيانات التحديث من git.
- [قاعدة البيانات ولوحة الإدارة](database.md): التوثيق في قاعدة بياناتك، خلف ذاكرة مؤقتة دائمًا.
- [ترحيلات التوثيق](docs-migrations.md): تغييرات المحتوى في مجموعة قاعدة البيانات، ضمن الـ pull request.
