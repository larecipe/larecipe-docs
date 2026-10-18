---
title: ترويسة الصفحة
description: كل مفتاح يمكن لصفحة أن تضبطه في ترويستها (frontmatter)، مع نوعه ومعناه ومثال عليه، وكيف تُعامَل المفاتيح غير المعروفة، ورابط المخطط.
sources:
  - vendor/larecipe/larecipe/resources/schemas/frontmatter.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Frontmatter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Page.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/FrontmatterSchemaRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/PageBasicsRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Search/Services/SearchIndexRepository.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/SourcePattern.php
translated_from: sha256:e2d9f42a85b1ccbf31fab6bf7f288857827245bb9c966424b911c80ea55786e0
---

# ترويسة الصفحة

كل مفتاح يمكن لصفحة أن تضبطه في ترويستها (frontmatter)، أي كتلة YAML الواقعة بين سطري `---` في أعلاها. تضبط الترويسة تلك الصفحة وحدها، أما إعدادات المجموعة كلها فمكانها [larecipe.json](manifest.md). وينبغي أن تضبط كل صفحة `title` و `description` و `sources`.

```yaml
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources:
  - app/Webhooks/**
  - routes/webhooks.php
order: 20
---
```

## المفاتيح

| المفتاح | النوع | المعنى | مثال |
| --- | --- | --- | --- |
| `title` | string | عنوان الصفحة. من دونه يُستخدم أول عنوان `# `، ثم اسم الملف. | `title: Webhooks` |
| `description` | string | جملة واحدة عن الصفحة. تُستخدم في `llms.txt` ووسوم meta ونتائج البحث وأداة MCP `list_pages`. ويحذّر `larecipe:validate` حين تغيب. | `description: Receive signed events and verify them.` |
| `sources` | string or list of strings | أنماط glob للكود الذي توثّقه الصفحة، انطلاقًا من جذر المشروع، ليعرف `larecipe:affected` متى تصبح الصفحة قديمة. ويحصر `path#region` الملف في منطقة `#region` واحدة. | `sources: [app/Webhooks/**, routes/api.php#webhooks]` |
| `order` | number | الموضع في التنقل المستنتج من المجلدات، والأصغر أولًا. يُقرأ عددًا صحيحًا. ويُتجاهل حين يكون للإصدار ملف `navigation.json`. | `order: 20` |
| `icon` | string | اسم أيقونة من Lucide، يُمرَّر إلى الواجهة مع مدخل الصفحة في التنقل. والشريط الجانبي المدمج لا يرسم أيقونات الصفحات في هذا الإصدار. | `icon: webhook` |
| `tags` | list of strings | كلمات إضافية للبحث. | `tags: [billing, events]` |
| `badge` | string, or `{ label, tone }` | شارة بجوار مدخل الصفحة في التنقل. وقيمة `tone` هي `info` أو `success` أو `warning` أو `danger` أو `neutral`. | `badge: { label: New, tone: info }` |
| `since` | string or number | الإصدار الذي قدّم الميزة. يُقبل ويُتحقق منه، لكنه لا يُعرض في هذا الإصدار. | `since: "3.1"` |
| `deprecated` | boolean or string | يعلّم الصفحة بأنها مهملة. يُقبل ويُتحقق منه، لكنه لا يُعرض في هذا الإصدار. | `deprecated: true` |
| `hidden` | boolean | للصفحة رابط يعمل، لكنها مستبعدة من التنقل والبحث وخريطة الموقع. | `hidden: true` |
| `noindex` | boolean | يرسل `X-Robots-Tag: noindex` ووسم meta للروبوتات، ويستبعد الصفحة من خريطة الموقع. | `noindex: true` |
| `visibility` | `public` or `private` | تحتاج الصفحة الخاصة إلى البوابة (gate) `viewLarecipe` في كل واجهة: HTML و Markdown والبحث و `llms.txt` و MCP. تُقصّ المسافات من القيمة وتُحوَّل إلى أحرف صغيرة، وكل ما عدا `public` يُعدّ خاصًا، فلا يَنشر خطأٌ إملائي صفحةً أبدًا. | `visibility: private` |
| `redirect_from` | string or list of strings | مسارات قديمة تجيب بتحويل `301` إلى هذه الصفحة. اكتب مسارًا داخل الإصدار (`/hooks`) أو مسار الرابط الكامل. | `redirect_from: [/hooks]` |
| `layout` | `default`, `wide` or `full` | `default` فيه الشريط الجانبي والعمود الجانبي (rail). `wide` يحذف العمود الجانبي. `full` يحذف الاثنين. | `layout: wide` |
| `inserts` | `false`, or an object of booleans | يستثني هذه الصفحة من نقاط الإدراج المضبوطة في `larecipe.json`. والقيمة `false` توقفها كلها. | `inserts: { article.after: false }` |
| `type` | `page` or `openapi` | نوع الصفحة. صفحات `openapi` تصل في إصدار 3.x لاحق، واليوم لا تسمح هذه القيمة إلا بمتن فارغ. | `type: page` |
| `spec` | string | المواصفة لصفحة `openapi`. تُقبل، لكنها لا تُقرأ في هذا الإصدار. | `spec: openapi.yaml` |
| `meta` | object | قيم لمكوّناتك الخاصة، تقرؤها بصيغة `page.meta`. وهي الشيء الوحيد من الترويسة الذي يصل إلى القرّاء غير المفاتيح أعلاه. | `meta: { plan: enterprise }` |

