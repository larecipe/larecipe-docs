---
title: بنية المشروع
description: ما الذي يعيش في مجلد التوثيق، وكيف تضيف صفحة، وكيف يصبح مسار كل ملف رابطًا، والمفاتيح الثلاثة في ترويسة الصفحة التي تبدأ بها كل صفحة.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageRef.php
  - vendor/larecipe/larecipe/src/Domains/Content/Services/SetRegistry.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/SkillLibrary.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/MakePageCommand.php
  - docs/larecipe.json
translated_from: sha256:d6f1e69c6d35c7e471c1c8b211441a833a8f78b745bf13238e3fb136f3141a4d
---

# بنية المشروع

توثيقك مجلد من ملفات Markdown، هو `docs/` في جذر تطبيقك. ومسار كل ملف هو رابط صفحته: يُقدَّم `docs/billing/refunds.md` على `/docs/billing/refunds`.

لتضيف صفحة، أنشئ الملف، أو دع LaRecipe يكتبه لك مع ترويسة الصفحة مملوءة:

```bash
php artisan larecipe:make:page billing/refunds --description="Refund a payment in full or in part." --source="app/Billing/**"
```

تبدأ كل صفحة بثلاثة مفاتيح في ترويسة الصفحة (frontmatter):

```markdown
---
title: Refunds
description: Refund a payment in full or in part.
sources:
  - app/Billing/**
---

# Refunds

Answer first: what this page is about, and when you need it.
```

- يحدّد `title` اسم الصفحة في رأسها، وفي الشريط الجانبي، والبحث، و `llms.txt`. ومن دونه يستخدم LaRecipe أول `# Heading`.
- `description` جملة واحدة. تظهر تحت العنوان، وفي نتائج البحث، و `llms.txt`، وأداة MCP `list_pages`.
- يسرد `sources` الكود الذي تشرحه الصفحة، مسارات أو أنماطًا تبدأ من جذر مشروعك. وبه يعرف LaRecipe أن الصفحة صارت قديمة حين يتغيّر ذلك الكود. راجع [كيف يعمل](../keeping-current/how-it-works.md).

ويسرد [مرجع ترويسة الصفحة](../reference/frontmatter.md) كل مفتاح آخر، مثل `order` و `hidden` و `visibility`.

## ما الذي يعيش في المجلد {#what-lives-in-the-folder}

مجلد `docs/` الخاص بهذا الموقع، مختصرًا:

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
      - your-first-docs.md
    - writing/
      - markdown.md
    - ar/
      - index.md
      - get-started/
        - installation.md
  - 2.x/
    - navigation.json
    - index.md

</FileTree>

| الاسم | ما هو |
| --- | --- |
| `larecipe.json` | [ملف التعريف](../reference/manifest.md) الخاص بالمجموعة: اسمها، وشعارها، ومظهرها، وإصداراتها، ولغاتها. اختياري. |
| `3.x/` و `2.x/` | مجلد لكل [إصدار](../writing/versions.md). والمجموعة التي لا إصدارات لها تحفظ صفحاتها مباشرة في `docs/`. |
| `navigation.json` | الشريط الجانبي للإصدار. اختياري: من دونه يُبنى الشريط الجانبي من مجلداتك. راجع [التنقل](../writing/navigation.md). |
| `index.md` | صفحة المجلد نفسه: الصفحة الرئيسية في جذر الإصدار، ونظرة عامة على القسم في المجلدات الفرعية. |
| `ar/` | [لغة](../writing/languages.md): مجلد لكل رمز لغة، بالمسارات نفسها التي في الأصل. |
| `_assets/` | الصور والملفات الأخرى التي تشير إليها الصفحات، في مجلد إصدار أو في جذر المجموعة. |

## من الملف إلى الرابط {#from-file-to-url}

رابط الصفحة هو مسارها داخل الإصدار، من دون `.md`، تحت بادئة المجموعة واللغة والإصدار:

```text
/{prefix}/{locale}/{version}/{path}
```

هذا الموقع مركّب على جذر النطاق، والإنجليزية لغته الافتراضية، فلا تحمل معظم روابطه إلا الإصدار والمسار:

| الملف | رابط الصفحة | نسخة Markdown |
| --- | --- | --- |
| `docs/3.x/index.md` | `/3.x` (و `/`، الصفحة الأولى للموقع) | `/3.x/index.md` |
| `docs/3.x/get-started/installation.md` | `/3.x/get-started/installation` | `/3.x/get-started/installation.md` |
| `docs/3.x/ar/get-started/installation.md` | `/ar/3.x/get-started/installation` | `/ar/3.x/get-started/installation.md` |

