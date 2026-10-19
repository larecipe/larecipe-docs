---
title: مصدر المستودع
description: المصدر الافتراضي. مجموعة توثيق على شكل ملفات Markdown في مستودع تطبيقك نفسه، تتغيّر عبر طلبات الـ pull request، ويُقرأ آخر تغيير في كل صفحة من git.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/SourceManager.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/GitProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Laravel/ProvenanceIndexPath.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/GitLogParser.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/MtimeProvenanceResolver.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Services/ProvenanceIndexWriter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Actions/CompileProvenanceAction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/ProvenanceCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/AuthorSession.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - config/larecipe.php
translated_from: sha256:81e88e3d1956b9aa96e72863b5cc6d1a0ddc89373bf6d6544476ae7701e25c99
---

# مصدر المستودع

يحفظ مصدر المستودع مجموعة التوثيق ملفاتٍ عادية في مستودع تطبيقك نفسه: صفحات Markdown، و `larecipe.json`، و `navigation.json`. وهو المصدر الافتراضي، والمصدر الذي نوصي به. إن ثبّتّ LaRecipe من دون `--driver=database`، فأنت تستخدمه أصلًا ولا شيء عليك إعداده.

يتغيّر التوثيق في الـ pull request نفسه مع الكود، وتجري المراجعة حيث تجري مراجعة الكود أصلًا، وتعرض كل صفحة من غيّرها آخر مرة، مقروءًا من git. أما ترتيب المجلدات وكيف تصبح الملفات عناوين URL ففي صفحة [بنية المشروع](../get-started/project-structure.md).

يستخدمه هذا الموقع، وتوثيقه في `docs/`:

```php title="config/larecipe.php"
'source' => [
    'driver' => 'repository',
    'path' => base_path('docs'),
],
```

من دون `source.path`، تقرأ المجموعة المجلد الذي يحمل اسم مفتاحها في جذر تطبيقك، فمجموعة `handbook` تقرأ `handbook/`.

## التغييرات ملفات

كل تغيير في مجموعة مستودع هو تغيير في ملف ضمن شجرة العمل لديك. سواء عدّل وكيل البرمجة ملف Markdown أو كتب عبر أدوات التأليف في خادم MCP المحلي، يصبح التغيير جزءًا من الـ commit التالي ومن الـ pull request. لا مسودات ولا خطوة نشر: الـ pull request هو المراجعة. ولا يقرّر [وضع المراجعة](../keeping-current/review-modes.md) إلا إن كان الوكيل يعرض عليك الفروق قبل أن ينفّذ الـ commit.

نقطة نهاية MCP على الويب لا تكتب أبدًا في مجموعة مستودع، حتى مع تفعيل `mcp.web.author_tools`. فمكان تغييراتها هو git.

<Accordion title="كيف تبقى عمليات الكتابة آمنة">

- لا يمكن لعملية كتابة أن تغادر مجلد المجموعة أبدًا. والمسار الذي يحاول ذلك يُرفض.
- الصفحة الجديدة التي يكون مسارها مجلدًا موجودًا أصلًا تصبح `index.md` لذلك المجلد، فتستمر الروابط النسبية داخل المجلد في العمل.
- يحتفظ `navigation.json` بمؤشّر `$schema` الخاص به، ويُكتب JSON بإزاحة من مسافتين وسطر جديد في النهاية، كما ينسّقه الناس.
- أدوات التأليف المحلية هي `write_page` و `move_page` و `delete_page` و `update_navigation` و `update_manifest`. راجع [خادم MCP](../agents/mcp.md).

</Accordion>

## بيانات التحديث (provenance) من git {#provenance-from-git}

تستطيع كل صفحة أن تعرض آخر تغيير فيها، مثل «حُدّث منذ 3 أيام بواسطة Claude»، مع الـ pull request أو الـ commit الذي جاء فيه. وفي مجموعة المستودع، يأتي ذلك من git، ويُجمَّع مرة واحدة أثناء البناء:

```bash
php artisan larecipe:provenance
```

يشغّل أمر `git log` واحدًا على مجلد المجموعة، ويحتفظ بأحدث commit لكل ملف، ويكتب النتيجة في `bootstrap/cache/larecipe/provenance-{set}.php`، فلا يشغّل تقديم الصفحة git أبدًا. شغّله حيث يكون السجل الكامل متاحًا: النسخة السطحية (shallow clone) تعطي كل الصفحات آخر commit نفسه. ولا تحتاج خوادم الإنتاج إلى git. وتبيّن صفحة [النشر](../operate/deployment.md) أين توضع هذه الخطوة.