نقاط الإدراج التي يمكن أن يسمّيها `inserts` هي `header.actions` و `sidebar.before` و `sidebar.after` و `article.before` و `article.after` و `toc.after` و `footer`.

## المفاتيح غير المعروفة {#unknown-keys}

تُحفظ المفاتيح غير المعروفة على الخادم، ولا تُعدّ خطأً أبدًا، ولا تصل إلى القرّاء أبدًا، فهي كثيرًا ما تحمل ملاحظات الفريق، مثل اسم المسؤول عن الصفحة. أما القيم التي تقرؤها مكوّناتك فتوضع تحت `meta:`، وهي الخريطة الوحيدة التي تُمرَّر إلى الواجهة، بصيغة `page.meta`:

```yaml
---
title: Pricing
owner: billing-team        # stays on the server
meta:
  plan: enterprise         # reaches the browser as page.meta.plan
---
```

يفحص `larecipe:validate` ترويسة كل صفحة ضمن القاعدة `frontmatter.schema`:

| الملاحظة | المستوى | مثال على الرسالة |
| --- | --- | --- |
| YAML لا يمكن تحليله | خطأ | The frontmatter is not valid YAML: ... |
| مفتاح معروف بنوع أو قيمة خاطئة | خطأ | Frontmatter at /layout: The data should match one item from enum |
| مفتاح غير معروف لا يبعد عن مفتاح معروف أكثر من تعديلين | تحذير | Unknown frontmatter key "descripton". Fix: Rename "descripton" to "description". |

تُعامَل الواصلة (`-`) معاملة الشرطة السفلية (`_`) عند البحث عن أقرب مفتاح، فيُلتقط `redirect-from` أيضًا.

## المخطط

يأتي المخطط مع الحزمة في `resources/schemas/frontmatter.schema.json`. وقيمة `$id` فيه:

```text
https://larecipe.saleem.dev/schemas/3.x/frontmatter.json
```

وتقدّم كل مجموعة توثيق أيضًا النسخة المطابقة لإصدارك المثبّت على `/{prefix}/schemas/frontmatter.json` (`/schemas/frontmatter.json` في هذا الموقع). راجع [larecipe.json](manifest.md#the-schema) لتعرف كيف تُقدَّم المخططات.

## التالي

- [Markdown](../writing/markdown.md): ما يدعمه متن الصفحة.
- [larecipe.json](manifest.md): إعدادات المجموعة كلها.
