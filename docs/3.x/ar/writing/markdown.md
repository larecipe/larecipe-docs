---
title: Markdown
description: اكتب الصفحات بصيغة Markdown بنكهة GitHub، واربط بينها بمسارات .md نسبية، وأضف التنبيهات والمكوّنات حيث لا تكفي Markdown وحدها.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/MarkdownEnvironmentFactory.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentProcessor.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/Slugger.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageSanitizer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Enums/HtmlInput.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/MarkdownServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/**
  - vendor/larecipe/larecipe/src/Domains/Content/Services/FrontmatterParser.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Page.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsLinkResolver.php
translated_from: sha256:a0f672c82395239715b7c87374b2d9c316307e271eed99c0b792ab59530cdd88
---

# Markdown

الصفحة ملف Markdown تعلوه كتلة قصيرة بصيغة YAML هي ترويسة الصفحة (frontmatter). اكتب Markdown بنكهة GitHub التي تعرفها، واربط بالصفحات الأخرى بمسارات `.md` نسبية، فيُقرأ الملف نفسه جيدًا على GitHub وعلى موقعك.

```markdown
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources: ["app/Webhooks/**", "routes/webhooks.php"]
---

# Webhooks

Every event is signed. Verify the signature before you trust the payload.
See [installation](../get-started/installation.md) for the setup.
```

تشرح صفحة [بنية المشروع](../get-started/project-structure.md) المفاتيح الثلاثة التي تحتاجها كل صفحة في ترويستها، وأين يوضع الملف.

## الروابط {#links}

اربط بالصفحات الأخرى بمسارات `.md` نسبية، كما تفعل على GitHub:

```markdown
See [webhook retries](./webhooks.md#retries), or go back to [installation](../get-started/installation.md).
```

يحوّل LaRecipe كل رابط إلى رابط القارئ في الإصدار واللغة نفسيهما. ويبلّغ `php artisan larecipe:validate` عن الروابط التي تشير إلى صفحات أو مراسٍ غير موجودة، ويقترح أقرب تطابق إصلاحًا لها.

## العناوين والمراسي {#headings-and-anchors}

يحصل كل عنوان على معرّف (id): نصّه بأحرف صغيرة، مع حذف علامات الترقيم وتحويل المسافات إلى شرطات. فالعنوان `## Retry failed deliveries` يصبح `#retry-failed-deliveries`. ولكي يبقى المعرّف ثابتًا حين تعيد صياغة العنوان، حدّده بنفسك:

```markdown
## Retry failed deliveries {#retries}
```

تبني عناوين H2 و H3 جدول المحتويات، وتقسم الصفحة إلى سجلات بحث، فاجعل لكل قسم عنوانًا خاصًا به بدلًا من سطر بخط عريض.

## الصور والملفات {#images-and-files}

ضع الصور بجوار الصفحة، أو في مجلد `_assets` الخاص بالإصدار، واربط بها بمسار نسبي:

```markdown
![The review queue](./_assets/review-queue.png)
```

ولتضيف تعليقًا تحت الصورة، ضعها داخل [Frame](components.md#frame).

## التنبيهات {#alerts}

تُصيَّر صيغة التنبيهات في GitHub على شكل صندوق تنبيه (callout). وهي أكثر طرق كتابته قابلية للنقل: يعرضها GitHub، ويقرؤها الوكلاء، وينسّقها LaRecipe.

> [!TIP] فضّل التنبيهات في الحالات البسيطة
> تُقرأ بالشكل نفسه في محرّرك، وعلى GitHub، وعلى موقعك.

```markdown
> [!TIP] Prefer alerts for simple callouts
> They read the same in your editor, on GitHub and on your site.
```

الأنواع هي `NOTE` و `TIP` و `IMPORTANT` و `WARNING` و `CAUTION`. والنص الذي يلي العلامة في السطر الأول يصبح العنوان.

## المكوّنات {#components}

لما لا تستطيع Markdown قوله وحدها (الخطوات، والتبويبات، والبطاقات، والفيديو)، اكتب مكوّنًا على شكل وسم بصيغة PascalCase في سطر مستقل، مع سطر فارغ قبله وبعده:

```markdown
<Callout type="warning" title="Before you deploy">
Run `php artisan optimize` so the first reader never waits.
</Callout>

<Badge tone="success">Stable</Badge>
```

المحتوى داخل المكوّن Markdown. والخصائص (props) نصوص بين علامتي تنصيص. والوسم غير المعروف يُصيَّر نصًا مهرّبًا ويُفشل `larecipe:validate`. وتعرض [صفحة المكوّنات](components.md) كل مكوّن مدمج، بأمثلة حية.

### التوجيهات {#directives}

لكل مكوّن أيضًا صيغة توجيه (directive) تبدأ بـ `:::`. والتوجيه والوسم ينتجان الصفحة نفسها.

<Accordion title="اكتب المكوّنات على شكل توجيهات">

```markdown
:::warning Before you deploy
Run `php artisan optimize` so the first reader never waits.
:::

::::tabs {sync=package-manager}
:::tab npm
`npm install`
:::
:::tab pnpm
`pnpm install`
:::
::::
```

- `:::note` و `:::tip` و `:::important` و `:::warning` و `:::caution` و `:::danger` صناديق تنبيه. وأي اسم آخر مكوّن: `:::code-group` هو `CodeGroup`.
- النص الذي يلي الاسم يملأ الخاصية `title` (و `label` في التبويب). والخصائص الأخرى توضع بين قوسين معقوفين: `{type=warning}`.
- لتداخل التوجيهات، أعطِ التوجيه الخارجي سياجًا أطول (`::::`). وسياج الإغلاق يغلق أعمق توجيه يستطيع إغلاقه.

</Accordion>

## HTML الخام {#raw-html}

يُهرَّب HTML الخام: فالوسم `<div>` أو `<details>` في الصفحة يظهر نصًا، ولا يُعرض وسومًا أبدًا. استخدم مكوّنًا بدلًا منه ([Accordion](components.md#accordion-and-accordiongroup) بدلًا من `<details>`، و[Frame](components.md#frame) بدلًا من figure)، أو [ابنِ مكوّنك الخاص](../customize/custom-components.md).

<Accordion title="اسمح بـ HTML الخام في مجموعة مستودع">

يمكن لمجموعة مستودع تحتاج صفحاتها إلى HTML خام أن تسمح به عبر `html_input` في مدخلها داخل `config/larecipe.php`:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // route, source and access as usual
        'html_input' => 'allow',
    ],
],
```

عندئذ يمرّ HTML الخام كما كُتب، ما عدا الوسوم التي يرشّحها GitHub، مثل `<script>` و `<iframe>` و `<style>`، فتبقى مهرّبة. وصفحات هذه المجموعة تُراجَع في git مع الكود، فالقرار لك.

أما مجموعة قاعدة البيانات فتهرّب HTML الخام دائمًا، وتمرّر كل صفحة أيضًا عبر منقّح HTML، فلا أثر لـ `html_input` فيها. راجع [مصدر قاعدة البيانات](../sources/database.md).

كان LaRecipe 2 يُصرّف Blade داخل Markdown. أما الإصدار 3 فلا ينفّذ Blade ولا PHP في أي صفحة أبدًا، مهما كان المصدر.

</Accordion>

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="كل ميزات Markdown">

يحلّل LaRecipe صيغة Markdown بمكتبة league/commonmark، مع هذه الإضافات في كل صفحة:

| الميزة | كيف تكتبها |
| --- | --- |
| الجداول، وقوائم المهام، والنص المشطوب، والروابط التلقائية | Markdown بنكهة GitHub، كما على GitHub |
| الحواشي | `A claim.[^1]` ثم لاحقًا `[^1]: The source.` |
| قوائم التعريف | المصطلح في سطر، ثم `: its definition` في السطر التالي |
| كتل الكود | كود مسيّج مع لغة. راجع [كتل الكود](code-blocks.md) |
| الرياضيات | كتلة `$$`، تُصيَّر معادلةً. راجع [المكوّنات](components.md#math) |
| المخططات | سياج ` ```mermaid `. راجع [المكوّنات](components.md#mermaid) |

</Accordion>

<Accordion title="العناوين وأول عنوان في الصفحة">

يأتي عنوان الصفحة من `title` في ترويسة الصفحة. ومن دونه يأخذ LaRecipe أول `# Heading` في المتن، وإن لم يجده، يصنع عنوانًا من اسم الملف (فيصبح `index` هو "Introduction").

يعرض رأس الصفحة العنوان أصلًا، فلا يتكرر عنوان `# Heading` الأول داخل المقالة. ومع ذلك أبقِه: فبه يُقرأ الملف جيدًا على GitHub وفي محرّرك.

وإن لم تكن ترويسة الصفحة YAML صالحة، تظل الصفحة تُصيَّر، بترويسة فارغة، ويخبرك `php artisan larecipe:validate` بما عليك إصلاحه.

</Accordion>

<Accordion title="كيف تُصنع معرّفات العناوين">

تحتفظ العربية والكتابات الأخرى بحروفها في المعرّف. وحين يتشارك عنوانان المعرّف نفسه، يحصل الثاني على `-1`، والثالث على `-2`. والسمتان `{#id}` و `{.class}` هما الوحيدتان اللتان يقبلهما LaRecipe، على العناوين والكتل الأخرى. وأي سمة أخرى، مثل `onclick`، تُتجاهل.

</Accordion>

<Accordion title="كيف تُحلّ الروابط">

- الرابط النسبي يُحلّ من مجلد الصفحة التي تحويه.
- الرابط الذي يبدأ بـ `/` وينتهي بـ `.md` يُحلّ من جذر الإصدار الحالي: فالرابط `[install](/get-started/installation.md)` يصل إلى الصفحة نفسها من أي مكان في الإصدار.
- الرابط الجذري الذي يذكر إصدارًا صراحة، بعد بادئة المجموعة ولغة اختيارية، يصل إلى تلك الصفحة: `/3.x/index.md` هي الصفحة الرئيسية للإصدار 3.x، و `/ar/3.x/index.md` هي نسختها العربية. ويتحقق `larecipe:validate` من هذه الروابط بالطريقة نفسها.
- الروابط التي تحمل مخططًا (`https://` و `mailto:`) والمراسي `#anchors` تُترك كما هي. ولتربط برابط ينتهي بـ `.md` وليس من صفحاتك، اكتب الرابط كاملًا.
- تُحذف روابط `javascript:` وغيرها من الروابط غير الآمنة.
- في نسخة Markdown التي يقرؤها الوكلاء، تشير الروابط إلى الصفحات الأخرى إلى نسخ `.md` المقابلة لها، بروابط مطلقة.

يمكن للمحتوى المكتوب لـ LaRecipe 2 أن يحتفظ بـ `{{version}}` في الرابط: إذ يُستبدل بالإصدار الحالي، في الروابط وفي خصائص المكوّنات مثل `href` و `src`. أما الصفحات الجديدة فلتستخدم الروابط النسبية.

</Accordion>

<Accordion title="أين يبحث LaRecipe عن الصور">

يحلّ LaRecipe مسار الصورة من مجلد الصفحة، ويبحث عن الملف في مجلد الإصدار، ثم في مجلد `_assets` الخاص بالإصدار، ثم في مجلد `_assets` في جذر التوثيق. ويقدّم الملف من `/_assets/{version}/...` تحت بادئة المجموعة. والمسار الذي يبدأ بـ `/` يُحلّ من جذر الإصدار بالطريقة نفسها. أما الصورة الموجودة في أي مكان آخر، مثل مجلد `public/` في تطبيقك أو شبكة CDN، فاكتب رابطها كاملًا مع المخطط.

</Accordion>

<Accordion title="صيغة المكوّنات كاملة">

- المكوّن الذي له محتوى يُفتح بـ `<Name>` ويُغلق بـ `</Name>`. ومن دون محتوى يغلق نفسه: `<Name prop="value" />`. والمكوّن ذو المحتوى القصير يمكن أن يأتي في سطر واحد، مثل الشارة.
- تعمل القوائم وكتل الكود والمكوّنات الأخرى داخل المكوّن.
- الخاصية المجرّدة (`open`) تعني true، و `prop="false"` تعني false، و JSON يوضع بين قوسين معقوفين: `cols={3}`. ولا توجد تعبيرات JSX: لا ينفّذ LaRecipe أبدًا كودًا من توثيقك.
- المكوّنات غير المعروفة والخصائص الناقصة أو غير الصالحة تُفشل `php artisan larecipe:validate`.

</Accordion>

<Accordion title="ملاحظات للكاتب التالي">

تُحذف تعليقات HTML من الصفحة، ومن نسخة Markdown المقابلة لها، ومن البحث، فيبقى `<!-- a note for the next writer -->` في المصدر وحده. أما داخل الكود المضمّن في السطر وكتل الكود، فتبقى التعليقات كما كُتبت.

</Accordion>

</AccordionGroup>

يسحب سطر مثل `<<< @/app/Billing/Invoice.php#refund` الكود مباشرة من تطبيقك إلى كتلة كود، فلا يمكن للمثال أن يبتعد عن الكود: راجع [كتل الكود](code-blocks.md#snippet-includes). ولكل صفحة أيضًا نسخة Markdown للوكلاء، تُصيَّر من الصفحة المحلَّلة نفسها: راجع [واجهات الوكلاء](../agents/surfaces.md).

## التالي {#next}

- [المكوّنات](components.md): كل مكوّن مدمج، مع مثال حي.
- [كتل الكود](code-blocks.md): العناوين، وتمييز الأسطر، والفروق، وتضمين المقتطفات.
- [مرجع ترويسة الصفحة](../reference/frontmatter.md): كل مفتاح يمكن للصفحة أن تحدّده.