ومع البادئة الافتراضية، تعيش الصفحات نفسها تحت `/docs`: `/docs/3.x/get-started/installation`. والروابط بين الصفحات تستخدم مسارات الملفات لا الروابط: اكتب `[installation](../get-started/installation.md)` وسيحوّله LaRecipe إلى الرابط الصحيح لإصدار القارئ ولغته.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ملفات ليست صفحات">

كل ملف `.md` داخل مجلد إصدار صفحة، ما عدا:

- الملفات والمجلدات التي يبدأ اسمها بـ `_` (احفظ المسودات والأجزاء في `_drafts/` أو `_partials.md`)،
- الملفات والمجلدات التي يبدأ اسمها بـ `.`،
- `node_modules/` و `vendor/` و `skills/`،
- ملف `README.md` داخل مجلد فرعي، كي يحمل المجلد ملف README لـ GitHub من دون أن يضيف صفحة،
- مجلدات اللغات الأخرى، فهي تتبع لغتها.

سمِّ الملفات بأحرف صغيرة مع شرطات. اسم الملف هو الرابط، حرفًا بحرف.

</Accordion>

<Accordion title="قواعد أخرى للروابط">

- **صفحات index** تأخذ رابط مجلدها. فالملف `get-started/index.md` هو الصفحة `get-started`، على `/3.x/get-started`. والملف `get-started.md` بجوار المجلد يصنع الصفحة نفسها، فاستخدم أحدهما فقط.
- **الإصدارات.** يُعاد توجيه الرابط الذي لا يحمل إصدارًا إلى الصفحة نفسها في الإصدار الافتراضي: يذهب `/get-started/installation` إلى `/3.x/get-started/installation`. وفي مجموعة مركّبة على جذر النطاق، يُصيّر `/` نفسه الصفحة الرئيسية للإصدار الافتراضي.
- **اللغات.** اللغة الافتراضية لا مقطع لها في الرابط. والصفحة التي لم تُترجم بعد تفتح في كل لغة، وتعرض الأصل.
- **نسخ Markdown.** أضف `.md` إلى رابط أي صفحة لتحصل على نسخة Markdown التي يقرؤها الوكلاء، أو اطلب رابط الصفحة مع رأس HTTP `Accept: text/markdown`. راجع [واجهات الوكلاء](../agents/surfaces.md).
- **الملفات المرفقة.** الصورة التي تشير إليها الصفحة، مثل `![Diagram](./_assets/diagram.png)` في `3.x/`، تُقدَّم من `/_assets/3.x/diagram.png` تحت بادئة المجموعة. وتشرح صفحة [Markdown](../writing/markdown.md) أين يبحث LaRecipe عن الملف.
- **المهارات.** يُقدَّم `docs/skills/billing-integration/SKILL.md` على `/skills/billing-integration/SKILL.md`، مع ملفات `references/` و `scripts/` و `assets/` بجواره. ولا تقدّمها إلا مجموعات المستودع.

</Accordion>

<Accordion title="المزيد عن larecipe:make:page والمجلد">

يكتب `larecipe:make:page` الصفحة في الإصدار الافتراضي (ويختار `--docs-version` و `--locale` غيره). وحين يكون للإصدار ملف `navigation.json`، أضف الصفحة إليه أيضًا، وتبيّن صفحة [التنقل](../writing/navigation.md) كيف.

من دون `larecipe.json`، تحمل المجموعة اسم مفتاحها ولا إصدارات لها. ويجوز أن يحوي مجلد اللغة ملف `navigation.json` خاصًا به، ومن دونه يُستخدم ملف الإصدار. ومجلد `skills/` في جذر المجموعة يحوي مهارات مكتوبة يدويًا للوكلاء، مجلدًا لكل مهارة فيه `SKILL.md`.

كل ما سبق ينطبق على [مجموعة المستودع](../sources/repository.md). أما مجموعة قاعدة البيانات فتحفظ الصفحات والملفات نفسها صفوفًا، بالشكل نفسه. والإعدادات التي تخص المجموعة كلها لا تُكتب أبدًا في ترويسة الصفحة: المظهر والإصدارات واللغات مكانها `larecipe.json`، والشريط الجانبي مكانه `navigation.json`.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [Markdown](../writing/markdown.md): ما الذي يمكن أن تحويه الصفحة.
- [التنقل](../writing/navigation.md): الشريط الجانبي، من المجلدات أو من `navigation.json`.
- [الإصدارات](../writing/versions.md) و[اللغات](../writing/languages.md): المجلدات داخل المجموعة.