لتخفي سطر آخر تغيير من كل الصفحات، اضبط `"provenance": false` في `larecipe.json`.

<AccordionGroup>

<Accordion title="ما الذي يقرؤه من كل commit">

- **متى**: تاريخ الـ commit.
- **من**: مؤلف الـ commit.
- **أي وكيل**: سطر `Co-Authored-By` في ذيل الرسالة يسمّي وكيل برمجة (Claude، Codex، Copilot، Cursor، Gemini أو Jules، Devin، Aider، Windsurf) يجعل التغيير مكتوبًا بيد ذلك الوكيل، بالاسم الوارد في السطر، ويُسجَّل مؤلف الـ commit بوصفه الشخص الذي اعتمده. وحساب البوت الخاص بالوكيل نفسه حين يكون مؤلف الـ commit يُحتسب أيضًا.
- **أي pull request**: `(#482)` في نهاية عنوان الـ commit، كما يكتبه دمج squash، أو `Merge pull request #482`. ومع وجود `repository.url` في `larecipe.json` (أو `repository.docsUrl`، حين يعيش التوثيق في مستودع خاص به)، يصبح المرجع عنوان الـ pull request. ومن دون pull request، تكون بصمة الـ commit هي المرجع.

مثلًا، commit مدموج بطريقة squash مثل هذا:

```text
Document the --agents flag on larecipe:install (#482)

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
```

يمنح كل صفحة لمسها تغييرًا من الوكيل `Claude Opus 5.5`، اعتمده مؤلف الـ commit، من الـ pull request رقم 482. ولهذا ينبغي لوكلاء البرمجة أن يحتفظوا بأسطر `Co-Authored-By`: فهي ما يخبر قرّاءك أن وكيلًا كتب التغيير.

</Accordion>

<Accordion title="من دون git">

يعود LaRecipe إلى أوقات تعديل الملفات حين لا يكون git مثبّتًا، وحين لا يكون المجلد داخل شجرة عمل git، وللملفات التي لم تُضف في أي commit. فتعرض الصفحة عندها متى تغيّرت، من دون اسم. والمجموعة التي لا ملف مجمَّعًا لها على الإطلاق تفعل الشيء نفسه، من ملف كل صفحة.

</Accordion>

<Accordion title="كل الخيارات">

من دون `--set`، يجمع الأمر كل مجموعات المستودع؛ ويزيل `--clear` الملفات المجمَّعة. ولا يجمع `larecipe:optimize` بيانات التحديث، لذا يشغّل النشر كليهما.

<Artisan command="larecipe:provenance" />

</Accordion>

</AccordionGroup>

## تفاصيل

<AccordionGroup>

<Accordion title="البصمات: لماذا لا شيء تفرّغه بعد النشر">

لكل إصدار من مجموعة المستودع بصمة: بصمة قصيرة لمسار كل ملف `.md` و `.json` و `.css` في مجلد الإصدار ووقت تعديله وحجمه، إضافة إلى وقت تعديل `larecipe.json`. وتُخزَّن تحتها قوائم الصفحات والتنقل وسجلات البحث المخزّنة مؤقتًا، فتغيير أي ملف يعيد بناءها في الطلب التالي. والصفحات المُصيَّرة مفتاحها محتواها نفسه فوق ذلك. راجع [التخزين المؤقت](../operate/caching.md).

</Accordion>

<Accordion title="عدة مجموعات في مجلدات مختلفة">

تشير كل مجموعة إلى مجلدها الخاص، مع `larecipe.json` وإصدارات وتنقل وملف بيانات تحديث خاصة بها. وتقدّم المجموعة كل إصداراتها من مصدر واحد. والأوامر التي تقبل `--set` تعمل على مجموعة واحدة، و `larecipe:provenance` من دونه يجمعها كلها. وتبيّن صفحة [أساسيات الإعداد](../get-started/configuration.md) كيف تضيف مجموعة ثانية.

</Accordion>

</AccordionGroup>

## التالي

- [بنية المشروع](../get-started/project-structure.md): ترتيب المجلدات وكيف تصبح الملفات عناوين URL.
- [النشر](../operate/deployment.md): أين يعمل `larecipe:provenance`.
- [مصدر قاعدة البيانات](database.md): تعمّق، للفرق التي تدير التوثيق من لوحة إدارة.
