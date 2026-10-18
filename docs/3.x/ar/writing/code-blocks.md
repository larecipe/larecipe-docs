---
title: كتل الكود
description: كود ملوّن بعناوين وأسطر مميّزة وأرقام أسطر وفروق، وأمثلة مضمّنة مباشرة من الكود الخاص بك فلا تبتعد عنه أبدًا.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/CodeBlockRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/DTOs/CodeFence.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/CodeHighlighter.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Support/PassTheme.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/SnippetIncludeStartParser.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/SnippetIncludeParser.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/SnippetResolver.php
  - vendor/larecipe/larecipe/src/Support/SecretFiles.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/MarkdownServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:98728291c2d6527a29db9262f2cde7341adde393235419f5cc2934c65c8f789c
---

# كتل الكود

اكتب كتلة كود مسيّجة مع لغة، فيلوّنها LaRecipe على الخادم، وتكون الألوان في HTML قبل أن يعمل أي JavaScript. وبضع كلمات بعد اللغة تضيف عنوانًا أو تميّز أسطرًا:

````markdown
```php title="app/Http/Controllers/SubscribeController.php" {3}
public function __invoke(Request $request): JsonResponse|RedirectResponse
{
    $data = $request->validate([
```
````

اكتب اللغة دائمًا: فهي تسمّي الكتلة وتخبر الوكلاء بما يقرؤونه. وحين يكون الكود الذي تعرضه موجودًا في تطبيقك، ضمّنه من الملف بدلًا من لصقه، عبر [تضمين مقتطف](#snippet-includes)، فيتغيّر المثال حين يتغيّر الكود.

## العناوين {#titles}

يسمّي `title="..."` الكتلة في رأسها، وعادة باسم الملف الذي جاءت منه. ومن دون عنوان، يعرض الرأس اللغة. والصيغة `[app/Models/Order.php]` اختصار للأمر نفسه.

```php title="config/larecipe.php"
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```

````markdown
```php title="config/larecipe.php"
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```
````

## الأسطر المميّزة {#highlighted-lines}

أرقام الأسطر بين قوسين معقوفين تميّز تلك الأسطر: أسطرًا مفردة ونطاقات، مفصولة بفواصل. استخدمها لتشير إلى السطر الذي تتحدث عنه الفقرة التي فوقها.

```json {4-5}
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/larecipe.json",
  "name": "LaRecipe",
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```

````markdown
```json {4-5}
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/larecipe.json",
  "name": "LaRecipe",
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```
````

## أرقام الأسطر {#line-numbers}

يضيف `showLineNumbers` هامشًا بأرقام الأسطر، للأمثلة الطويلة التي يشير إليها النص برقم السطر.

```yaml showLineNumbers
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources: ["app/Webhooks/**", "routes/webhooks.php"]
---
```

## الفروق {#diffs}

لتعرض تغييرًا، أضف `diff` بعد اللغة وابدأ كل سطر بعلامة، كما في الـ patch: `+` للسطر المضاف، و `-` للسطر المحذوف، ومسافة لبقية الأسطر. يحذف LaRecipe العلامات، ويلوّن الكود بلغته، ويعلّم كل سطر في الهامش.

```php diff
 'sets' => [
     'docs' => [
-        'route' => ['prefix' => 'docs'],
+        'route' => ['prefix' => ''],
     ],
 ],
```

````markdown
```php diff
 'sets' => [
     'docs' => [
-        'route' => ['prefix' => 'docs'],
+        'route' => ['prefix' => ''],
     ],
 ],
```
````

تجتمع أجزاء السياج بأي ترتيب بعد اللغة: ` ```php title="routes/web.php" {2} showLineNumbers `.

## مجموعات الكود {#code-groups}

ضع عدة كتل كود داخل `CodeGroup` لتعرض بدائل على شكل تبويبات فوق لوحة واحدة. ويصبح عنوان كل كتلة، أو لغتها، تسمية التبويب. راجع [المكوّنات](components.md#codegroup) لمثال حي.

## تضمين المقتطفات {#snippet-includes}

أمثلة الكود التي تصف تطبيقك يجب أن تأتي من تطبيقك. وتضمين المقتطف سطر يبدأ بـ `<<<` ويشير إلى ملف، بدءًا من جذر مشروعك:

```markdown
<<< @/resources/larecipe/extension.tsx {8}
```

يقرأ LaRecipe الملف حين يُصيّر الصفحة ويحوّله إلى كتلة كود، عنوانها مساره، بلغة يحدّدها امتداده. وهذا المثال حي: إنه ملف الإضافة الرئيسي لهذا الموقع، الملف الذي يسجّل نموذج النشرة البريدية في أسفل الصفحة.

<<< @/resources/larecipe/extension.tsx {8}

كل ما يأتي بعد المسار يعمل كما في السياج: تمييز الأسطر، و `showLineNumbers`، و `diff`، أو `title="..."` ليحلّ محلّ المسار في الرأس. وأرقام الأسطر المميّزة تُعدّ من أعلى الكود المضمَّن، لا من أعلى الملف. وحين يتغيّر الملف، تُصيَّر الصفحة من جديد عند الطلب التالي.

> [!TIP]
> اذكر الملف الذي تضمّنه في `sources:` الخاصة بالصفحة أيضًا. عندها يسمّي `php artisan larecipe:affected` الصفحةَ حين يتغيّر ذلك الملف، ويعيد وكيلك قراءة النص المحيط بالمثال، لا المثال وحده.

### تضمين منطقة واحدة {#include-one-region}

معظم الملفات أطول من أن تُعرض كاملة. علّم الجزء الذي تريده بتعليق `#region`، وأعطه اسمًا، وضمّن تلك المنطقة وحدها:

```php title="app/Billing/Invoice.php"
public function refund(int $amount): Refund
{
    // #region refund
    $refund = $this->payments->refund($this->payment_id, $amount);

    $this->refunds()->save($refund);
    // #endregion

    return $refund;
}
```

```markdown
<<< @/app/Billing/Invoice.php#refund
```

الكود المضمَّن هو الأسطر الواقعة بين `#region refund` و `#endregion` الخاص بها، مع حذف المسافة البادئة المشتركة. ويمكن للمناطق أن تتداخل، والعلامة تعمل في أي نمط تعليق: `//` و `#` و `/* */` و `<!-- -->` و `{{-- --}}` و `--` و `;`. وحين تضمّن ملفًا كاملًا، يحذف LaRecipe علامات المناطق.

### حين يكون المقتطف مفقودًا {#when-a-snippet-is-missing}

الملف المنقول أو المنطقة التي تغيّر اسمها لا تفشل بصمت. تعرض الصفحة تحذير "Snippet not found" مكان الكود، ويبلّغ `php artisan larecipe:validate` عن خطأ مع رقم السطر، فيوقف المثالُ القديم عمليةَ CI لديك بدلًا من أن يصل إلى القارئ.

<Accordion title="قواعد المقتطفات التي يبلّغ عنها larecipe:validate">

| القاعدة | متى |
| --- | --- |
| `snippets.missing` | الملف، أو المنطقة داخله، غير موجود. |
| `snippets.denied` | المسار يخرج من جذر المقتطفات، أو يشير إلى ملف قد يحوي أسرارًا. |
| `snippets.disabled` | ليس لمجموعة التوثيق جذر مقتطفات. |

الصفحة التي يحمل تصييرها خطأً كهذه لا تُخزَّن مؤقتًا، فيظهر الإصلاح عند الطلب التالي.

</Accordion>

### ما الذي يمكن تضمينه {#what-can-be-included}

`@/` هو جذر المقتطفات: المسار الأساسي لمشروعك في مجموعة توثيق المستودع. وبعض الملفات لا تُضمَّن أبدًا، حتى داخل الجذر، كي لا يصبح التوثيق بابًا خلفيًا إلى إعداداتك: الملفات النقطية مثل `.env`، و `config/`، و `storage/`، و `vendor/`، والمفاتيح، وبيانات الاعتماد، وقواعد البيانات، والسجلات.

<Accordion title="جذر المقتطفات والقائمة الكاملة للملفات المرفوضة">

اضبط `snippets.root` في `config/larecipe.php` لتحصر الجذر في مجلد واحد، أو `sets.{key}.snippets.root` لمجموعة توثيق واحدة. ولا تضمّن مجموعة توثيق قاعدة البيانات شيئًا ما لم تضبط مجموعتها جذرًا، لأن محتواها قد يأتي من أشخاص خارج الكود.

هذه الملفات مرفوضة حتى داخل الجذر. وهي القائمة نفسها التي يُبعد عنها [`larecipe:sync`](../keeping-current/sync.md) وكيلَه:

- الملفات النقطية وكل ما في مجلد يبدأ اسمه بنقطة (`.env` و `.git/` و `.ssh/`)،
- `config/` و `bootstrap/cache/` و `storage/` و `vendor/` و `node_modules/`،
- الملف `auth.json` الخاص بـ Composer وملفات PHPUnit (`phpunit.xml` و `phpunit.xml.dist`)، لأنها تضبط قيمًا للبيئة،
- المفاتيح والشهادات ومخازن المفاتيح (`.key` و `.pem` و `.p8` و `.p12` و `.pfx` و `.ppk` و `.crt` و `.cer` و `.der` و `.jks` و `.keystore` و `.kdbx`)، ومفاتيح SSH (`id_rsa`)، وملفات المفاتيح أو الأسرار التي بلا امتداد (`deploy_key`)،
- ملفات بيانات الاعتماد (`firebase-credentials.json`)، وقواعد البيانات (`.sqlite` و `.sqlite3` و `.db`)، والنسخ المفرَّغة والسجلات (`.sql` و `.dump` و `.log`)، والملفات التي تنتهي بـ `.env`، وملفات JSON المحفوظة تحت `database/` أو `backups/` أو `dumps/`.

ويُرفض المسار الذي يحوي `..` ويخرج من الجذر، وكذلك الرابط الرمزي الذي يقع ملفه الحقيقي خارجه.

</Accordion>

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="أي اللغات تُلوَّن">

اللغة هي الكلمة الأولى في السياج. يلوّنها LaRecipe بمكتبة Phiki، التي تقرأ قواعد TextMate نفسها التي يقرؤها VS Code، فتُغطّى `php` و `blade` و `bash` و `json` و `yaml` و `tsx` و `sql` و `diff` واللغات الأخرى التي تستخدمها يوميًا. وتُلوَّن `env` على أنها ملف dotenv، و `terminal` و `cli` على أنهما shell.

وتعني `text` و `txt` و `plain` و `plaintext` و `none` و `output` عدم التلوين. واللغة التي لا تعرفها Phiki تعود إلى النص العادي، فلا يكسر خطأ إملائي في السياج أي صفحة أبدًا.

يوضع الكود على لوح رمادي داكن في الوضعين الفاتح والداكن، بمظهر LaRecipe الخاص `larecipe-pass`. والتلوين أبطأ أجزاء التصيير، لذا تُخزَّن مخرجات كل كتلة مؤقتًا وحدها: تعديل فقرة يعيد تصيير الصفحة، بينما تأتي كل كتلة كود لم تتغيّر من الذاكرة المؤقتة.

</Accordion>

<Accordion title="الـ patches بسياج diff">

تحتفظ كتلة ` ```diff ` بعلامتي `+` و `-`، لأنهما جزء من الصيغة، وتعلّم الأسطر بالطريقة نفسها. استخدمها للـ patches الحقيقية، واستخدم `diff` بعد اسم لغة لعرض تغيير مقروء.

</Accordion>

<Accordion title="النسخ، وما يقرؤه الوكلاء">

تضيف واجهة القراءة زر نسخ إلى كل كتلة، والنسخ يعطيك الكود من دون أرقام الأسطر ولا علامات الفروق. وفي نسخة Markdown الخاصة بالوكلاء، تبقى كل كتلة كتلة كود مسيّجة بلغتها وعنوانها، فيقرأ الوكيل الكود نفسه الذي ينسخه الشخص.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [التنقل](navigation.md): الشريط الجانبي، من المجلدات أو من `navigation.json`.
- [المكوّنات](components.md): الخطوات والتبويبات والبطاقات وغيرها.
- [المراجع الحية](live-references.md): المسارات والأوامر والنماذج والأحداث مقروءة من تطبيقك، للحقائق التي لا ينبغي لصقها أبدًا.
